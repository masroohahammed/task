<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Projects extends MY_Controller {

    /** NULL if empty; int only if client row exists (avoids FK 1452 on ''). */
    private function _posted_client_id() {
        $raw = $this->input->post('client_id');
        if ($raw === null || $raw === '' || $raw === false) {
            return null;
        }
        $id = (int)$raw;
        if ($id < 1) {
            return null;
        }
        return $this->Client_model->get($id) ? $id : null;
    }

    /** Member IDs from POST: only ints that exist as active non-client users. */
    private function _posted_member_ids() {
        $raw = $this->input->post('members');
        if (!is_array($raw)) {
            $raw = [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
        if (empty($ids)) {
            return [];
        }
        $rows = $this->db->select('u.id')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where_in('u.id', $ids)
            ->where('u.status', 'active')
            ->where('r.slug !=', 'client')
            ->get()->result();
        return array_map('intval', array_column($rows, 'id'));
    }

    public function index() {
        $role = $this->current_user->role_slug;
        if ($role === 'admin' || $role === 'project_manager') {
            $data['projects'] = $this->Project_model->get_all_with_stats();
        } elseif ($role === 'client') {
            $data['projects'] = $this->_client_projects();
        } else {
            $data['projects'] = $this->Project_model->get_by_member($this->current_user->id);
        }
        $data['page_title'] = 'Projects';
        $this->render('projects/index', $data);
    }

    public function create() {
        require_permission('can_create_project');
        $data['clients'] = $this->Client_model->get_all();
        $data['users']   = $this->User_model->get_project_member_candidates();
        $data['page_title'] = 'Create Project';
        $this->render('projects/create', $data);
    }

    public function store() {
        require_permission('can_create_project');
        $this->form_validation->set_rules('name',       'Project Name', 'required|max_length[255]');
        $this->form_validation->set_rules('start_date', 'Start Date',   'required');
        $this->form_validation->set_rules('end_date',   'End Date',     'required');

        if ($this->form_validation->run()) {
            $start = $this->input->post('start_date');
            $end   = $this->input->post('end_date');
            if ($start && $end && strtotime($end) < strtotime($start)) {
                $this->session->set_flashdata('error', 'End date must be on or after the start date.');
                $this->create();
                return;
            }

            $slug = url_title($this->input->post('name', TRUE), '-', TRUE) . '-' . time();
            $project_id = $this->Project_model->create([
                'client_id'   => $this->_posted_client_id(),
                'name'        => $this->input->post('name', TRUE),
                'slug'        => $slug,
                'description' => $this->input->post('description', TRUE),
                'start_date'  => $start,
                'end_date'    => $end,
                'status'      => $this->input->post('status'),
                'priority'    => $this->input->post('priority'),
                'created_by'  => $this->current_user->id,
            ]);

            $member_ids = $this->_posted_member_ids();
            $this->Project_model->sync_members($project_id, $member_ids, (int)$this->current_user->id);

            $notify_ids = $member_ids;
            $mid = (int)$this->current_user->id;
            if ($mid && !in_array($mid, $notify_ids, true)) {
                $notify_ids[] = $mid;
            }
            if (!empty($notify_ids)) {
                $this->Notification_model->notify_members($notify_ids, 'project_assigned',
                    'New Project Assigned', 'You have been assigned to project: ' . $this->input->post('name', TRUE),
                    'project', $project_id);
            }

            $this->log_activity('Created project', 'project', $project_id);
            $this->session->set_flashdata('success', 'Project created successfully.');
            redirect('projects/view/' . $project_id);
        } else {
            $this->create();
        }
    }

    public function view($id) {
        $project = $this->Project_model->get_with_details($id);
        if (!$project) show_404();
        $this->_require_client_project($id, 'view');

        $data['project']         = $project;
        $data['members']         = $this->Project_model->get_members($id);
        $data['task_stats']      = $this->Task_model->get_project_stats($id);
        $data['issue_stats']     = $this->Issue_model->get_project_stats($id);
        $data['ticket_stats']    = $this->Ticket_model->get_project_stats($id);
        $data['recent_tasks']    = $this->Task_model->get_by_project($id, 5);
        $data['recent_issues']   = $this->Issue_model->get_by_project($id, 5);
        $data['recent_tickets']  = $this->Ticket_model->get_by_project($id, 5);
        $data['activities']      = $this->Activity_model->get_by_reference('project', $id, 10);
        $data['page_title']      = $project->name;
        $this->render('projects/view', $data);
    }

    public function edit($id) {
        require_permission('can_manage_project');
        $project = $this->Project_model->get($id);
        if (!$project) show_404();
        $data['project']    = $project;
        $data['clients']    = $this->Client_model->get_all();
        $data['users']      = $this->User_model->get_project_member_candidates();
        $data['members']    = array_column($this->Project_model->get_members($id), 'user_id');
        $data['page_title'] = 'Edit Project';
        $this->render('projects/create', $data);
    }

    public function update($id) {
        require_permission('can_manage_project');
        $project = $this->Project_model->get($id);
        if (!$project) show_404();

        $start = $this->input->post('start_date');
        $end   = $this->input->post('end_date');
        if ($start && $end && strtotime($end) < strtotime($start)) {
            $this->session->set_flashdata('error', 'End date must be on or after the start date.');
            redirect('projects/edit/' . $id);
            return;
        }

        $this->Project_model->update($id, [
            'client_id'   => $this->_posted_client_id(),
            'name'        => $this->input->post('name', TRUE),
            'description' => $this->input->post('description', TRUE),
            'start_date'  => $start,
            'end_date'    => $end,
            'status'      => $this->input->post('status'),
            'priority'    => $this->input->post('priority'),
        ]);

        $member_ids = $this->_posted_member_ids();
        $manager_id = (int)$project->created_by ?: (int)$this->current_user->id;
        $this->Project_model->sync_members($id, $member_ids, $manager_id);

        $this->log_activity('Updated project', 'project', $id);
        $this->session->set_flashdata('success', 'Project updated successfully.');
        redirect('projects/view/' . $id);
    }

    public function delete($id) {
        require_permission('can_manage_project');
        $this->Project_model->delete($id);
        $this->log_activity('Deleted project', 'project', $id);
        $this->session->set_flashdata('success', 'Project deleted.');
        redirect('projects');
    }

    public function add_member($project_id) {
        require_permission('can_manage_project');
        $user_id = (int)$this->input->post('user_id');
        $this->Project_model->add_member($project_id, $user_id);
        $this->json_response(['success' => true]);
    }

    public function remove_member($project_id, $user_id) {
        require_permission('can_manage_project');
        $this->Project_model->remove_member($project_id, $user_id);
        $this->json_response(['success' => true]);
    }
}

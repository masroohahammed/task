<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Issues extends MY_Controller {

    public function index() {
        $data['issues']     = $this->Issue_model->get_all_with_details();
        $data['projects']   = $this->Project_model->get_all();
        $data['page_title'] = 'Issue Tracker';
        $this->render('issues/index', $data);
    }

    public function kanban($project_id) {
        $project = $this->Project_model->get($project_id);
        if (!$project) show_404();

        $data['project']          = $project;
        $data['members']          = $this->Project_model->get_members($project_id);
        $data['issues_open']      = $this->Issue_model->get_by_status($project_id, 'open');
        $data['issues_progress']  = $this->Issue_model->get_by_status($project_id, 'in_progress');
        $data['issues_fixed']     = $this->Issue_model->get_by_status($project_id, 'fixed');
        $data['issues_retesting'] = $this->Issue_model->get_by_status($project_id, 'retesting');
        $data['issues_closed']    = $this->Issue_model->get_by_status($project_id, 'closed');
        $data['page_title']       = 'Issue Board — ' . $project->name;
        $this->render('issues/kanban', $data);
    }

    public function create($project_id = null) {
        require_permission('can_raise_issue');
        $data['projects']   = $this->Project_model->get_all();
        $data['project_id'] = $project_id;
        $data['tasks']      = $project_id ? $this->Task_model->get_by_project($project_id) : [];
        $data['users']      = $this->User_model->get_employees();
        $data['page_title'] = 'Report Issue';
        $this->render('issues/create', $data);
    }

    public function store() {
        require_permission('can_raise_issue');
        $this->form_validation->set_rules('title',      'Title',   'required');
        $this->form_validation->set_rules('project_id', 'Project', 'required');

        if ($this->form_validation->run()) {
            $project_id   = $this->input->post('project_id');
            $issue_number = 'ISS-' . strtoupper(substr(uniqid(), -6));
            
            // Handle screenshot upload
            $screenshot = null;
            if (!empty($_FILES['screenshot']['name'])) {
                $this->load->library('upload');
                $config = [
                    'upload_path'   => FCPATH . 'uploads/issues/',
                    'allowed_types' => 'jpg|jpeg|png|gif',
                    'max_size'      => 5120,
                    'file_name'     => 'iss_' . time(),
                ];
                $this->upload->initialize($config);
                if ($this->upload->do_upload('screenshot')) {
                    $screenshot = 'issues/' . $this->upload->data('file_name');
                }
            }

            $issue_id = $this->Issue_model->create([
                'project_id'         => $project_id,
                'task_id'            => $this->input->post('task_id') ?: NULL,
                'issue_number'       => $issue_number,
                'title'              => $this->input->post('title', TRUE),
                'description'        => $this->input->post('description', TRUE),
                'steps_to_reproduce' => $this->input->post('steps_to_reproduce', TRUE),
                'expected_result'    => $this->input->post('expected_result', TRUE),
                'actual_result'      => $this->input->post('actual_result', TRUE),
                'screenshot'         => $screenshot,
                'priority'           => $this->input->post('priority'),
                'severity'           => $this->input->post('severity'),
                'status'             => 'open',
                'reported_by'        => $this->current_user->id,
                'assigned_to'        => $this->input->post('assigned_to') ?: NULL,
                'tags'               => $this->input->post('tags', TRUE),
            ]);

            // Notify assigned developer
            $assigned = $this->input->post('assigned_to');
            if ($assigned) {
                $this->Notification_model->create([
                    'user_id'        => $assigned,
                    'type'           => 'issue_assigned',
                    'title'          => 'Issue Assigned: ' . $issue_number,
                    'message'        => $this->input->post('title', TRUE),
                    'reference_type' => 'issue',
                    'reference_id'   => $issue_id,
                ]);
            }

            $this->log_activity('Reported issue ' . $issue_number, 'issue', $issue_id);
            $this->session->set_flashdata('success', 'Issue reported successfully.');
            redirect('issues/view/' . $issue_id);
        } else {
            $this->create($this->input->post('project_id'));
        }
    }

    public function view($id) {
        $issue = $this->Issue_model->get_with_details($id);
        if (!$issue) show_404();

        $data['issue']      = $issue;
        $data['comments']   = $this->Issue_model->get_comments($id);
        $data['members']    = $this->Project_model->get_members($issue->project_id);
        $data['page_title'] = $issue->issue_number . ': ' . $issue->title;
        $this->render('issues/view', $data);
    }

    public function update_status() {
        $issue_id = $this->input->post('issue_id');
        $status   = $this->input->post('status');
        $position = $this->input->post('position');

        $valid = ['open','in_progress','fixed','retesting','closed','reopened'];
        if (!in_array($status, $valid)) {
            $this->json_response(['success' => false], 400);
            return;
        }

        $issue = $this->Issue_model->get($issue_id);
        $this->Issue_model->update($issue_id, ['status' => $status, 'position' => $position]);

        // Workflow notifications
        if ($status === 'fixed' && $issue) {
            $this->Notification_model->create([
                'user_id'        => $issue->reported_by,
                'type'           => 'issue_fixed',
                'title'          => 'Issue Fixed',
                'message'        => 'Issue has been marked as fixed and needs retesting.',
                'reference_type' => 'issue',
                'reference_id'   => $issue_id,
            ]);
        }

        $this->log_activity('Changed issue status to ' . $status, 'issue', $issue_id);
        $this->json_response(['success' => true]);
    }

    public function add_comment() {
        $issue_id = $this->input->post('issue_id');
        $comment  = $this->input->post('comment', TRUE);

        if (empty($comment)) {
            $this->json_response(['success' => false, 'message' => 'Comment required'], 422);
            return;
        }

        $this->Issue_model->add_comment([
            'issue_id' => $issue_id,
            'user_id'  => $this->current_user->id,
            'comment'  => $comment,
        ]);

        $this->log_activity('Commented on issue', 'issue', $issue_id);
        $this->json_response(['success' => true]);
    }

    public function delete($id) {
        require_permission('can_manage_project');
        $this->Issue_model->delete($id);
        $this->session->set_flashdata('success', 'Issue deleted.');
        redirect('issues');
    }
}

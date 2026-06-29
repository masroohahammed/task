<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends MY_Controller {

    public function index() {
        $role = $this->current_user->role_slug;
        if ($role === 'client') {
            $data['tickets'] = $this->Ticket_model->get_for_client_user(
                $this->current_user->client_id,
                $this->current_user->id,
                $this->_client_is_admin()
            );
            $data['projects'] = $this->_client_projects();
        } elseif ($role === 'employee') {
            $data['tickets'] = $this->Ticket_model->get_assigned_to($this->current_user->id);
        } else {
            $data['tickets'] = $this->Ticket_model->get_all_with_details();
        }
        $data['projects']   = $this->Project_model->get_all();
        $data['page_title'] = 'Support Tickets';
        $this->render('tickets/index', $data);
    }

    public function create() {
        $role = $this->current_user->role_slug;
        if ($role === 'client') {
            $data['projects'] = $this->_client_projects();
        } else {
            $data['projects'] = $this->Project_model->get_all();
        }
        $data['page_title'] = 'Create Ticket';
        $this->render('tickets/create', $data);
    }

    public function store() {
        $this->form_validation->set_rules('title',      'Title',   'required');
        $this->form_validation->set_rules('project_id', 'Project', 'required');

        if ($this->form_validation->run()) {
            $project_id = (int)$this->input->post('project_id');
            if (has_role('client')) {
                $this->_require_client_project($project_id, 'tickets');
            }

            $ticket_number = 'TKT-' . strtoupper(substr(uniqid(), -6));

            // Image upload
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $this->load->library('upload');
                $config = [
                    'upload_path'   => FCPATH . 'uploads/tickets/',
                    'allowed_types' => 'jpg|jpeg|png|gif',
                    'max_size'      => 5120,
                    'file_name'     => 'tkt_' . time(),
                ];
                $this->upload->initialize($config);
                if ($this->upload->do_upload('image')) {
                    $image = 'tickets/' . $this->upload->data('file_name');
                }
            }

            $client_id = $this->current_user->client_id;
            if (!$client_id) {
                $project = $this->Project_model->get($this->input->post('project_id'));
                $client_id = $project ? $project->client_id : 0;
            }

            $ticket_id = $this->Ticket_model->create([
                'project_id'    => $this->input->post('project_id'),
                'client_id'     => $client_id,
                'ticket_number' => $ticket_number,
                'title'         => $this->input->post('title', TRUE),
                'description'   => $this->input->post('description', TRUE),
                'image'         => $image,
                'priority'      => $this->input->post('priority'),
                'status'        => 'open',
                'created_by'    => $this->current_user->id,
            ]);

            // Notify project managers
            $this->Notification_model->notify_role('project_manager', 'ticket_created',
                'New Ticket: ' . $ticket_number,
                $this->input->post('title', TRUE),
                'ticket', $ticket_id);

            $this->log_activity('Created ticket ' . $ticket_number, 'ticket', $ticket_id);
            $this->session->set_flashdata('success', 'Ticket submitted successfully.');
            redirect('tickets/view/' . $ticket_id);
        } else {
            $this->create();
        }
    }

    public function view($id) {
        $ticket = $this->Ticket_model->get_with_details($id);
        if (!$ticket) show_404();

        if (has_role('client')) {
            if ((int)$ticket->client_id !== (int)$this->current_user->client_id) {
                show_404();
            }
            $this->_require_client_project($ticket->project_id, 'view');
        }

        $data['ticket']     = $ticket;
        $data['comments']   = $this->Ticket_model->get_comments($id);
        $data['users']      = $this->User_model->get_employees();
        $data['page_title'] = $ticket->ticket_number . ': ' . $ticket->title;
        $this->render('tickets/view', $data);
    }

    public function update_status() {
        require_permission('can_manage_ticket');
        $ticket_id = $this->input->post('ticket_id');
        $status    = $this->input->post('status');
        $assigned  = $this->input->post('assigned_to');

        $update = ['status' => $status];
        if ($assigned) $update['assigned_to'] = $assigned;

        $this->Ticket_model->update($ticket_id, $update);

        // Notify client
        $ticket = $this->Ticket_model->get($ticket_id);
        if ($ticket && $ticket->created_by) {
            $this->Notification_model->create([
                'user_id'        => $ticket->created_by,
                'type'           => 'ticket_updated',
                'title'          => 'Ticket Updated',
                'message'        => 'Ticket status changed to ' . ucfirst($status),
                'reference_type' => 'ticket',
                'reference_id'   => $ticket_id,
            ]);
        }

        $this->log_activity('Updated ticket status to ' . $status, 'ticket', $ticket_id);
        $this->json_response(['success' => true]);
    }

    public function add_comment() {
        $ticket_id = $this->input->post('ticket_id');
        $comment   = $this->input->post('comment', TRUE);

        if (empty($comment)) {
            $this->json_response(['success' => false], 422);
            return;
        }

        $this->Ticket_model->add_comment([
            'ticket_id' => $ticket_id,
            'user_id'   => $this->current_user->id,
            'comment'   => $comment,
        ]);

        $this->log_activity('Commented on ticket', 'ticket', $ticket_id);
        $this->json_response(['success' => true,
            'user_name'  => $this->current_user->first_name . ' ' . $this->current_user->last_name,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete($id) {
        require_permission('can_manage_ticket');
        $this->Ticket_model->delete($id);
        $this->session->set_flashdata('success', 'Ticket deleted.');
        redirect('tickets');
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Client-facing portal: sub-user and project access management.
 * Only client admins (is_client_admin) can use these endpoints.
 */
class Client_portal extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Client_user_project_model');
        $this->_require_client_admin();
    }

    public function users() {
        $client_id = (int)$this->current_user->client_id;
        $data['users']      = $this->Client_model->get_users($client_id);
        $data['projects']   = $this->Project_model->get_by_client($client_id);
        $data['page_title'] = 'Team Members';
        $this->render('client_portal/users', $data);
    }

    public function add_user() {
        $client_id = (int)$this->current_user->client_id;
        $email      = trim((string)$this->input->post('email', TRUE));
        $first_name = trim((string)$this->input->post('first_name', TRUE));
        $pass       = $this->input->post('password');

        if ($first_name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json_response(['success' => false, 'message' => 'First name and a valid email are required.'], 422);
            return;
        }
        if (!$pass || strlen($pass) < 6) {
            $this->json_response(['success' => false, 'message' => 'Password must be at least 6 characters.'], 422);
            return;
        }
        if ($this->User_model->email_exists($email)) {
            $this->json_response(['success' => false, 'message' => 'Email already registered.'], 422);
            return;
        }

        $role_id = $this->Role_model->get_client_role_id();
        if (!$role_id) {
            $this->json_response(['success' => false, 'message' => 'Client role is not configured.'], 500);
            return;
        }

        $data = [
            'role_id'    => $role_id,
            'client_id'  => $client_id,
            'first_name' => $first_name,
            'last_name'  => $this->input->post('last_name', TRUE),
            'email'      => $email,
            'password'   => password_hash($pass, PASSWORD_DEFAULT),
            'job_title'  => $this->input->post('job_title', TRUE) ?: 'Client Contact',
            'status'     => 'active',
        ];
        if ($this->db->field_exists('is_client_admin', 'users')) {
            $data['is_client_admin'] = 0;
        }

        $uid = $this->User_model->create($data);

        if (!$uid) {
            $this->json_response(['success' => false, 'message' => 'Could not create user.'], 500);
            return;
        }

        $assignments = $this->input->post('projects');
        if (is_array($assignments)) {
            $valid = $this->_filter_project_assignments($client_id, $assignments);
            $this->Client_user_project_model->sync_for_user($uid, $valid, $this->current_user->id);
        }

        $this->json_response(['success' => true, 'user_id' => $uid, 'message' => 'Team member added.']);
    }

    public function remove_user($user_id) {
        $user = $this->_get_client_user($user_id);
        if (!$user) {
            $this->json_response(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }
        if (!empty($user->is_client_admin)) {
            $this->json_response(['success' => false, 'message' => 'Cannot remove the primary client admin.'], 422);
            return;
        }
        $this->Client_user_project_model->remove_user_assignments($user_id);
        $this->User_model->delete($user_id);
        $this->json_response(['success' => true]);
    }

    public function reset_password($user_id) {
        $user = $this->_get_client_user($user_id);
        if (!$user) {
            $this->json_response(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }
        $pass = $this->input->post('password');
        if (!$pass || strlen($pass) < 6) {
            $this->json_response(['success' => false, 'message' => 'Password must be at least 6 characters.'], 422);
            return;
        }
        $this->User_model->update($user_id, ['password' => password_hash($pass, PASSWORD_DEFAULT)]);
        $this->json_response(['success' => true, 'message' => 'Password updated.']);
    }

    public function save_user_projects($user_id) {
        $user = $this->_get_client_user($user_id);
        if (!$user) {
            $this->json_response(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }
        if (!empty($user->is_client_admin)) {
            $this->json_response(['success' => false, 'message' => 'Primary admins have access to all projects.'], 422);
            return;
        }

        $assignments = $this->input->post('projects');
        if (!is_array($assignments)) {
            $assignments = [];
        }
        $valid = $this->_filter_project_assignments((int)$user->client_id, $assignments);
        $this->Client_user_project_model->sync_for_user($user_id, $valid, $this->current_user->id);
        $this->json_response(['success' => true, 'message' => 'Project access updated.']);
    }

    public function user_projects($user_id) {
        $user = $this->_get_client_user($user_id);
        if (!$user) {
            $this->json_response(['success' => false], 404);
            return;
        }
        $assigned = $this->Client_user_project_model->get_for_user($user_id);
        $map = [];
        foreach ($assigned as $row) {
            $map[$row->project_id] = $row->permission;
        }
        $this->json_response(['success' => true, 'projects' => $map]);
    }

    private function _require_client_admin() {
        if (!has_role('client') || !is_client_admin()) {
            $this->session->set_flashdata('error', 'You do not have permission to manage team members.');
            redirect('dashboard');
        }
    }

    private function _get_client_user($user_id) {
        $user = $this->User_model->get_user($user_id);
        if (!$user || $user->role_slug !== 'client') {
            return null;
        }
        if ((int)$user->client_id !== (int)$this->current_user->client_id) {
            return null;
        }
        return $user;
    }

    private function _filter_project_assignments($client_id, array $assignments) {
        $valid = [];
        $allowed = ['view', 'tickets', 'manage'];
        foreach ($assignments as $project_id => $permission) {
            $project = $this->Project_model->get((int)$project_id);
            if ($project && (int)$project->client_id === (int)$client_id) {
                $valid[(int)$project_id] = in_array($permission, $allowed, true) ? $permission : 'view';
            }
        }
        return $valid;
    }
}

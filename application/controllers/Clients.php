<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Client_user_project_model');
    }

    public function index() {
        require_permission('can_view_client_details');
        $data['clients']    = $this->Client_model->get_all_with_stats();
        $data['page_title'] = 'Clients';
        $this->render('clients/index', $data);
    }

    public function create() {
        require_permission('can_view_client_details');
        $data['timezones']  = timezone_identifiers_list();
        $data['page_title'] = 'Add Client';
        $this->render('clients/create', $data);
    }

    public function store() {
        require_permission('can_view_client_details');
        $this->form_validation->set_rules('company_name',   'Company Name',   'required');
        $this->form_validation->set_rules('contact_person', 'Contact Person', 'required');
        $this->form_validation->set_rules('email',          'Email',          'required|valid_email');

        if (!$this->form_validation->run()) {
            $this->create();
            return;
        }

        $email    = $this->input->post('email', TRUE);
        $password = $this->input->post('password');

        if ($password !== null && $password !== '' && strlen($password) < 6) {
            $this->session->set_flashdata('error', 'Portal password must be at least 6 characters.');
            $this->create();
            return;
        }

        if ($password && $this->User_model->email_exists($email)) {
            $this->session->set_flashdata('error', 'That email is already registered. Use a different company email or add the portal user later.');
            $this->create();
            return;
        }

        $client_id = $this->Client_model->create([
            'company_name'   => $this->input->post('company_name', TRUE),
            'contact_person' => $this->input->post('contact_person', TRUE),
            'email'          => $email,
            'phone'          => $this->input->post('phone', TRUE),
            'address'        => $this->input->post('address', TRUE),
            'website'        => $this->input->post('website', TRUE),
            'notes'          => $this->input->post('notes', TRUE),
            'created_by'     => $this->current_user->id,
        ]);

        if ($this->db->field_exists('default_timezone', 'clients')) {
            $tz = trim((string)$this->input->post('default_timezone'));
            if ($tz === '' || in_array($tz, timezone_identifiers_list(), true)) {
                $this->Client_model->update($client_id, ['default_timezone' => $tz === '' ? null : $tz]);
            }
        }

        $portal_msg = '';
        if ($password) {
            $uid = $this->_create_portal_user($client_id, $email, $this->input->post('contact_person', TRUE), $password, true);
            if (!$uid) {
                $portal_msg = ' Portal login could not be created — please add a user from the client page.';
            }
        }

        $this->log_activity('Created client', 'client', $client_id);
        $this->session->set_flashdata('success', 'Client created successfully.' . $portal_msg);
        redirect('clients/view/' . $client_id);
    }

    public function view($id) {
        require_permission('can_view_client_details');
        $client = $this->Client_model->get($id);
        if (!$client) show_404();
        $data['client']     = $client;
        $data['projects']   = $this->Project_model->get_by_client($id);
        $data['tickets']    = $this->Ticket_model->get_by_client($id);
        $data['users']      = $this->Client_model->get_users($id);
        $data['page_title'] = html_escape($client->company_name);
        $data['page_scripts'] = $this->load->view('clients/view_scripts', $data, TRUE);
        $this->render('clients/view', $data);
    }

    public function edit($id) {
        require_permission('can_view_client_details');
        $data['client']     = $this->Client_model->get($id);
        if (!$data['client']) show_404();
        $data['timezones']  = timezone_identifiers_list();
        $data['page_title'] = 'Edit Client';
        $this->render('clients/create', $data);
    }

    public function update($id) {
        require_permission('can_view_client_details');
        $patch = [
            'company_name'   => $this->input->post('company_name', TRUE),
            'contact_person' => $this->input->post('contact_person', TRUE),
            'phone'          => $this->input->post('phone', TRUE),
            'address'        => $this->input->post('address', TRUE),
            'website'        => $this->input->post('website', TRUE),
            'notes'          => $this->input->post('notes', TRUE),
        ];
        if ($this->db->field_exists('default_timezone', 'clients')) {
            $tz = trim((string)$this->input->post('default_timezone'));
            if ($tz === '' || in_array($tz, timezone_identifiers_list(), true)) {
                $patch['default_timezone'] = $tz === '' ? null : $tz;
            }
        }
        $this->Client_model->update($id, $patch);
        $this->session->set_flashdata('success', 'Client updated.');
        redirect('clients/view/' . $id);
    }

    public function delete($id) {
        require_permission('can_view_client_details');
        $this->Client_model->delete($id);
        $this->session->set_flashdata('success', 'Client deleted.');
        redirect('clients');
    }

    public function add_user($client_id) {
        require_permission('can_view_client_details');
        if (!$this->Client_model->get($client_id)) {
            $this->json_response(['success' => false, 'message' => 'Client not found.'], 404);
            return;
        }

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

        $uid = $this->_create_portal_user(
            $client_id,
            $email,
            $first_name,
            $pass,
            false,
            $this->input->post('last_name', TRUE),
            $this->input->post('job_title', TRUE) ?: 'Client Contact'
        );

        if (!$uid) {
            $this->json_response(['success' => false, 'message' => 'Could not create user. Please try again.'], 500);
            return;
        }

        $this->json_response(['success' => true, 'user_id' => $uid, 'message' => 'User added!']);
    }

    public function remove_user($user_id) {
        require_permission('can_view_client_details');
        $user = $this->User_model->get_user($user_id);
        if (!$user || $user->role_slug !== 'client') {
            $this->json_response(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }
        $this->Client_user_project_model->remove_user_assignments($user_id);
        $this->User_model->delete($user_id);
        $this->json_response(['success' => true]);
    }

    public function reset_password($user_id) {
        require_permission('can_view_client_details');
        $user = $this->User_model->get_user($user_id);
        if (!$user || $user->role_slug !== 'client') {
            $this->json_response(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }
        $pass = $this->input->post('password');
        if (!$pass || strlen($pass) < 6) {
            $this->json_response(['success' => false, 'message' => 'Min 6 characters.'], 422);
            return;
        }
        $this->User_model->update($user_id, ['password' => password_hash($pass, PASSWORD_DEFAULT)]);
        $this->json_response(['success' => true, 'message' => 'Password updated.']);
    }

    public function save_user_projects($user_id) {
        require_permission('can_view_client_details');
        $user = $this->User_model->get_user($user_id);
        if (!$user || $user->role_slug !== 'client' || !$user->client_id) {
            $this->json_response(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }

        $assignments = $this->input->post('projects');
        if (!is_array($assignments)) {
            $assignments = [];
        }

        $valid = [];
        foreach ($assignments as $project_id => $permission) {
            $project = $this->Project_model->get((int)$project_id);
            if ($project && (int)$project->client_id === (int)$user->client_id) {
                $valid[(int)$project_id] = $permission;
            }
        }

        $this->Client_user_project_model->sync_for_user($user_id, $valid, $this->current_user->id);
        $this->json_response(['success' => true, 'message' => 'Project access updated.']);
    }

    public function user_projects($user_id) {
        require_permission('can_view_client_details');
        $user = $this->User_model->get_user($user_id);
        if (!$user || $user->role_slug !== 'client' || !$user->client_id) {
            $this->json_response(['success' => false], 404);
            return;
        }
        $assigned = $this->Client_user_project_model->get_for_user($user_id);
        $map = [];
        foreach ($assigned as $row) {
            $map[$row->project_id] = $row->permission;
        }
        $this->json_response([
            'success'  => true,
            'projects' => $map,
            'is_admin' => !empty($user->is_client_admin),
        ]);
    }

    private function _create_portal_user($client_id, $email, $name, $password, $is_admin = false, $last_name = '', $job_title = 'Client Contact') {
        $role_id = $this->Role_model->get_client_role_id();
        if (!$role_id) {
            return false;
        }

        $parts = preg_split('/\s+/', trim((string)$name), 2);
        $first = $parts[0] ?? 'Client';
        $last  = trim((string)$last_name);
        if ($last === '' && isset($parts[1])) {
            $last = $parts[1];
        }

        $data = [
            'role_id'    => $role_id,
            'client_id'  => (int)$client_id,
            'first_name' => $first,
            'last_name'  => $last,
            'email'      => $email,
            'password'   => password_hash($password, PASSWORD_DEFAULT),
            'job_title'  => $job_title,
            'status'     => 'active',
        ];

        if ($this->db->field_exists('is_client_admin', 'users')) {
            $data['is_client_admin'] = $is_admin ? 1 : 0;
        }

        $uid = $this->User_model->create($data);
        if (!$uid) {
            return false;
        }

        $this->_apply_client_default_tz_to_user($uid, $client_id);

        if ($is_admin && $this->Client_user_project_model->table_exists()) {
            $projects = $this->Project_model->get_by_client($client_id);
            $assignments = [];
            foreach ($projects as $p) {
                $assignments[$p->id] = 'manage';
            }
            if (!empty($assignments)) {
                $this->Client_user_project_model->sync_for_user($uid, $assignments, $this->current_user->id);
            }
        }

        return $uid;
    }

    private function _apply_client_default_tz_to_user($user_id, $client_id) {
        if (!$this->db->field_exists('timezone', 'users') || !$this->db->field_exists('default_timezone', 'clients')) {
            return;
        }
        $c = $this->Client_model->get($client_id);
        if ($c && !empty($c->default_timezone)) {
            $this->User_model->update($user_id, ['timezone' => $c->default_timezone]);
        }
    }
}

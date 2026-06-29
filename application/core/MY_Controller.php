<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    protected $current_user = [];
    protected $data = [];

    public function __construct() {
        parent::__construct();
        $this->load->model([
            'User_model', 'Role_model', 'Project_model',
            'Task_model', 'Issue_model', 'Ticket_model',
            'Client_model', 'Notification_model', 'Activity_model',
            'Attendance_model', 'Hr_model', 'Discussion_model', 'Chat_model',
            'Client_user_project_model',
        ]);
        $this->_check_auth();
    }

    protected function _check_auth() {
        $user_id = $this->session->userdata('user_id');
        if (!$user_id) { redirect('login'); }

        $this->current_user = $this->User_model->get_user($user_id);

        // Block resigned/inactive
        if (!$this->current_user || !in_array($this->current_user->status, ['active'])) {
            $this->session->sess_destroy();
            redirect('login');
        }

        if (!empty($this->current_user->timezone)) {
            $tz = $this->current_user->timezone;
            if (is_string($tz) && strlen($tz) <= 64 && in_array($tz, timezone_identifiers_list(), true)) {
                date_default_timezone_set($tz);
            }
        }

        $permissions = $this->User_model->get_user_permissions($user_id);
        $this->session->set_userdata('permissions', $permissions);

        // Update online status silently (table may not exist yet)
        try { $this->Chat_model->update_status($user_id, 'online'); } catch(Exception $e) {}

        $this->data['current_user']          = $this->current_user;
        $this->data['unread_notifications']  = $this->Notification_model->get_unread_count($user_id);
        $this->data['notifications']         = $this->Notification_model->get_latest($user_id, 5);

        // Safe loads — tables may not exist if SQL upgrade not run yet
        try { $this->data['chat_unread'] = $this->Chat_model->get_unread_count($user_id); }
        catch(Exception $e) { $this->data['chat_unread'] = 0; }

        try { $this->data['birthdays_today'] = $this->Hr_model->get_birthdays_today(); }
        catch(Exception $e) { $this->data['birthdays_today'] = []; }

        try { $this->data['present_today'] = $this->Hr_model->get_present_today(); }
        catch(Exception $e) { $this->data['present_today'] = []; }
    }

    protected function render($view, $data = []) {
        $data = array_merge($this->data, $data);
        $data['content'] = $this->load->view($view, $data, TRUE);
        $this->load->view('layouts/main', $data);
    }

    protected function render_ajax($view, $data = []) {
        $data = array_merge($this->data, $data);
        $this->load->view($view, $data);
    }

    protected function json_response($data, $status = 200) {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    protected function log_activity($action, $ref_type = null, $ref_id = null, $description = null) {
        $this->Activity_model->log([
            'user_id'        => $this->current_user->id,
            'action'         => $action,
            'reference_type' => $ref_type,
            'reference_id'   => $ref_id,
            'description'    => $description,
            'ip_address'     => $this->input->ip_address(),
        ]);
    }

    /** Whether the logged-in client user is the company admin. */
    protected function _client_is_admin() {
        return has_role('client') && is_client_admin();
    }

    /** Projects visible to the current client portal user. */
    protected function _client_projects() {
        return $this->Project_model->get_for_client_user(
            $this->current_user->client_id,
            $this->current_user->id,
            $this->_client_is_admin()
        );
    }

    /** Abort unless the client user may access the project. */
    protected function _require_client_project($project_id, $min_permission = 'view') {
        if (!has_role('client')) {
            return;
        }
        if (!$this->Project_model->client_user_can_access(
            $project_id,
            $this->current_user->client_id,
            $this->current_user->id,
            $this->_client_is_admin(),
            $min_permission
        )) {
            show_404();
        }
    }
}

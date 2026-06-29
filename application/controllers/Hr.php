<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hr extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if (!has_role(['admin','hr'])) {
            $this->session->set_flashdata('error', 'Access denied.');
            redirect('dashboard');
        }
        $this->load->model('Hr_model');
    }

    // ── Employee List ──────────────────────────────────────
    public function index() {
        $data['employees']  = $this->Hr_model->get_all_employees();
        $data['roles']      = $this->Role_model->get_all();
        $data['page_title'] = 'Employee Management';
        $this->render('hr/index', $data);
    }

    // ── Create Employee ────────────────────────────────────
    public function create() {
        $data['roles']      = $this->Role_model->get_staff_roles();
        $data['show_timezone'] = $this->db->field_exists('timezone', 'users');
        $data['page_title'] = 'Add Employee';
        $this->render('hr/create', $data);
    }

    // ── Store Employee ─────────────────────────────────────
    public function store() {
        $this->form_validation->set_rules('first_name',  'First Name',  'required');
        $this->form_validation->set_rules('last_name',   'Last Name',   'required');
        $this->form_validation->set_rules('email',       'Email',       'required|valid_email');
        $this->form_validation->set_rules('role_id',     'Role',        'required');
        $this->form_validation->set_rules('joining_date','Joining Date','required');

        if ($this->form_validation->run()) {
            $email = $this->input->post('email', TRUE);

            if ($this->User_model->email_exists($email)) {
                $this->session->set_flashdata('error', 'Email already registered.');
                $this->create(); return;
            }

            // Auto-generate employee ID
            $emp_id = 'EMP' . str_pad($this->Hr_model->next_emp_number(), 4, '0', STR_PAD_LEFT);

            // Auto-generate password from name+dob or random
            $dob      = $this->input->post('date_of_birth') ?: date('Y-m-d');
            $auto_pass = $this->input->post('first_name', TRUE) . '@' . date('Y', strtotime($dob));
            $password  = $this->input->post('password') ?: $auto_pass;

            // Handle avatar upload
            $avatar = null;
            if (!empty($_FILES['avatar']['name'])) {
                $this->load->library('upload');
                $cfg = [
                    'upload_path'   => FCPATH . 'uploads/avatars/',
                    'allowed_types' => 'jpg|jpeg|png|gif',
                    'max_size'      => 2048,
                    'file_name'     => 'emp_' . time(),
                ];
                @mkdir(FCPATH . 'uploads/avatars/', 0755, TRUE);
                $this->upload->initialize($cfg);
                if ($this->upload->do_upload('avatar')) {
                    $avatar = 'avatars/' . $this->upload->data('file_name');
                }
            }

            $uid = $this->User_model->create([
                'role_id'           => $this->input->post('role_id'),
                'first_name'        => $this->input->post('first_name', TRUE),
                'last_name'         => $this->input->post('last_name',  TRUE),
                'email'             => $email,
                'password'          => password_hash($password, PASSWORD_DEFAULT),
                'phone'             => $this->input->post('phone', TRUE),
                'job_title'         => $this->input->post('job_title', TRUE),
                'department'        => $this->input->post('department', TRUE),
                'date_of_birth'     => $dob ?: NULL,
                'address'           => $this->input->post('address', TRUE),
                'joining_date'      => $this->input->post('joining_date'),
                'employee_id'       => $emp_id,
                'emergency_contact' => $this->input->post('emergency_contact', TRUE),
                'avatar'            => $avatar,
                'status'            => 'active',
            ]);
            $this->_apply_hr_schedule_fields($uid);

            // Notify the new employee
            $this->Notification_model->create([
                'user_id'        => $uid,
                'type'           => 'welcome',
                'title'          => 'Welcome to Techfod!',
                'message'        => 'Your account has been created. Login: '.$email.' | Pass: '.$password,
                'reference_type' => 'user',
                'reference_id'   => $uid,
            ]);

            $this->log_activity('Created employee profile', 'user', $uid);
            $this->session->set_flashdata('success',
                'Employee created! Login: <strong>'.$email.'</strong> | Password: <strong>'.$password.'</strong>');
            redirect('hr/view/' . $uid);
        } else {
            $this->create();
        }
    }

    // ── View Employee ──────────────────────────────────────
    public function view($id) {
        $emp = $this->User_model->get_user($id);
        if (!$emp) show_404();
        $data['emp']          = $emp;
        $data['tasks']        = $this->Task_model->get_assigned_to($id, 10);
        $data['attendance']   = $this->Attendance_model->get_user_stats($id, date('m'), date('Y'));
        $data['recent_att']   = $this->Attendance_model->get_user_history($id, date('m'), date('Y'));
        $data['leaves']       = $this->Attendance_model->get_user_leaves($id);
        $data['page_title']   = html_escape($emp->first_name . ' ' . $emp->last_name);
        $this->render('hr/view', $data);
    }

    // ── Edit Employee ──────────────────────────────────────
    public function edit($id) {
        $data['emp']        = $this->User_model->get_user($id);
        $data['roles']      = $this->Role_model->get_staff_roles();
        $data['show_timezone'] = $this->db->field_exists('timezone', 'users');
        $data['page_title'] = 'Edit Employee';
        $this->render('hr/create', $data);
    }

    // ── Update Employee ────────────────────────────────────
    public function update($id) {
        $update = [
            'role_id'           => $this->input->post('role_id'),
            'first_name'        => $this->input->post('first_name', TRUE),
            'last_name'         => $this->input->post('last_name',  TRUE),
            'phone'             => $this->input->post('phone', TRUE),
            'job_title'         => $this->input->post('job_title', TRUE),
            'department'        => $this->input->post('department', TRUE),
            'date_of_birth'     => $this->input->post('date_of_birth') ?: NULL,
            'address'           => $this->input->post('address', TRUE),
            'joining_date'      => $this->input->post('joining_date') ?: NULL,
            'emergency_contact' => $this->input->post('emergency_contact', TRUE),
            'status'            => $this->input->post('status'),
        ];

        // Auto-block resigned
        if ($update['status'] === 'resigned') {
            $update['resignation_date'] = date('Y-m-d');
        }

        // Avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $this->load->library('upload');
            $cfg = [
                'upload_path'   => FCPATH . 'uploads/avatars/',
                'allowed_types' => 'jpg|jpeg|png|gif',
                'max_size'      => 2048,
                'file_name'     => 'emp_' . $id . '_' . time(),
            ];
            @mkdir(FCPATH . 'uploads/avatars/', 0755, TRUE);
            $this->upload->initialize($cfg);
            if ($this->upload->do_upload('avatar')) {
                $update['avatar'] = 'avatars/' . $this->upload->data('file_name');
            }
        }

        // Password change
        if ($this->input->post('new_password')) {
            $update['password'] = password_hash($this->input->post('new_password'), PASSWORD_DEFAULT);
        }

        $this->_merge_hr_schedule_into_update($update);

        $this->User_model->update($id, $update);
        $this->log_activity('Updated employee profile', 'user', $id);
        $this->session->set_flashdata('success', 'Employee profile updated.');
        redirect('hr/view/' . $id);
    }

    // ── Reset Password (AJAX) ──────────────────────────────
    public function reset_password($id) {
        $pass = $this->input->post('password');
        if (!$pass || strlen($pass) < 6) {
            $this->json_response(['success' => false, 'message' => 'Min 6 characters.'], 422); return;
        }
        $this->User_model->update($id, ['password' => password_hash($pass, PASSWORD_DEFAULT)]);
        $this->log_activity('Reset password for user', 'user', $id);
        $this->json_response(['success' => true, 'message' => 'Password reset successfully.']);
    }

    // ── Work Settings ──────────────────────────────────────
    public function settings() {
        if (!has_role('admin')) redirect('dashboard');
        if ($this->input->post()) {
            $settings = [
                'work_start'              => $this->input->post('work_start'),
                'work_end'                => $this->input->post('work_end'),
                'late_threshold_minutes'  => $this->input->post('late_threshold'),
                'work_days'               => implode(',', $this->input->post('work_days') ?: []),
            ];
            foreach ($settings as $k => $v) {
                $this->db->where('setting_key', $k)->update('work_settings', [
                    'setting_value' => $v,
                    'updated_by'    => $this->current_user->id,
                ]);
            }
            $this->session->set_flashdata('success', 'Work settings saved.');
        }
        $raw = $this->db->get('work_settings')->result();
        $data['settings'] = array_column((array)$raw, 'setting_value', 'setting_key');
        $data['page_title'] = 'Work Settings';
        $this->render('hr/settings', $data);
    }

    // ── Send Global Notification ───────────────────────────
    public function send_notification() {
        require_permission('can_send_notification');
        $title   = $this->input->post('title', TRUE);
        $message = $this->input->post('message', TRUE);
        $target  = $this->input->post('target'); // all, role:admin, role:employee etc.

        if (!$title || !$message) {
            $this->json_response(['success' => false, 'message' => 'Title and message required.'], 422); return;
        }

        if ($target === 'all') {
            $users = $this->db->select('id')->where('status', 'active')->get('users')->result();
            foreach ($users as $u) {
                $this->Notification_model->create([
                    'user_id'        => $u->id,
                    'type'           => 'global',
                    'title'          => $title,
                    'message'        => $message,
                    'is_global'      => 1,
                    'reference_type' => 'announcement',
                    'reference_id'   => 0,
                ]);
            }
        } elseif (strpos($target, 'role:') === 0) {
            $role = substr($target, 5);
            $this->Notification_model->notify_role($role, 'global', $title, $message, 'announcement', 0);
        }

        $this->json_response(['success' => true, 'message' => 'Notification sent!']);
    }

    // ── Deactivate ────────────────────────────────────────
    public function toggle_status($id) {
        $emp = $this->User_model->get_user($id);
        if (!$emp) { $this->json_response(['success'=>false],404); return; }
        $new_status = $emp->status === 'active' ? 'inactive' : 'active';
        $this->User_model->update($id, ['status' => $new_status]);
        $this->json_response(['success' => true, 'status' => $new_status]);
    }

    private function _valid_iana_timezone($tz) {
        if (strlen($tz) > 64 || !preg_match('/^[A-Za-z0-9_\/+\-]+$/', $tz)) {
            return false;
        }
        return in_array($tz, timezone_identifiers_list(), true);
    }

    private function _apply_hr_schedule_fields($user_id) {
        $patch = [];
        $this->_merge_hr_schedule_into_update($patch);
        if (!empty($patch)) {
            $this->User_model->update($user_id, $patch);
        }
    }

    private function _merge_hr_schedule_into_update(array &$update) {
        if ($this->db->field_exists('work_start', 'users')) {
            $ws = $this->input->post('work_start');
            $we = $this->input->post('work_end');
            $update['work_start'] = ($ws !== '' && $ws !== null) ? $ws : null;
            $update['work_end']   = ($we !== '' && $we !== null) ? $we : null;
        }
        if ($this->db->field_exists('timezone', 'users')) {
            $tz = trim((string)$this->input->post('timezone'));
            if ($tz === '' || $this->_valid_iana_timezone($tz)) {
                $update['timezone'] = $tz === '' ? null : $tz;
            }
        }
        if ($this->db->field_exists('auto_detect_timezone', 'users')) {
            $update['auto_detect_timezone'] = $this->input->post('auto_detect_timezone') ? 1 : 0;
        }
    }
}

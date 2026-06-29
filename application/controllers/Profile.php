<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Profile extends MY_Controller {

    public function index() {
        $data['user']         = $this->User_model->get_user($this->current_user->id);
        $data['timezones']   = timezone_identifiers_list();
        $data['show_timezone'] = $this->db->field_exists('timezone', 'users');
        $data['show_work_hours'] = $this->db->field_exists('work_start', 'users')
            && !in_array($this->current_user->role_slug, ['client'], true);
        $data['my_tasks']     = $this->Task_model->get_assigned_to($this->current_user->id, 5);
        $data['my_issues']    = $this->Issue_model->get_assigned_to($this->current_user->id, 5);
        $data['activities']   = $this->Activity_model->get_by_user($this->current_user->id, 10);
        $data['total_tasks']  = $this->Task_model->count_assigned_to($this->current_user->id);
        $data['done_tasks']   = $this->Task_model->count_assigned_by_status($this->current_user->id, 'done');
        $data['page_title']   = 'My Profile';
        $this->render('profile/index', $data);
    }

    public function update() {
        $uid = $this->current_user->id;

        $this->form_validation->set_rules('first_name', 'First Name', 'required');
        $this->form_validation->set_rules('last_name',  'Last Name',  'required');

        if ($this->form_validation->run()) {
            $update = [
                'first_name' => $this->input->post('first_name', TRUE),
                'last_name'  => $this->input->post('last_name',  TRUE),
                'phone'      => $this->input->post('phone',      TRUE),
                'job_title'  => $this->input->post('job_title',  TRUE),
                'department' => $this->input->post('department', TRUE),
            ];
            if ($this->db->field_exists('timezone', 'users')) {
                $tz = trim((string)$this->input->post('timezone'));
                if ($tz === '' || in_array($tz, timezone_identifiers_list(), true)) {
                    $update['timezone'] = $tz === '' ? null : $tz;
                }
            }
            if ($this->db->field_exists('auto_detect_timezone', 'users')) {
                $update['auto_detect_timezone'] = $this->input->post('auto_detect_timezone') ? 1 : 0;
            }
            if ($this->db->field_exists('work_start', 'users') && !in_array($this->current_user->role_slug, ['client'], true)) {
                $ws = $this->input->post('work_start');
                $we = $this->input->post('work_end');
                $update['work_start'] = $ws !== '' && $ws !== null ? $ws : null;
                $update['work_end']   = $we !== '' && $we !== null ? $we : null;
            }

            // Handle avatar upload
            if (!empty($_FILES['avatar']['name'])) {
                $this->load->library('upload');
                $config = [
                    'upload_path'   => FCPATH . 'uploads/avatars/',
                    'allowed_types' => 'jpg|jpeg|png|gif',
                    'max_size'      => 2048,
                    'file_name'     => 'avatar_' . $uid . '_' . time(),
                ];
                @mkdir(FCPATH . 'uploads/avatars/', 0755, TRUE);
                $this->upload->initialize($config);
                if ($this->upload->do_upload('avatar')) {
                    $update['avatar'] = 'avatars/' . $this->upload->data('file_name');
                }
            }

            // Handle password change
            $new_pass = $this->input->post('new_password');
            if ($new_pass && strlen($new_pass) >= 6) {
                $update['password'] = password_hash($new_pass, PASSWORD_DEFAULT);
            }

            $this->User_model->update($uid, $update);

            // Refresh session name
            $this->session->set_userdata('user_name',
                $update['first_name'] . ' ' . $update['last_name']);

            $this->log_activity('Updated profile', 'user', $uid);
            $this->session->set_flashdata('success', 'Profile updated successfully.');
        } else {
            $this->session->set_flashdata('error', validation_errors());
        }
        redirect('profile');
    }
}

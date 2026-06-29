<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->library(['session', 'form_validation']);
        $this->load->helper(['url', 'permission']);
        $this->load->database();
    }

    public function index() {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }
        redirect('login');
    }

    public function login() {
        if ($this->session->userdata('user_id')) {
            redirect('dashboard');
        }

        $data['title'] = 'Login — Techfod Task System';
        $data['error'] = $this->session->flashdata('error');
        $data['success'] = $this->session->flashdata('success');

        if ($this->input->post()) {
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

            if ($this->form_validation->run()) {
                $email    = $this->input->post('email', TRUE);
                $password = $this->input->post('password');

                $user = $this->User_model->authenticate($email, $password);

                if ($user) {
                    $permissions = $this->User_model->get_user_permissions($user->id);

                    $this->session->set_userdata([
                        'user_id'     => $user->id,
                        'user_name'   => $user->first_name . ' ' . $user->last_name,
                        'user_email'  => $user->email,
                        'user_avatar' => $user->avatar,
                        'role_id'     => $user->role_id,
                        'role_slug'   => $user->role_slug,
                        'role_name'   => $user->role_name,
                        'client_id'   => $user->client_id,
                        'permissions' => $permissions,
                        'logged_in'   => TRUE,
                    ]);

                    // Update last login
                    $this->User_model->update_last_login($user->id);
                    $this->_apply_login_geo_timezone($user->id);

                    redirect('dashboard');
                } else {
                    $data['error'] = 'Invalid email or password. Please try again.';
                }
            }
        }

        $this->load->view('auth/login', $data);
    }

    /**
     * When the user opts in (auto_detect_timezone), store country from IP/headers
     * and set PHP timezone from browser-reported IANA string when provided.
     */
    private function _apply_login_geo_timezone($user_id) {
        if (!$this->db->field_exists('auto_detect_timezone', 'users')) {
            return;
        }
        $row = $this->db->select('auto_detect_timezone, timezone, last_country_code')
            ->get_where('users', ['id' => $user_id])->row();
        if (!$row || !(int)$row->auto_detect_timezone) {
            return;
        }
        $tzBrowser = trim((string)$this->input->post('client_timezone'));
        if ($tzBrowser && $this->_valid_iana_timezone($tzBrowser) && $this->db->field_exists('timezone', 'users')) {
            $this->User_model->update($user_id, ['timezone' => $tzBrowser]);
        }
        if (!$this->input->post('use_geo_on_login')) {
            // Still allow proxy header country hint below
            $tzFromGeo = null;
            $cc = null;
        } else {
            $tzFromGeo = trim((string)$this->input->post('geo_timezone'));
            if ($tzFromGeo && $this->_valid_iana_timezone($tzFromGeo) && $this->db->field_exists('timezone', 'users')) {
                if (!$tzBrowser) {
                    $this->User_model->update($user_id, ['timezone' => $tzFromGeo]);
                }
            }
            $cc = strtoupper(substr((string)$this->input->post('geo_country_code'), 0, 2));
            if (!preg_match('/^[A-Z]{2}$/', $cc)) {
                $cc = null;
            }
            if ($cc && $this->db->field_exists('last_country_code', 'users') && $cc !== ($row->last_country_code ?? '')) {
                $this->User_model->update($user_id, ['last_country_code' => $cc]);
            }

            if ($cc || $tzFromGeo || $tzBrowser) {
                if ($this->db->table_exists('user_login_events')) {
                    $this->db->insert('user_login_events', [
                        'user_id'        => $user_id,
                        'logged_at'      => date('Y-m-d H:i:s'),
                        'country_code'   => $cc,
                        'timezone'       => $tzFromGeo ?: ($tzBrowser ?: null),
                        'ip_address'     => $this->input->ip_address(),
                        'lat'            => null,
                        'lng'            => null,
                    ]);
                }
            }
        }
        // Fallback: country hint from Cloudflare / common reverse-proxy headers
        $hint = $this->input->server('HTTP_CF_IPCOUNTRY');
        if (!$hint) {
            $hint = $this->input->server('HTTP_X_APPENGINE_COUNTRY');
        }
        if ($hint && strlen($hint) === 2 && $this->db->field_exists('last_country_code', 'users')) {
            $cc = strtoupper($hint);
            if ($cc !== ($row->last_country_code ?? '')) {
                $this->User_model->update($user_id, ['last_country_code' => $cc]);
            }
        }
    }

    private function _valid_iana_timezone($tz) {
        if (strlen($tz) > 64 || !preg_match('/^[A-Za-z0-9_\/+\-]+$/', $tz)) {
            return false;
        }
        return in_array($tz, timezone_identifiers_list(), true);
    }

    public function logout() {
        $this->session->sess_destroy();
        redirect('login');
    }
}

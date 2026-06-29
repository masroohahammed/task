<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function index() {
        if (!has_role('admin')) show_404();
        $data['users']      = $this->User_model->get_all_with_roles();
        $data['roles']      = $this->Role_model->get_all();
        $data['page_title'] = 'User Management';
        $this->render('users/index', $data);
    }

    /** Internal staff are created via HR; client portal users via Clients. */
    public function create() {
        if (!has_role('admin')) show_404();
        redirect('hr/create');
    }

    public function store() {
        if (!has_role('admin')) show_404();
        redirect('hr/create');
    }

    public function edit($id) {
        if (!has_role('admin')) show_404();
        $data['user']       = $this->User_model->get_user($id);
        $data['roles']      = $this->Role_model->get_all();
        $data['clients']    = $this->Client_model->get_all();
        $data['page_title'] = 'Edit User';
        $this->render('users/edit', $data);
    }

    public function update($id) {
        if (!has_role('admin')) show_404();
        $update = [
            'role_id'    => $this->input->post('role_id'),
            'first_name' => $this->input->post('first_name', TRUE),
            'last_name'  => $this->input->post('last_name', TRUE),
            'phone'      => $this->input->post('phone', TRUE),
            'job_title'  => $this->input->post('job_title', TRUE),
            'department' => $this->input->post('department', TRUE),
            'status'     => $this->input->post('status'),
            'client_id'  => null,
        ];
        $role = $this->Role_model->get((int)$this->input->post('role_id'));
        if ($role && $role->slug === 'client') {
            $update['client_id'] = $this->input->post('client_id') ?: null;
        }
        if ($this->input->post('password')) {
            $update['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
        }
        $this->User_model->update($id, $update);
        $this->session->set_flashdata('success', 'User updated.');
        redirect('users');
    }

    public function permissions($id) {
        if (!has_role('admin')) show_404();
        $data['user']        = $this->User_model->get_user($id);
        $data['permissions'] = $this->Role_model->get_all_permissions();
        $data['user_perms']  = $this->User_model->get_user_permissions($id);
        $data['page_title']  = 'User Permissions';
        $this->render('users/permissions', $data);
    }

    public function save_permissions() {
        if (!has_role('admin')) show_404();
        $user_id     = $this->input->post('user_id');
        $permissions = $this->input->post('permissions') ?? [];

        $this->User_model->clear_permissions($user_id);
        foreach ($permissions as $perm_id) {
            $this->User_model->grant_permission($user_id, $perm_id);
        }

        $this->session->set_flashdata('success', 'Permissions updated.');
        redirect('users/permissions/' . $user_id);
    }

    public function delete($id) {
        if (!has_role('admin')) show_404();
        if ($id == $this->current_user->id) {
            $this->session->set_flashdata('error', 'You cannot delete your own account.');
            redirect('users');
        }
        $this->User_model->delete($id);
        $this->session->set_flashdata('success', 'User deleted.');
        redirect('users');
    }
}

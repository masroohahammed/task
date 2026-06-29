<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    public function authenticate($email, $password) {
        $user = $this->db->select('u.*, r.slug as role_slug, r.name as role_name')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.email', $email)
            ->where('u.status', 'active')
            ->get()->row();
        if ($user && password_verify($password, $user->password)) return $user;
        return FALSE;
    }

    public function get_user($id) {
        return $this->db->select('u.*, r.slug as role_slug, r.name as role_name')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.id', $id)->get()->row();
    }

    public function get_all_with_roles() {
        return $this->db->select('u.*, r.name as role_name, r.slug as role_slug')
            ->from('users u')->join('roles r', 'r.id = u.role_id')
            ->order_by('u.first_name')->get()->result();
    }

    public function get_employees() {
        return $this->db->select('u.*, r.name as role_name')
            ->from('users u')->join('roles r', 'r.id = u.role_id')
            ->where_in('r.slug', ['admin','project_manager','employee','hr'])
            ->where('u.status', 'active')->order_by('u.first_name')->get()->result();
    }

    /**
     * Everyone who can be added to a project (internal staff only, not client portal).
     */
    public function get_project_member_candidates() {
        return $this->db->select('u.*, r.name as role_name, r.slug as role_slug')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.slug !=', 'client')
            ->where('u.status', 'active')
            ->order_by('u.first_name')
            ->get()->result();
    }

    public function get_by_client($client_id) {
        return $this->db->select('u.*, r.name as role_name')
            ->from('users u')->join('roles r', 'r.id = u.role_id')
            ->where('u.client_id', $client_id)
            ->where('r.slug', 'client')
            ->order_by('u.first_name')->get()->result();
    }

    public function email_exists($email, $exclude_id = null) {
        $this->db->where('email', $email);
        if ($exclude_id) $this->db->where('id !=', $exclude_id);
        return $this->db->count_all_results('users') > 0;
    }

    public function get_user_permissions($user_id) {
        $user = $this->get_user($user_id);
        if (!$user) return [];
        $role_perms = $this->db->select('p.slug')
            ->from('permissions p')
            ->join('role_permissions rp', 'rp.permission_id = p.id')
            ->where('rp.role_id', $user->role_id)->get()->result_array();
        $perms = array_column($role_perms, 'slug');
        $user_perms = $this->db->select('p.slug, up.granted')
            ->from('permissions p')
            ->join('user_permissions up', 'up.permission_id = p.id')
            ->where('up.user_id', $user_id)->get()->result();
        foreach ($user_perms as $up) {
            if ($up->granted && !in_array($up->slug, $perms)) $perms[] = $up->slug;
            elseif (!$up->granted) $perms = array_diff($perms, [$up->slug]);
        }
        return array_values($perms);
    }

    public function clear_permissions($user_id) {
        $this->db->delete('user_permissions', ['user_id' => $user_id]);
    }

    public function grant_permission($user_id, $permission_id) {
        $this->db->replace('user_permissions', [
            'user_id' => $user_id, 'permission_id' => $permission_id, 'granted' => 1
        ]);
    }

    public function create($data) {
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id)->update('users', $data);
    }

    public function update_last_login($id) {
        $this->db->where('id', $id)->update('users', ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function delete($id) {
        $this->db->delete('users', ['id' => $id]);
    }

    public function count_all() {
        return $this->db->count_all('users');
    }
}

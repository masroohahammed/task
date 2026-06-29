<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role_model extends CI_Model {

    public function get_all() {
        return $this->db->get('roles')->result();
    }

    /** Roles assignable to internal staff (HR / employees), excluding client portal. */
    public function get_staff_roles() {
        return $this->db->where_not_in('slug', ['client'])
            ->order_by('name', 'ASC')
            ->get('roles')
            ->result();
    }

    public function get_all_permissions() {
        return $this->db->get('permissions')->result();
    }

    public function get($id) {
        return $this->db->get_where('roles', ['id' => $id])->row();
    }

    public function get_by_slug($slug) {
        return $this->db->get_where('roles', ['slug' => $slug])->row();
    }

    public function get_client_role_id() {
        $role = $this->get_by_slug('client');
        return $role ? (int)$role->id : 0;
    }
}

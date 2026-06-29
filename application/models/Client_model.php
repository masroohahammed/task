<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model {

    public function get($id) {
        return $this->db->get_where('clients', ['id' => $id])->row();
    }

    public function get_all() {
        return $this->db->where('status', 'active')
            ->order_by('company_name')->get('clients')->result();
    }

    public function get_all_with_stats() {
        $clients = $this->db->order_by('company_name')->get('clients')->result();
        foreach ($clients as &$c) {
            $c->project_count = $this->db->where('client_id', $c->id)->count_all_results('projects');
            $c->ticket_count  = $this->db->where('client_id', $c->id)->count_all_results('tickets');
            $c->user_count    = $this->count_client_users($c->id);
        }
        return $clients;
    }

    // Get all client users (portal logins)
    public function get_users($client_id) {
        $select = 'u.*, r.name as role_name';
        if ($this->db->field_exists('is_client_admin', 'users')) {
            $select .= ', u.is_client_admin';
        }
        return $this->db->select($select)
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.client_id', $client_id)
            ->where('r.slug', 'client')
            ->order_by('u.first_name')
            ->get()->result();
    }

    public function count_client_users($client_id) {
        return $this->db->select('u.id')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.client_id', $client_id)
            ->where('r.slug', 'client')
            ->count_all_results();
    }

    public function create($data) {
        $this->db->insert('clients', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id)->update('clients', $data);
    }

    public function delete($id) {
        $this->load->model('Client_user_project_model');
        $users = $this->db->select('id')->from('users')->where('client_id', $id)->get()->result();
        foreach ($users as $u) {
            $this->Client_user_project_model->remove_user_assignments($u->id);
        }
        $this->db->where('client_id', $id)->delete('users');
        $this->db->delete('clients', ['id' => $id]);
    }

    public function count_all() {
        return $this->db->count_all('clients');
    }
}

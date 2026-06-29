<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model {

    public function get($id) {
        return $this->db->get_where('tickets', ['id' => $id])->row();
    }

    public function get_with_details($id) {
        return $this->db->select('t.*, p.name as project_name,
            c.company_name, c.contact_person,
            a.first_name as assignee_first, a.last_name as assignee_last,
            cr.first_name as creator_first, cr.last_name as creator_last')
            ->from('tickets t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('clients c', 'c.id = t.client_id', 'left')
            ->join('users a', 'a.id = t.assigned_to', 'left')
            ->join('users cr', 'cr.id = t.created_by', 'left')
            ->where('t.id', $id)
            ->get()->row();
    }

    public function get_all_with_details() {
        return $this->db->select('t.*, p.name as project_name, c.company_name,
            a.first_name as assignee_first, a.last_name as assignee_last')
            ->from('tickets t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('clients c', 'c.id = t.client_id', 'left')
            ->join('users a', 'a.id = t.assigned_to', 'left')
            ->order_by('t.created_at', 'DESC')
            ->get()->result();
    }

    // ── ADDED: get tickets by project ──
    public function get_by_project($project_id, $limit = null) {
        $q = $this->db->select('t.*, c.company_name,
            a.first_name as assignee_first, a.last_name as assignee_last')
            ->from('tickets t')
            ->join('clients c', 'c.id = t.client_id', 'left')
            ->join('users a', 'a.id = t.assigned_to', 'left')
            ->where('t.project_id', $project_id)
            ->order_by('t.created_at', 'DESC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    public function get_by_client($client_id, $limit = null) {
        $q = $this->db->select('t.*, p.name as project_name')
            ->from('tickets t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->where('t.client_id', $client_id)
            ->order_by('t.created_at', 'DESC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    /** Tickets for a client portal user (scoped by project assignments when not admin). */
    public function get_for_client_user($client_id, $user_id, $is_client_admin = false, $limit = null) {
        if ($is_client_admin || !$this->db->table_exists('client_user_projects')) {
            return $this->get_by_client($client_id, $limit);
        }
        $q = $this->db->select('t.*, p.name as project_name')
            ->from('tickets t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('client_user_projects cup', 'cup.project_id = t.project_id AND cup.user_id = ' . (int)$user_id)
            ->where('t.client_id', (int)$client_id)
            ->order_by('t.created_at', 'DESC');
        if ($limit) {
            $q->limit($limit);
        }
        return $q->get()->result();
    }

    public function get_assigned_to($user_id, $limit = null) {
        $q = $this->db->select('t.*, p.name as project_name, c.company_name')
            ->from('tickets t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('clients c', 'c.id = t.client_id', 'left')
            ->where('t.assigned_to', $user_id)
            ->order_by('t.created_at', 'DESC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    public function get_recent($limit = 5) {
        return $this->db->select('t.*, p.name as project_name, c.company_name')
            ->from('tickets t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('clients c', 'c.id = t.client_id', 'left')
            ->order_by('t.created_at', 'DESC')
            ->limit($limit)->get()->result();
    }

    public function get_comments($ticket_id) {
        return $this->db->select('tc.*, u.first_name, u.last_name, u.avatar, r.slug as role_slug')
            ->from('ticket_comments tc')
            ->join('users u', 'u.id = tc.user_id')
            ->join('roles r', 'r.id = u.role_id')
            ->where('tc.ticket_id', $ticket_id)
            ->order_by('tc.created_at', 'ASC')
            ->get()->result();
    }

    // ── ADDED: get_project_stats ──
    public function get_project_stats($project_id) {
        $stats = [];
        foreach (['open','in_progress','resolved','closed'] as $s) {
            $stats[$s] = $this->db->where(['project_id' => $project_id, 'status' => $s])
                ->count_all_results('tickets');
        }
        $stats['total'] = array_sum($stats);
        return $stats;
    }

    public function create($data) {
        $this->db->insert('tickets', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id)->update('tickets', $data);
    }

    public function add_comment($data) {
        $this->db->insert('ticket_comments', $data);
        return $this->db->insert_id();
    }

    public function delete($id) {
        $this->db->delete('tickets', ['id' => $id]);
    }

    public function count_by_status($status) {
        return $this->db->where('status', $status)->count_all_results('tickets');
    }

    public function count_by_client($client_id, $status = null) {
        $this->db->where('client_id', $client_id);
        if ($status) $this->db->where('status', $status);
        return $this->db->count_all_results('tickets');
    }
}

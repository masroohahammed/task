<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Issue_model extends CI_Model {

    public function get($id) {
        return $this->db->get_where('issues', ['id' => $id])->row();
    }

    public function get_with_details($id) {
        return $this->db->select('i.*, p.name as project_name,
            r.first_name as reporter_first, r.last_name as reporter_last, r.avatar as reporter_avatar,
            a.first_name as assignee_first, a.last_name as assignee_last, a.avatar as assignee_avatar,
            t.title as task_title')
            ->from('issues i')
            ->join('projects p', 'p.id = i.project_id', 'left')
            ->join('users r', 'r.id = i.reported_by', 'left')
            ->join('users a', 'a.id = i.assigned_to', 'left')
            ->join('tasks t', 't.id = i.task_id', 'left')
            ->where('i.id', $id)
            ->get()->row();
    }

    public function get_all_with_details() {
        return $this->db->select('i.*, p.name as project_name,
            r.first_name as reporter_first, r.last_name as reporter_last,
            a.first_name as assignee_first, a.last_name as assignee_last')
            ->from('issues i')
            ->join('projects p', 'p.id = i.project_id', 'left')
            ->join('users r', 'r.id = i.reported_by', 'left')
            ->join('users a', 'a.id = i.assigned_to', 'left')
            ->order_by('i.created_at', 'DESC')
            ->get()->result();
    }

    public function get_by_project($project_id, $limit = null) {
        $q = $this->db->select('i.*, r.first_name as reporter_first, r.last_name as reporter_last,
            a.first_name as assignee_first, a.last_name as assignee_last')
            ->from('issues i')
            ->join('users r', 'r.id = i.reported_by', 'left')
            ->join('users a', 'a.id = i.assigned_to', 'left')
            ->where('i.project_id', $project_id)
            ->order_by('i.created_at', 'DESC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    public function get_by_status($project_id, $status) {
        return $this->db->select('i.*, r.first_name as reporter_first, r.last_name as reporter_last,
            a.first_name as assignee_first, a.last_name as assignee_last, a.avatar as assignee_avatar')
            ->from('issues i')
            ->join('users r', 'r.id = i.reported_by', 'left')
            ->join('users a', 'a.id = i.assigned_to', 'left')
            ->where('i.project_id', $project_id)
            ->where('i.status', $status)
            ->order_by('i.position ASC, i.created_at DESC')
            ->get()->result();
    }

    public function get_assigned_to($user_id, $limit = null) {
        $q = $this->db->select('i.*, p.name as project_name')
            ->from('issues i')
            ->join('projects p', 'p.id = i.project_id', 'left')
            ->where('i.assigned_to', $user_id)
            ->where_not_in('i.status', ['closed'])
            ->order_by('i.created_at', 'DESC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    public function get_recent($limit = 5) {
        return $this->db->select('i.*, p.name as project_name, u.first_name, u.last_name')
            ->from('issues i')
            ->join('projects p', 'p.id = i.project_id', 'left')
            ->join('users u', 'u.id = i.reported_by', 'left')
            ->order_by('i.created_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }

    public function get_comments($issue_id) {
        return $this->db->select('ic.*, u.first_name, u.last_name, u.avatar')
            ->from('issue_comments ic')
            ->join('users u', 'u.id = ic.user_id')
            ->where('ic.issue_id', $issue_id)
            ->order_by('ic.created_at ASC')
            ->get()->result();
    }

    public function get_project_stats($project_id) {
        $stats = [];
        foreach (['open','in_progress','fixed','retesting','closed','reopened'] as $s) {
            $stats[$s] = $this->db->where(['project_id' => $project_id, 'status' => $s])->count_all_results('issues');
        }
        $stats['total'] = array_sum($stats);
        return $stats;
    }

    public function get_status_stats() {
        $stats = [];
        foreach (['open','in_progress','fixed','retesting','closed'] as $s) {
            $stats[$s] = $this->db->where('status', $s)->count_all_results('issues');
        }
        return $stats;
    }

    public function create($data) {
        $this->db->insert('issues', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id)->update('issues', $data);
    }

    public function add_comment($data) {
        $this->db->insert('issue_comments', $data);
        return $this->db->insert_id();
    }

    public function delete($id) {
        $this->db->delete('issues', ['id' => $id]);
    }

    public function count_by_status($status) {
        return $this->db->where('status', $status)->count_all_results('issues');
    }
}

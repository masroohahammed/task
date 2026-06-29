<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Task_model extends CI_Model {

    public function get($id) {
        return $this->db->get_where('tasks', ['id' => $id])->row();
    }

    public function get_with_details($id) {
        return $this->db->select('t.*, u.first_name as assignee_first, u.last_name as assignee_last,
            u.avatar as assignee_avatar, c.first_name as creator_first, c.last_name as creator_last,
            p.name as project_name')
            ->from('tasks t')
            ->join('users u', 'u.id = t.assigned_to', 'left')
            ->join('users c', 'c.id = t.created_by', 'left')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->where('t.id', $id)
            ->get()->row();
    }

    public function get_with_assignee($id) {
        return $this->db->select('t.*, u.first_name, u.last_name, u.avatar')
            ->from('tasks t')
            ->join('users u', 'u.id = t.assigned_to', 'left')
            ->where('t.id', $id)
            ->get()->row();
    }

    public function get_by_project($project_id, $limit = null) {
        $q = $this->db->select('t.*, u.first_name, u.last_name, u.avatar')
            ->from('tasks t')
            ->join('users u', 'u.id = t.assigned_to', 'left')
            ->where('t.project_id', $project_id)
            ->order_by('t.created_at', 'DESC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    public function get_by_status($project_id, $status) {
        return $this->db->select('t.*, u.first_name, u.last_name, u.avatar')
            ->from('tasks t')
            ->join('users u', 'u.id = t.assigned_to', 'left')
            ->where('t.project_id', $project_id)
            ->where('t.status', $status)
            ->order_by('t.position ASC, t.created_at DESC')
            ->get()->result();
    }

    public function get_assigned_to($user_id, $limit = null) {
        $q = $this->db->select('t.*, p.name as project_name')
            ->from('tasks t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->where('t.assigned_to', $user_id)
            ->where('t.status !=', 'done')
            ->order_by('t.deadline ASC');
        if ($limit) $q->limit($limit);
        return $q->get()->result();
    }

    public function get_recent($limit = 5) {
        return $this->db->select('t.*, p.name as project_name, u.first_name, u.last_name')
            ->from('tasks t')
            ->join('projects p', 'p.id = t.project_id', 'left')
            ->join('users u', 'u.id = t.assigned_to', 'left')
            ->order_by('t.created_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }

    public function get_comments($task_id) {
        return $this->db->select('tc.*, u.first_name, u.last_name, u.avatar')
            ->from('task_comments tc')
            ->join('users u', 'u.id = tc.user_id')
            ->where('tc.task_id', $task_id)
            ->order_by('tc.created_at ASC')
            ->get()->result();
    }

    public function get_updates($task_id) {
        return $this->db->select('tu.*, u.first_name, u.last_name, u.avatar')
            ->from('task_updates tu')
            ->join('users u', 'u.id = tu.user_id')
            ->where('tu.task_id', $task_id)
            ->order_by('tu.created_at DESC')
            ->get()->result();
    }

    public function get_files($task_id) {
        return $this->db->where(['reference_type' => 'task', 'reference_id' => $task_id])->get('files')->result();
    }

    public function get_project_stats($project_id) {
        $stats = [];
        foreach (['todo','in_progress','testing','done'] as $s) {
            $stats[$s] = $this->db->where(['project_id' => $project_id, 'status' => $s])->count_all_results('tasks');
        }
        $stats['total'] = array_sum($stats);
        return $stats;
    }

    public function get_status_stats() {
        $stats = [];
        foreach (['todo','in_progress','testing','done'] as $s) {
            $stats[$s] = $this->db->where('status', $s)->count_all_results('tasks');
        }
        return $stats;
    }

    public function create($data) {
        $this->db->insert('tasks', $data);
        return $this->db->insert_id();
    }

    public function update($id, $data) {
        $this->db->where('id', $id)->update('tasks', $data);
    }

    public function add_comment($data) {
        $this->db->insert('task_comments', $data);
        return $this->db->insert_id();
    }

    public function add_update($data) {
        $this->db->insert('task_updates', $data);
    }

    public function delete($id) {
        $this->db->delete('tasks', ['id' => $id]);
    }

    public function count_all() {
        return $this->db->count_all('tasks');
    }

    public function count_assigned_to($user_id) {
        return $this->db->where('assigned_to', $user_id)->count_all_results('tasks');
    }

    public function count_assigned_by_status($user_id, $status) {
        return $this->db->where(['assigned_to' => $user_id, 'status' => $status])->count_all_results('tasks');
    }
}

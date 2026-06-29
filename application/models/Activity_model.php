<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity_model extends CI_Model {

    public function log($data) {
        $this->db->insert('activity_logs', $data);
    }

    public function get_recent($limit = 10) {
        return $this->db->select('al.*, u.first_name, u.last_name, u.avatar')
            ->from('activity_logs al')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->order_by('al.created_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }

    public function get_by_user($user_id, $limit = 10) {
        return $this->db->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('activity_logs')->result();
    }

    public function get_by_reference($type, $id, $limit = 10) {
        return $this->db->select('al.*, u.first_name, u.last_name, u.avatar')
            ->from('activity_logs al')
            ->join('users u', 'u.id = al.user_id', 'left')
            ->where('al.reference_type', $type)
            ->where('al.reference_id', $id)
            ->order_by('al.created_at', 'DESC')
            ->limit($limit)
            ->get()->result();
    }
}

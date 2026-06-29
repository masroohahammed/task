<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_model extends CI_Model {

    public function create($data) {
        $this->db->insert('notifications', $data);
        return $this->db->insert_id();
    }

    public function get_latest($user_id, $limit = 5) {
        return $this->db->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get('notifications')->result();
    }

    public function get_all_for_user($user_id) {
        return $this->db->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->get('notifications')->result();
    }

    public function get_unread_count($user_id) {
        return $this->db->where(['user_id' => $user_id, 'is_read' => 0])->count_all_results('notifications');
    }

    public function mark_read($id, $user_id) {
        $this->db->where(['id' => $id, 'user_id' => $user_id])->update('notifications', ['is_read' => 1]);
    }

    public function mark_all_read($user_id) {
        $this->db->where('user_id', $user_id)->update('notifications', ['is_read' => 1]);
    }

    public function notify_members($user_ids, $type, $title, $message, $ref_type = null, $ref_id = null) {
        if (empty($user_ids)) return;
        foreach ($user_ids as $uid) {
            $this->create([
                'user_id'        => $uid,
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'reference_type' => $ref_type,
                'reference_id'   => $ref_id,
            ]);
        }
    }

    public function notify_role($role_slug, $type, $title, $message, $ref_type = null, $ref_id = null) {
        $users = $this->db->select('u.id')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.slug', $role_slug)
            ->where('u.status', 'active')
            ->get()->result();

        foreach ($users as $u) {
            $this->create([
                'user_id'        => $u->id,
                'type'           => $type,
                'title'          => $title,
                'message'        => $message,
                'reference_type' => $ref_type,
                'reference_id'   => $ref_id,
            ]);
        }
    }
}

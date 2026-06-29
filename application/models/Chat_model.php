<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

    // ══════════════════════════════════════════
    // ROOMS
    // ══════════════════════════════════════════

    public function get_user_rooms($user_id) {
        $uid = (int)$user_id;
        $sql = "SELECT cr.*, crm.last_read,
            (SELECT cm2.message FROM chat_messages cm2 WHERE cm2.room_id=cr.id ORDER BY cm2.created_at DESC LIMIT 1) AS last_message,
            (SELECT cm3.created_at FROM chat_messages cm3 WHERE cm3.room_id=cr.id ORDER BY cm3.created_at DESC LIMIT 1) AS last_activity,
            (SELECT COUNT(*) FROM chat_messages cm4 WHERE cm4.room_id=cr.id AND cm4.user_id!={$uid}
             AND (crm.last_read IS NULL OR cm4.created_at>crm.last_read)) AS unread_count,
            (SELECT CONCAT(u2.first_name,' ',u2.last_name) FROM chat_room_members crm2
             JOIN users u2 ON u2.id=crm2.user_id WHERE crm2.room_id=cr.id AND crm2.user_id!={$uid} LIMIT 1) AS other_name,
            (SELECT u3.avatar FROM chat_room_members crm3
             JOIN users u3 ON u3.id=crm3.user_id WHERE crm3.room_id=cr.id AND crm3.user_id!={$uid} LIMIT 1) AS other_avatar,
            (SELECT u4.id FROM chat_room_members crm4
             JOIN users u4 ON u4.id=crm4.user_id WHERE crm4.room_id=cr.id AND crm4.user_id!={$uid} LIMIT 1) AS other_user_id
            FROM chat_rooms cr
            JOIN chat_room_members crm ON crm.room_id=cr.id AND crm.user_id={$uid}
            ORDER BY last_activity DESC";
        return $this->db->query($sql)->result();
    }

    public function get_or_create_direct($user1, $user2) {
        $u1 = (int)$user1; $u2 = (int)$user2;
        $row = $this->db->query("
            SELECT cr.* FROM chat_rooms cr
            JOIN chat_room_members m1 ON m1.room_id=cr.id AND m1.user_id={$u1}
            JOIN chat_room_members m2 ON m2.room_id=cr.id AND m2.user_id={$u2}
            WHERE cr.type='direct' LIMIT 1")->row();
        if ($row) return $row;
        $room_id = $this->create_room(['type'=>'direct','created_by'=>$u1]);
        $this->add_member($room_id, $u1);
        $this->add_member($room_id, $u2);
        return $this->db->get_where('chat_rooms', ['id'=>$room_id])->row();
    }

    public function create_room($data) {
        $this->db->insert('chat_rooms', $data);
        return $this->db->insert_id();
    }

    public function add_member($room_id, $user_id) {
        if (!$this->is_member($room_id, $user_id)) {
            $this->db->insert('chat_room_members', ['room_id'=>$room_id,'user_id'=>$user_id]);
        }
    }

    public function is_member($room_id, $user_id) {
        return $this->db->where(['room_id'=>$room_id,'user_id'=>$user_id])
            ->count_all_results('chat_room_members') > 0;
    }

    public function get_room_members($room_id) {
        return $this->db->select('u.id, u.first_name, u.last_name, u.avatar, u.job_title')
            ->from('chat_room_members crm')
            ->join('users u','u.id=crm.user_id')
            ->where('crm.room_id', (int)$room_id)
            ->order_by('u.first_name', 'ASC')
            ->get()->result();
    }

    /** @return int[] */
    public function get_room_member_ids($room_id) {
        $rows = $this->db->select('user_id')->from('chat_room_members')
            ->where('room_id', (int)$room_id)->get()->result();
        $ids = [];
        foreach ($rows as $r) {
            $ids[] = (int)$r->user_id;
        }
        return $ids;
    }

    // ══════════════════════════════════════════
    // MESSAGES — with day-session JSON caching
    // ══════════════════════════════════════════

    /**
     * Get new messages after a given ID (for live polling)
     */
    public function get_messages($room_id, $after_id = 0, $limit = 60) {
        $this->db->select('cm.id, cm.room_id, cm.user_id, cm.message,
                cm.attachment, cm.created_at,
                u.first_name, u.last_name, u.avatar')
            ->from('chat_messages cm')
            ->join('users u','u.id=cm.user_id')
            ->where('cm.room_id', (int)$room_id);
        if ($after_id) $this->db->where('cm.id >', (int)$after_id);
        return $this->db->order_by('cm.created_at','ASC')->limit($limit)->get()->result();
    }

    /**
     * Load today's messages as JSON session — cached in chat_sessions table.
     * Returns array of message objects.
     */
    public function get_today_session($room_id, $user_id) {
        return $this->get_day_session($room_id, $user_id, date('Y-m-d'));
    }

    /**
     * Load messages for a specific date, cache to chat_sessions JSON.
     */
    public function get_day_session($room_id, $user_id, $date) {
        $room_id = (int)$room_id;
        $user_id = (int)$user_id;

        // Check if cached session exists and is fresh (for past dates, always fresh)
        $isPast = $date !== date('Y-m-d');
        if ($isPast) {
            $cached = $this->db->where([
                'room_id'=>$room_id,'user_id'=>$user_id,'session_date'=>$date
            ])->get('chat_sessions')->row();
            if ($cached && $cached->msg_count > 0) {
                return json_decode($cached->messages, true) ?: [];
            }
        }

        // Fetch from DB
        $messages = $this->db->select('cm.id, cm.room_id, cm.user_id, cm.message,
                cm.attachment, cm.created_at,
                u.first_name, u.last_name, u.avatar')
            ->from('chat_messages cm')
            ->join('users u','u.id=cm.user_id')
            ->where('cm.room_id', $room_id)
            ->where("DATE(cm.created_at) = '{$date}'", NULL, FALSE)
            ->order_by('cm.created_at','ASC')
            ->get()->result_array();

        // Save/update session cache
        $json = json_encode($messages);
        $existing = $this->db->where(['room_id'=>$room_id,'user_id'=>$user_id,'session_date'=>$date])
            ->get('chat_sessions')->row();
        if ($existing) {
            $this->db->where('id',$existing->id)->update('chat_sessions',[
                'messages'=>$json,'msg_count'=>count($messages)
            ]);
        } else {
            $this->db->insert('chat_sessions',[
                'room_id'=>$room_id,'user_id'=>$user_id,
                'session_date'=>$date,'messages'=>$json,'msg_count'=>count($messages)
            ]);
        }
        return $messages;
    }

    /**
     * Get available session dates for a room (for "load older" on scroll-top).
     */
    public function get_session_dates($room_id, $limit = 30) {
        return $this->db->query("
            SELECT DATE(created_at) AS session_date, COUNT(*) AS msg_count,
                   MIN(created_at) AS first_msg, MAX(created_at) AS last_msg
            FROM chat_messages
            WHERE room_id = ?
            GROUP BY DATE(created_at)
            ORDER BY session_date DESC
            LIMIT {$limit}
        ", [(int)$room_id])->result();
    }

    public function get_message($id) {
        return $this->db->select('cm.*, u.first_name, u.last_name, u.avatar')
            ->from('chat_messages cm')->join('users u','u.id=cm.user_id')
            ->where('cm.id',$id)->get()->row();
    }

    public function send_message($data) {
        $this->db->insert('chat_messages', $data);
        $id = $this->db->insert_id();
        // Update today's session cache for the room
        $members = $this->db->where('room_id',(int)$data['room_id'])->get('chat_room_members')->result();
        foreach ($members as $m) {
            try { $this->get_day_session($data['room_id'], $m->user_id, date('Y-m-d')); }
            catch (Exception $e) {}
        }
        return $id;
    }

    /**
     * Other members in a room (excluding sender), for notifications.
     */
    public function get_recipient_user_ids($room_id, $exclude_user_id) {
        $rid = (int)$room_id;
        $ex  = (int)$exclude_user_id;
        $rows = $this->db->select('user_id')->from('chat_room_members')
            ->where('room_id', $rid)->where('user_id !=', $ex)->get()->result();
        $ids = [];
        foreach ($rows as $r) {
            $ids[] = (int)$r->user_id;
        }
        return $ids;
    }

    public function get_room($room_id) {
        return $this->db->get_where('chat_rooms', ['id' => (int)$room_id])->row();
    }

    public function mark_read($room_id, $user_id) {
        $this->db->where(['room_id'=>$room_id,'user_id'=>$user_id])
            ->update('chat_room_members',['last_read'=>date('Y-m-d H:i:s')]);
    }

    // ══════════════════════════════════════════
    // UNREAD / STATUS
    // ══════════════════════════════════════════

    public function get_unread_count($user_id) {
        try {
            $uid = (int)$user_id;
            $row = $this->db->query("
                SELECT COALESCE(SUM(
                    (SELECT COUNT(*) FROM chat_messages cm
                     WHERE cm.room_id=crm.room_id AND cm.user_id!={$uid}
                     AND (crm.last_read IS NULL OR cm.created_at>crm.last_read))
                ),0) AS total
                FROM chat_room_members crm WHERE crm.user_id={$uid}
            ")->row();
            return $row ? (int)$row->total : 0;
        } catch (Exception $e) { return 0; }
    }

    public function get_unread_from($current_user, $other_user) {
        try {
            $cu=(int)$current_user; $ou=(int)$other_user;
            $room = $this->db->query("
                SELECT cr.id FROM chat_rooms cr
                JOIN chat_room_members m1 ON m1.room_id=cr.id AND m1.user_id={$cu}
                JOIN chat_room_members m2 ON m2.room_id=cr.id AND m2.user_id={$ou}
                WHERE cr.type='direct' LIMIT 1")->row();
            if (!$room) return 0;
            $rid=(int)$room->id;
            $row = $this->db->query("
                SELECT COUNT(*) AS cnt FROM chat_messages cm
                JOIN chat_room_members crm ON crm.room_id=cm.room_id AND crm.user_id={$cu}
                WHERE cm.room_id={$rid} AND cm.user_id!={$cu}
                AND (crm.last_read IS NULL OR cm.created_at>crm.last_read)")->row();
            return $row ? (int)$row->cnt : 0;
        } catch (Exception $e) { return 0; }
    }

    public function update_status($user_id, $status = 'online') {
        try {
            $exists = $this->db->where('user_id',(int)$user_id)->count_all_results('user_status');
            if ($exists) {
                $this->db->where('user_id',(int)$user_id)->update('user_status',['status'=>$status,'last_seen'=>date('Y-m-d H:i:s')]);
            } else {
                $this->db->insert('user_status',['user_id'=>(int)$user_id,'status'=>$status,'last_seen'=>date('Y-m-d H:i:s')]);
            }
        } catch (Exception $e) {}
    }

    public function get_online_users() {
        try {
            return $this->db->select('u.id AS user_id, u.first_name, u.last_name, u.avatar, u.job_title, us.status, us.last_seen')
                ->from('user_status us')
                ->join('users u','u.id=us.user_id')
                ->where('u.status','active')
                ->where("us.last_seen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)", NULL, FALSE)
                ->order_by('us.status','ASC')->get()->result();
        } catch (Exception $e) { return []; }
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Chat_model');
        $this->load->model('Notification_model');
    }

    public function user_list() {
        $users  = $this->User_model->get_employees();
        $online = [];
        try {
            $rows   = $this->Chat_model->get_online_users();
            foreach ($rows as $r) $online[$r->user_id] = $r->status;
        } catch(Exception $e) {}
        foreach ($users as &$u) {
            $u->is_online = isset($online[$u->id]) && $online[$u->id] !== 'offline';
            $u->status    = $online[$u->id] ?? 'offline';
            try { $u->unread = $this->Chat_model->get_unread_from($this->current_user->id, $u->id); }
            catch(Exception $e) { $u->unread = 0; }
        }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'users'=>$users,'me'=>$this->current_user->id]));
    }

    public function direct($user_id) {
        try {
            $room  = $this->Chat_model->get_or_create_direct($this->current_user->id, (int)$user_id);
            $other = $this->User_model->get_user((int)$user_id);
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'success'      => true,
                    'room'         => $room,
                    'other_name'   => $other ? trim($other->first_name.' '.$other->last_name) : 'Unknown',
                    'other_avatar' => $other ? $other->avatar : null,
                    'other_id'     => (int)$user_id,
                ]));
        } catch(Exception $e) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>$e->getMessage()]));
        }
    }

    /** Load today's session messages + available session dates */
    public function session($room_id) {
        if (!$this->Chat_model->is_member((int)$room_id, $this->current_user->id)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a member'])); return;
        }
        $this->Chat_model->mark_read((int)$room_id, $this->current_user->id);
        $this->Chat_model->update_status($this->current_user->id, 'online');
        try { $messages = $this->Chat_model->get_today_session((int)$room_id, $this->current_user->id); }
        catch(Exception $e) { $messages = []; }
        try { $dates = $this->Chat_model->get_session_dates((int)$room_id); }
        catch(Exception $e) { $dates = []; }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'messages'=>$messages,'dates'=>$dates,'today'=>date('Y-m-d')]));
    }

    /** Load a specific date's session (for scroll-to-top / history) */
    public function day_session($room_id) {
        $date = $this->input->get('date') ?: date('Y-m-d');
        if (!$this->Chat_model->is_member((int)$room_id, $this->current_user->id)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a member'])); return;
        }
        try { $messages = $this->Chat_model->get_day_session((int)$room_id, $this->current_user->id, $date); }
        catch(Exception $e) { $messages = []; }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'messages'=>$messages,'date'=>$date]));
    }

    /** Poll for new messages after a given ID */
    public function poll($room_id) {
        $after = (int)($this->input->get('after') ?: 0);
        $this->Chat_model->update_status($this->current_user->id, 'online');
        $msgs = $this->Chat_model->get_messages((int)$room_id, $after);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'messages'=>$msgs]));
    }

    public function send() {
        $room_id = (int)$this->input->post('room_id');
        $message = trim($this->input->post('message', TRUE));
        if (!$room_id || !$message) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Missing fields'])); return;
        }
        if (!$this->Chat_model->is_member($room_id, $this->current_user->id)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a member'])); return;
        }
        $id  = $this->Chat_model->send_message(['room_id'=>$room_id,'user_id'=>$this->current_user->id,'message'=>$message]);
        $msg = $this->Chat_model->get_message($id);
        $this->Chat_model->update_status($this->current_user->id, 'online');

        $room = $this->Chat_model->get_room($room_id);
        $sender = trim($this->current_user->first_name . ' ' . $this->current_user->last_name);
        $preview = mb_strlen($message) > 120 ? mb_substr($message, 0, 117) . '…' : $message;
        $title = $room && $room->type === 'group' && !empty($room->name)
            ? 'Group: ' . $room->name
            : 'Message from ' . $sender;
        foreach ($this->Chat_model->get_recipient_user_ids($room_id, $this->current_user->id) as $uid) {
            $this->Notification_model->create([
                'user_id'        => $uid,
                'type'           => 'chat_message',
                'title'          => $title,
                'message'        => $preview,
                'reference_type' => 'chat',
                'reference_id'   => $room_id,
            ]);
        }

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'message'=>$msg,'id'=>$id]));
    }

    public function create_group() {
        $name    = trim($this->input->post('name', TRUE));
        $members = $this->input->post('members');
        if (!is_array($members)) {
            $members = $members !== null && $members !== '' ? [(int)$members] : [];
        }
        $members = array_map('intval', $members);
        if (!$name) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Name required'])); return;
        }
        $room_id = $this->Chat_model->create_room(['type'=>'group','name'=>$name,'created_by'=>$this->current_user->id]);
        foreach (array_unique(array_merge([$this->current_user->id], $members)) as $uid) {
            if ($uid > 0) {
                $this->Chat_model->add_member($room_id, $uid);
            }
        }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'room_id'=>$room_id,'name'=>$name]));
    }

    /** Group room: current members + room meta (for widget). */
    public function room_info($room_id) {
        $rid = (int)$room_id;
        if (!$this->Chat_model->is_member($rid, $this->current_user->id)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a member'])); return;
        }
        $room = $this->Chat_model->get_room($rid);
        if (!$room || $room->type !== 'group') {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a group chat'])); return;
        }
        $members = $this->Chat_model->get_room_members($rid);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'room'=>$room,'members'=>$members]));
    }

    /** Add users to an existing group (caller must already be a member). */
    public function add_members($room_id) {
        $rid = (int)$room_id;
        if (!$this->Chat_model->is_member($rid, $this->current_user->id)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a member'])); return;
        }
        $room = $this->Chat_model->get_room($rid);
        if (!$room || $room->type !== 'group') {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Not a group chat'])); return;
        }
        $raw = $this->input->post('members');
        if (!is_array($raw)) {
            $raw = $raw !== null && $raw !== '' ? [(int)$raw] : [];
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $raw))));
        $allowed = [];
        foreach ($this->User_model->get_employees() as $u) {
            $allowed[(int)$u->id] = true;
        }
        $added = 0;
        foreach ($ids as $uid) {
            if ($uid <= 0 || !isset($allowed[$uid])) {
                continue;
            }
            if (!$this->Chat_model->is_member($rid, $uid)) {
                $this->Chat_model->add_member($rid, $uid);
                $added++;
                $this->Notification_model->create([
                    'user_id'        => $uid,
                    'type'           => 'chat_message',
                    'title'          => 'Added to group: ' . ($room->name ?: 'Chat'),
                    'message'        => trim($this->current_user->first_name . ' ' . $this->current_user->last_name) . ' added you to a group chat.',
                    'reference_type' => 'chat',
                    'reference_id'   => $rid,
                ]);
            }
        }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'added'=>$added]));
    }

    public function heartbeat() {
        $this->Chat_model->update_status($this->current_user->id, 'online');
        $unread = $this->Chat_model->get_unread_count($this->current_user->id);
        $online = $this->Chat_model->get_online_users();
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'unread'=>(int)$unread,'online'=>$online]));
    }

    public function rooms() {
        $rooms = $this->Chat_model->get_user_rooms($this->current_user->id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'rooms'=>$rooms]));
    }

    public function set_work_hours($user_id) {
        if (!has_role(['admin','hr'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Unauthorized'])); return;
        }
        $this->User_model->update((int)$user_id,[
            'work_start' => $this->input->post('work_start') ?: NULL,
            'work_end'   => $this->input->post('work_end')   ?: NULL,
        ]);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'message'=>'Work hours saved.']));
    }
}

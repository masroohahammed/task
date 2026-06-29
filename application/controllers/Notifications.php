<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notifications extends MY_Controller {

    public function index() {
        $data['notifications'] = $this->Notification_model->get_all_for_user($this->current_user->id);
        $this->Notification_model->mark_all_read($this->current_user->id);
        $data['page_title'] = 'Notifications';
        $this->render('notifications/index', $data);
    }

    public function mark_read($id) {
        $this->Notification_model->mark_read($id, $this->current_user->id);
        $this->json_response(['success' => true]);
    }

    public function mark_all_read() {
        $this->Notification_model->mark_all_read($this->current_user->id);
        redirect('notifications');
    }
}

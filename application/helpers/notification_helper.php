<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('notification_link_url')) {
    /**
     * Best URL for a notification row (in-app links).
     */
    function notification_link_url($n) {
        if (empty($n->reference_type) || empty($n->reference_id)) {
            return site_url('notifications');
        }
        $t = $n->reference_type;
        $id = (int)$n->reference_id;
        if ($t === 'task') {
            return site_url('tasks/view/' . $id);
        }
        if ($t === 'issue') {
            return site_url('issues/view/' . $id);
        }
        if ($t === 'ticket') {
            return site_url('tickets/view/' . $id);
        }
        if ($t === 'chat') {
            return site_url('dashboard') . '#open-chat-' . $id;
        }
        return site_url('notifications');
    }
}

if (!function_exists('notification_icon_bi')) {
    function notification_icon_bi($type) {
        switch ($type) {
            case 'task_assigned':
                return 'bi-check2-square';
            case 'issue_assigned':
                return 'bi-bug';
            case 'chat_message':
                return 'bi-chat-dots-fill';
            case 'leave_request':
            case 'leave_approved':
            case 'leave_rejected':
                return 'bi-calendar-event';
            default:
                return 'bi-bell-fill';
        }
    }
}

if (!function_exists('notification_icon_color')) {
    function notification_icon_color($type) {
        switch ($type) {
            case 'task_assigned':
                return 'blue';
            case 'issue_assigned':
                return 'red';
            case 'chat_message':
                return 'purple';
            case 'leave_request':
            case 'leave_approved':
            case 'leave_rejected':
                return 'amber';
            default:
                return 'amber';
        }
    }
}

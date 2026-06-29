<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hr_model extends CI_Model {

    public function get_all_employees($status = null) {
        $this->db->select('u.*, r.name as role_name, r.slug as role_slug')
            ->from('users u')
            ->join('roles r', 'r.id = u.role_id')
            ->where_not_in('r.slug', ['client'])
            ->order_by('u.first_name');
        if ($status) $this->db->where('u.status', $status);
        return $this->db->get()->result();
    }

    public function next_emp_number() {
        $row = $this->db->select('COUNT(*) as cnt')->get('users')->row();
        return ($row->cnt ?? 0) + 1;
    }

    public function get_birthdays_today() {
        $today = date('m-d');
        // Pass NULL + FALSE so CI3 uses the string as-is (includes = operator)
        return $this->db
            ->select('u.id, u.first_name, u.last_name, u.avatar, u.job_title')
            ->from('users u')
            ->where("DATE_FORMAT(u.date_of_birth, '%m-%d') = '" . $today . "'", NULL, FALSE)
            ->where('u.status', 'active')
            ->get()->result();
    }

    public function get_upcoming_birthdays($days = 7) {
        $from = date('m-d');
        $to   = date('m-d', strtotime('+' . $days . ' days'));
        return $this->db
            ->select('u.id, u.first_name, u.last_name, u.avatar, u.date_of_birth')
            ->from('users u')
            ->where('u.date_of_birth IS NOT NULL', NULL, FALSE)
            ->where('u.status', 'active')
            ->where("DATE_FORMAT(u.date_of_birth, '%m-%d') >= '" . $from . "'", NULL, FALSE)
            ->where("DATE_FORMAT(u.date_of_birth, '%m-%d') <= '" . $to . "'", NULL, FALSE)
            ->get()->result();
    }

    public function get_present_today() {
        return $this->db->select('a.user_id, a.clock_in, a.clock_out, a.status as att_status,
            u.first_name, u.last_name, u.avatar, u.job_title, us.status as online_status, us.last_seen')
            ->from('attendance a')
            ->join('users u', 'u.id = a.user_id')
            ->join('user_status us', 'us.user_id = a.user_id', 'left')
            ->where('a.work_date', date('Y-m-d'))
            ->order_by('a.clock_in')
            ->get()->result();
    }

    public function get_online_users() {
        return $this->db->select('u.id, u.first_name, u.last_name, u.avatar, u.job_title, us.status, us.last_seen')
            ->from('user_status us')
            ->join('users u', 'u.id = us.user_id')
            ->where('us.status !=', 'offline')
            ->where("us.last_seen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)")
            ->get()->result();
    }

    public function get_work_settings() {
        $rows = $this->db->get('work_settings')->result();
        return array_column((array)$rows, 'setting_value', 'setting_key');
    }
}

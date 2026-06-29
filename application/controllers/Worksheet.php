<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Worksheet extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model');
    }

    // ── My worksheet page ─────────────────────────────────
    public function index() {
        $uid   = $this->current_user->id;
        $date  = $this->input->get('date') ?: date('Y-m-d');
        $month = $this->input->get('month') ?: date('m');
        $year  = $this->input->get('year')  ?: date('Y');

        $data['worksheet']   = $this->Attendance_model->get_worksheet($uid, $date);
        $data['history']     = $this->Attendance_model->get_worksheets($uid, 30);
        $data['today_att']   = $this->Attendance_model->get_today($uid);
        $data['stats']       = $this->Attendance_model->get_user_stats($uid, $month, $year);
        $data['selected_date'] = $date;
        $data['month']       = $month;
        $data['year']        = $year;
        $data['page_title']  = 'Daily Worksheet';
        $this->render('worksheet/index', $data);
    }

    // ── AJAX save ─────────────────────────────────────────
    public function save() {
        $uid  = $this->current_user->id;
        $data = [
            'work_date'     => $this->input->post('work_date')    ?: date('Y-m-d'),
            'tasks_done'    => $this->input->post('tasks_done',    TRUE),
            'plan_tomorrow' => $this->input->post('plan_tomorrow', TRUE),
            'blockers'      => $this->input->post('blockers',      TRUE),
            'total_hours'   => $this->input->post('total_hours')  ?: 0,
            'mood'          => $this->input->post('mood')         ?: 'good',
        ];
        if (empty($data['tasks_done'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success'=>false,'message'=>'Please describe tasks done.']));
            return;
        }
        $id = $this->Attendance_model->save_worksheet($uid, $data);
        $this->log_activity('Saved worksheet for '.$data['work_date'], 'worksheet', $id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success'=>true,'message'=>'Worksheet saved!','id'=>$id]));
    }

    // ── Admin: all worksheets for a date ──────────────────
    public function admin() {
        if (!has_role(['admin','project_manager','hr'])) redirect('dashboard');
        $date = $this->input->get('date') ?: date('Y-m-d');
        $data['worksheets']  = $this->Attendance_model->get_all_worksheets($date);
        $data['date']        = $date;
        $data['page_title']  = 'Team Worksheets';
        $this->render('worksheet/admin', $data);
    }
}

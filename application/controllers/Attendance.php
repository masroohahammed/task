<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model');
    }

    // ── Main unified page ─────────────────────────────
    public function index() {
        $uid   = $this->current_user->id;
        $month = $this->input->get('month') ?: date('m');
        $year  = $this->input->get('year')  ?: date('Y');

        $data['today']          = $this->Attendance_model->get_today($uid);
        $data['shift']          = $this->Attendance_model->get_shift_times($uid);
        $data['open_break']     = $this->Attendance_model->get_open_break($uid);
        $data['today_breaks']   = $this->Attendance_model->get_today_breaks($uid);
        $data['history']        = $this->Attendance_model->get_user_history($uid, $month, $year);
        $data['stats']          = $this->Attendance_model->get_user_stats($uid, $month, $year);
        $data['worksheet']      = $this->Attendance_model->get_worksheet($uid, date('Y-m-d'));
        $data['my_leaves']      = $this->Attendance_model->get_user_leaves($uid);
        $data['pending_leaves'] = has_role(['admin','project_manager'])
            ? $this->Attendance_model->get_pending_leaves() : [];
        $data['all_leaves']     = has_role(['admin','project_manager'])
            ? $this->Attendance_model->get_all_leaves($month, $year) : [];
        $data['month']          = $month;
        $data['year']           = $year;
        $data['page_title']     = 'Attendance & Leave';
        $this->render('attendance/index', $data);
    }

    // ── AJAX: Clock In ────────────────────────────────
    public function clock_in() {
        $uid    = $this->current_user->id;
        $result = $this->Attendance_model->clock_in($uid, $this->input->post('note', TRUE));
        if ($result['success']) {
            $this->log_activity('Clocked in', 'attendance', $uid);
        }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    // ── AJAX: Clock Out ───────────────────────────────
    public function clock_out() {
        $uid    = $this->current_user->id;
        $result = $this->Attendance_model->clock_out(
            $uid,
            $this->input->post('note', TRUE),
            $this->input->post('early_clockout_reason', TRUE)
        );
        if ($result['success']) {
            $this->log_activity('Clocked out', 'attendance', $uid);
        }
        $this->output->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /** AJAX: validate worksheet + breaks before showing clock-out modal */
    public function pre_clock_out() {
        $uid = $this->current_user->id;
        $req = $this->Attendance_model->get_clock_out_requirements($uid);
        $shift = $this->Attendance_model->get_shift_times($uid);
        $out_min = (int)date('H') * 60 + (int)date('i');
        $end_min = $this->_minutes_from_time($shift['work_end']);
        $this->output->set_content_type('application/json')->set_output(json_encode(array_merge($req, [
            'duty_end'        => $shift['work_end'],
            'early_candidate' => $out_min < $end_min,
        ])));
    }

    public function break_start() {
        $uid = $this->current_user->id;
        $r   = $this->Attendance_model->break_start($uid);
        if ($r['success']) {
            $this->log_activity('Break started', 'attendance', $uid);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($r));
    }

    public function break_end() {
        $uid = $this->current_user->id;
        $r   = $this->Attendance_model->break_end($uid);
        if ($r['success']) {
            $this->log_activity('Break ended', 'attendance', $uid);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($r));
    }

    // ── AJAX: get current clock status (for topbar) ──
    public function get_status() {
        $uid   = $this->current_user->id;
        $today = $this->Attendance_model->get_today($uid);
        $req   = $today && !$today->clock_out
            ? $this->Attendance_model->get_clock_out_requirements($uid)
            : ['success' => true];
        $open_break = $this->Attendance_model->get_open_break($uid);
        $shift = $this->Attendance_model->get_shift_times($uid);
        $this->output->set_content_type('application/json')->set_output(json_encode([
            'clocked_in'            => $today && !$today->clock_out,
            'clocked_out'           => $today && $today->clock_out,
            'clock_in_time'         => $today ? date('h:i A', strtotime($today->clock_in)) : null,
            'total_hours'           => $today ? $today->total_hours : null,
            'can_clock_out'         => !empty($req['success']),
            'clock_out_block_reason'=> empty($req['success']) ? ($req['message'] ?? '') : null,
            'clock_out_block_code'  => empty($req['success']) ? ($req['code'] ?? null) : null,
            'on_break'              => (bool)$open_break,
            'duty_end'              => $shift['work_end'],
        ]));
    }

    private function _minutes_from_time($t) {
        $p = explode(':', $t);
        return (int)($p[0] ?? 0) * 60 + (int)($p[1] ?? 0);
    }

    // ── AJAX: Save Worksheet ──────────────────────────
    public function save_worksheet() {
        $uid  = $this->current_user->id;
        $data = [
            'work_date'     => $this->input->post('work_date') ?: date('Y-m-d'),
            'tasks_done'    => $this->input->post('tasks_done', TRUE),
            'plan_tomorrow' => $this->input->post('plan_tomorrow', TRUE),
            'blockers'      => $this->input->post('blockers', TRUE),
            'total_hours'   => $this->input->post('total_hours') ?: 0,
            'mood'          => $this->input->post('mood') ?: 'good',
        ];
        if (empty($data['tasks_done'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Please describe tasks done.']));
            return;
        }
        $id = $this->Attendance_model->save_worksheet($uid, $data);
        $this->log_activity('Saved daily worksheet', 'worksheet', $id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Worksheet saved successfully!']));
    }

    // ── AJAX: Submit Leave Request ────────────────────
    public function leave_request() {
        $uid = $this->current_user->id;
        $from = $this->input->post('from_date');
        $to   = $this->input->post('to_date') ?: $from;
        $reason = $this->input->post('reason', TRUE);

        if (!$from || !$reason) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Date and reason are required.']));
            return;
        }

        $id = $this->Attendance_model->create_leave([
            'user_id'    => $uid,
            'leave_type' => $this->input->post('leave_type'),
            'from_date'  => $from,
            'to_date'    => $to,
            'reason'     => $reason,
            'status'     => 'pending',
        ]);

        // Notify admins and PMs
        $this->Notification_model->notify_role('admin', 'leave_request',
            'Leave Request: ' . $this->current_user->first_name . ' ' . $this->current_user->last_name,
            ucfirst($this->input->post('leave_type')) . ' leave from ' . $from . ' to ' . $to,
            'leave', $id
        );
        $this->Notification_model->notify_role('project_manager', 'leave_request',
            'Leave Request: ' . $this->current_user->first_name . ' ' . $this->current_user->last_name,
            ucfirst($this->input->post('leave_type')) . ' leave from ' . $from . ' to ' . $to,
            'leave', $id
        );

        $this->log_activity('Submitted leave request', 'leave', $id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Leave request submitted!']));
    }

    // ── AJAX: Approve Leave ───────────────────────────
    public function leave_approve($id) {
        if (!has_role(['admin','project_manager'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
            return;
        }
        $leave = $this->Attendance_model->get_leave($id);
        $this->Attendance_model->approve_leave($id, $this->current_user->id);

        if ($leave) {
            $this->Notification_model->create([
                'user_id'        => $leave->user_id,
                'type'           => 'leave_approved',
                'title'          => 'Leave Request Approved',
                'message'        => 'Your leave request has been approved.',
                'reference_type' => 'leave',
                'reference_id'   => $id,
            ]);
        }
        $this->log_activity('Approved leave request #' . $id, 'leave', $id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Leave approved.']));
    }

    // ── AJAX: Reject Leave ────────────────────────────
    public function leave_reject($id) {
        if (!has_role(['admin','project_manager'])) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Unauthorized']));
            return;
        }
        $leave  = $this->Attendance_model->get_leave($id);
        $reason = $this->input->post('reason', TRUE);
        $this->Attendance_model->reject_leave($id, $this->current_user->id, $reason);

        if ($leave) {
            $this->Notification_model->create([
                'user_id'        => $leave->user_id,
                'type'           => 'leave_rejected',
                'title'          => 'Leave Request Rejected',
                'message'        => $reason ?: 'Your leave request was not approved.',
                'reference_type' => 'leave',
                'reference_id'   => $id,
            ]);
        }
        $this->log_activity('Rejected leave request #' . $id, 'leave', $id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'message' => 'Leave rejected.']));
    }

    // ── AJAX: Cancel Leave (by employee) ─────────────
    public function leave_cancel($id) {
        $this->Attendance_model->cancel_leave($id, $this->current_user->id);
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    // ── Admin: Attendance + Worksheets board ──────────
    public function admin() {
        if (!has_role(['admin','project_manager'])) redirect('dashboard');
        $date  = $this->input->get('date') ?: date('Y-m-d');
        $month = $this->input->get('month') ?: date('m');
        $year  = $this->input->get('year')  ?: date('Y');
        $data['records']     = $this->Attendance_model->get_all($date);
        $data['worksheets']  = $this->Attendance_model->get_all_worksheets($date);
        $data['all_leaves']  = $this->Attendance_model->get_all_leaves($month, $year);
        $data['pending_cnt'] = $this->Attendance_model->count_pending_leaves();
        $data['date']        = $date;
        $data['month']       = $month;
        $data['year']        = $year;
        $data['page_title']  = 'Attendance Management';
        $this->render('attendance/admin', $data);
    }

    // ── Admin: Performance Report ─────────────────────
    public function performance() {
        if (!has_role('admin')) redirect('dashboard');
        $month = $this->input->get('month') ?: date('m');
        $year  = $this->input->get('year')  ?: date('Y');
        $data['summary']    = $this->Attendance_model->get_performance_summary($month, $year);
        $data['month']      = $month;
        $data['year']       = $year;
        $data['month_name'] = date('F', mktime(0, 0, 0, $month, 1, $year));
        $data['page_title'] = 'Performance Report';
        $this->render('attendance/performance', $data);
    }

    // ── Admin: Per-user report ────────────────────────
    public function user_report($user_id) {
        if (!has_role(['admin','project_manager'])) redirect('dashboard');
        $month = $this->input->get('month') ?: date('m');
        $year  = $this->input->get('year')  ?: date('Y');
        $data['report_user'] = $this->User_model->get_user($user_id);
        $data['history']     = $this->Attendance_model->get_user_history($user_id, $month, $year);
        $data['stats']       = $this->Attendance_model->get_user_stats($user_id, $month, $year);
        $data['worksheets']  = $this->Attendance_model->get_worksheets($user_id, 31);
        $data['leaves']      = $this->Attendance_model->get_user_leaves($user_id);
        $data['month']       = $month;
        $data['year']        = $year;
        $data['page_title']  = 'Attendance Report';
        $this->render('attendance/user_report', $data);
    }

    // ── Admin: OT & Delay Report ─────────────────────────
    public function ot_report() {
        if (!has_role(['admin','hr','project_manager'])) redirect('dashboard');
        $month = $this->input->get('month') ?: date('m');
        $year  = $this->input->get('year')  ?: date('Y');
        $data['report']     = $this->Attendance_model->get_ot_delay_report($month, $year);
        $data['month']      = $month;
        $data['year']       = $year;
        $data['month_name'] = date('F', mktime(0,0,0,$month,1,$year));
        $data['page_title'] = 'OT & Delay Report';
        $this->render('attendance/ot_report', $data);
    }

}
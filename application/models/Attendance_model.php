<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_model extends CI_Model {

    // ══════════════════════════════════════
    // CLOCK IN / OUT
    // ══════════════════════════════════════

    public function clock_in($user_id, $note = '') {
        $today    = date('Y-m-d');
        $existing = $this->db->where(['user_id'=>$user_id,'work_date'=>$today])
            ->get('attendance')->row();
        if ($existing) return ['success'=>false,'message'=>'Already clocked in today.'];

        $ws        = $this->_get_work_settings_for_user($user_id);
        $now_min   = (int)date('H')*60 + (int)date('i');
        $start_min = $this->_time_to_min($ws['start']);
        $threshold = (int)$this->_get_setting('late_threshold_minutes', 15);
        $status    = ($now_min > $start_min + $threshold) ? 'late' : 'present';

        $insert = [
            'user_id'   => $user_id,
            'clock_in'  => date('Y-m-d H:i:s'),
            'work_date' => $today,
            'status'    => $status,
            'note'      => $note,
        ];
        $this->db->insert('attendance', $insert);
        return ['success'=>true,'time'=>date('h:i A'),'status'=>$status,
                'work_start'=>$ws['start'],'work_end'=>$ws['end']];
    }

    /**
     * Validate clock-out prerequisites (worksheet, breaks). Does not persist.
     */
    public function get_clock_out_requirements($user_id) {
        $record = $this->db->where(['user_id'=>$user_id,'work_date'=>date('Y-m-d')])
            ->where('clock_out IS NULL')->get('attendance')->row();
        if (!$record) {
            return ['success'=>false,'message'=>'No active clock-in found.'];
        }
        $ws = $this->get_worksheet($user_id, date('Y-m-d'));
        if (!$ws || trim((string)($ws->tasks_done ?? '')) === '') {
            return [
                'success'            => false,
                'code'               => 'worksheet_required',
                'message'            => 'Please complete your daily status (worksheet) before clocking out.',
                'worksheet_required' => true,
            ];
        }
        if ($this->_breaks_table_exists()) {
            $open = $this->db->where('attendance_id', (int)$record->id)->where('break_end IS NULL', null, false)
                ->count_all_results('attendance_breaks');
            if ($open > 0) {
                return [
                    'success'        => false,
                    'code'           => 'break_open',
                    'message'        => 'End your break (break-out) before clocking out.',
                    'break_inactive' => false,
                ];
            }
        }
        return ['success'=>true,'attendance'=>$record];
    }

    public function clock_out($user_id, $note = '', $early_reason = '') {
        $req = $this->get_clock_out_requirements($user_id);
        if (!$req['success']) {
            return $req;
        }
        $record = $req['attendance'];

        $ws_user = $this->_get_work_settings_for_user($user_id);
        $clock_out    = date('Y-m-d H:i:s');
        $out_min      = (int)date('H', strtotime($clock_out))*60 + (int)date('i', strtotime($clock_out));
        $end_min      = $this->_time_to_min($ws_user['end']);
        $is_early     = $out_min < $end_min;
        if ($is_early) {
            $early_reason = trim((string)$early_reason);
            if ($early_reason === '') {
                return [
                    'success'              => false,
                    'code'                 => 'early_reason_required',
                    'message'              => 'You are clocking out before your duty end ('.$ws_user['end'].'). Please provide a reason.',
                    'early_reason_required'=> true,
                    'duty_end'             => $ws_user['end'],
                ];
            }
        }

        $total_hours  = round((strtotime($clock_out)-strtotime($record->clock_in))/3600, 2);
        $ws           = $this->_get_work_settings_for_user($user_id);
        $exp_h        = $this->_calc_expected_hours($ws['start'], $ws['end']);
        $ot_hours     = max(0, round($total_hours - $exp_h, 2));
        $status       = $total_hours < 4 ? 'half_day' : $record->status;

        $update = [
            'clock_out'      => $clock_out,
            'total_hours'    => $total_hours,
            'expected_hours' => $exp_h,
            'status'         => $status,
            'note'           => $note !== '' ? $note : $record->note,
        ];
        if ($this->_has_column('attendance', 'early_clockout_reason')) {
            $update['early_clockout_reason'] = $is_early ? $early_reason : null;
        }

        $this->db->where('id',$record->id)->update('attendance', $update);
        return ['success'=>true,'time'=>date('h:i A'),
                'total_hours'=>$total_hours,'expected_hours'=>$exp_h,'ot_hours'=>$ot_hours,
                'early_clockout'=>$is_early];
    }

    public function get_today($user_id) {
        return $this->db->where(['user_id'=>$user_id,'work_date'=>date('Y-m-d')])
            ->get('attendance')->row();
    }

    // ══════════════════════════════════════
    // BREAKS
    // ══════════════════════════════════════

    public function break_start($user_id) {
        if (!$this->_breaks_table_exists()) {
            return ['success'=>false,'message'=>'Break tracking is not available. Run the database upgrade.'];
        }
        $record = $this->db->where(['user_id'=>$user_id,'work_date'=>date('Y-m-d')])
            ->where('clock_out IS NULL')->get('attendance')->row();
        if (!$record) {
            return ['success'=>false,'message'=>'You must be clocked in to start a break.'];
        }
        $open = $this->db->where('attendance_id', (int)$record->id)->where('break_end IS NULL', null, false)
            ->get('attendance_breaks')->row();
        if ($open) {
            return ['success'=>false,'message'=>'You already have an active break. End it before starting another.'];
        }
        $this->db->insert('attendance_breaks', [
            'attendance_id' => (int)$record->id,
            'break_start'   => date('Y-m-d H:i:s'),
        ]);
        return ['success'=>true,'break_id'=>(int)$this->db->insert_id(),'time'=>date('h:i A')];
    }

    public function break_end($user_id) {
        if (!$this->_breaks_table_exists()) {
            return ['success'=>false,'message'=>'Break tracking is not available. Run the database upgrade.'];
        }
        $record = $this->db->where(['user_id'=>$user_id,'work_date'=>date('Y-m-d')])
            ->where('clock_out IS NULL')->get('attendance')->row();
        if (!$record) {
            return ['success'=>false,'message'=>'No active attendance found.'];
        }
        $open = $this->db->where('attendance_id', (int)$record->id)->where('break_end IS NULL', null, false)
            ->order_by('break_start', 'DESC')->get('attendance_breaks')->row();
        if (!$open) {
            return ['success'=>false,'message'=>'No active break to end.'];
        }
        $this->db->where('id', (int)$open->id)->update('attendance_breaks', [
            'break_end' => date('Y-m-d H:i:s'),
        ]);
        return ['success'=>true,'time'=>date('h:i A')];
    }

    public function get_open_break($user_id) {
        if (!$this->_breaks_table_exists()) {
            return null;
        }
        $record = $this->db->where(['user_id'=>$user_id,'work_date'=>date('Y-m-d')])
            ->where('clock_out IS NULL')->get('attendance')->row();
        if (!$record) {
            return null;
        }
        return $this->db->where('attendance_id', (int)$record->id)->where('break_end IS NULL', null, false)
            ->order_by('break_start', 'DESC')->get('attendance_breaks')->row();
    }

    public function get_today_breaks($user_id) {
        if (!$this->_breaks_table_exists()) {
            return [];
        }
        $record = $this->db->where(['user_id'=>$user_id,'work_date'=>date('Y-m-d')])
            ->get('attendance')->row();
        if (!$record) {
            return [];
        }
        return $this->db->where('attendance_id', (int)$record->id)
            ->order_by('break_start', 'ASC')->get('attendance_breaks')->result();
    }

    /** Duty start/end for UI (per-user or global defaults). */
    public function get_shift_times($user_id) {
        $ws = $this->_get_work_settings_for_user($user_id);
        return ['work_start' => $ws['start'], 'work_end' => $ws['end']];
    }

    public function get_user_history($user_id, $month=null, $year=null) {
        $month = $month ?: date('m'); $year = $year ?: date('Y');
        return $this->db->where('user_id',$user_id)
            ->where("MONTH(work_date)",$month)->where("YEAR(work_date)",$year)
            ->order_by('work_date','DESC')->get('attendance')->result();
    }

    public function get_all($date=null) {
        $this->db->select('a.*, u.first_name, u.last_name, u.avatar, u.job_title')
            ->from('attendance a')->join('users u','u.id=a.user_id');
        if ($date) $this->db->where('a.work_date',$date);
        return $this->db->order_by('a.clock_in','ASC')->get()->result();
    }

    /**
     * Stats with OT + delay breakdown per employee work hours
     */
    public function get_user_stats($user_id, $month=null, $year=null) {
        $rows  = $this->get_user_history($user_id, $month, $year);
        $ws    = $this->_get_work_settings_for_user($user_id);
        $exp_h = $this->_calc_expected_hours($ws['start'], $ws['end']);
        $stats = ['present'=>0,'late'=>0,'half_day'=>0,'absent'=>0,
                  'total_hours'=>0,'expected_hours'=>0,'ot_hours'=>0,
                  'delay_minutes'=>0,'days'=>count($rows),
                  'work_start'=>$ws['start'],'work_end'=>$ws['end'],'expected_daily'=>$exp_h];
        foreach ($rows as $r) {
            if (isset($stats[$r->status])) $stats[$r->status]++;
            $actual = (float)$r->total_hours;
            $stats['total_hours']    += $actual;
            $stats['expected_hours'] += $exp_h;
            if ($actual > $exp_h) $stats['ot_hours'] += round($actual-$exp_h,2);
            if ($r->clock_in) {
                $cin_min = (int)date('H',strtotime($r->clock_in))*60+(int)date('i',strtotime($r->clock_in));
                $s_min   = $this->_time_to_min($ws['start']);
                if ($cin_min > $s_min) $stats['delay_minutes'] += ($cin_min-$s_min);
            }
        }
        $stats['total_hours'] = round($stats['total_hours'],2);
        $stats['ot_hours']    = round($stats['ot_hours'],2);
        return $stats;
    }

    /**
     * Full OT/delay monthly report for all employees (admin)
     */
    public function get_ot_delay_report($month=null, $year=null) {
        $month = $month ?: date('m'); $year = $year ?: date('Y');
        $users = $this->db->select('u.id, u.first_name, u.last_name, u.avatar,
            u.job_title, u.work_start, u.work_end, r.name as role_name')
            ->from('users u')->join('roles r','r.id=u.role_id')
            ->where_not_in('r.slug',['client'])->where('u.status','active')->get()->result();
        $report = [];
        foreach ($users as $u) {
            $ws      = $this->_get_work_settings_for_user($u->id);
            $exp_h   = $this->_calc_expected_hours($ws['start'],$ws['end']);
            $records = $this->db->where('user_id',$u->id)
                ->where("MONTH(work_date)",$month)->where("YEAR(work_date)",$year)
                ->order_by('work_date','ASC')->get('attendance')->result();
            $row = ['user'=>$u,'work_start'=>$ws['start'],'work_end'=>$ws['end'],
                    'expected_daily'=>$exp_h,'records'=>[],'total_days'=>count($records),
                    'total_hours'=>0,'total_expected'=>0,'total_ot'=>0,
                    'total_delay'=>0,'late_count'=>0];
            foreach ($records as $r) {
                $actual    = (float)$r->total_hours;
                $ot        = max(0, round($actual-$exp_h,2));
                $delay_min = 0;
                if ($r->clock_in) {
                    $cin = (int)date('H',strtotime($r->clock_in))*60+(int)date('i',strtotime($r->clock_in));
                    $sin = $this->_time_to_min($ws['start']);
                    $delay_min = max(0,$cin-$sin);
                }
                $row['records'][]      = ['rec'=>$r,'ot'=>$ot,'delay_min'=>$delay_min];
                $row['total_hours']   += $actual;
                $row['total_expected']+= $exp_h;
                $row['total_ot']      += $ot;
                $row['total_delay']   += $delay_min;
                if ($r->status==='late') $row['late_count']++;
            }
            $row['total_hours'] = round($row['total_hours'],2);
            $row['total_ot']    = round($row['total_ot'],2);
            $report[] = $row;
        }
        return $report;
    }

    // ══════════════════════════════════════
    // DAILY WORKSHEET
    // ══════════════════════════════════════

    public function save_worksheet($user_id, $data) {
        $date     = !empty($data['work_date']) ? $data['work_date'] : date('Y-m-d');
        $existing = $this->db->where(['user_id'=>$user_id,'work_date'=>$date])
            ->get('daily_worksheets')->row();
        $row = ['user_id'=>$user_id,'work_date'=>$date,
                'tasks_done'=>$data['tasks_done']??'','plan_tomorrow'=>$data['plan_tomorrow']??'',
                'blockers'=>$data['blockers']??'','total_hours'=>$data['total_hours']??0,
                'mood'=>$data['mood']??'good'];
        if ($existing) { $this->db->where('id',$existing->id)->update('daily_worksheets',$row); return $existing->id; }
        $this->db->insert('daily_worksheets',$row); return $this->db->insert_id();
    }

    public function get_worksheet($user_id,$date=null) {
        $date=$date?:date('Y-m-d');
        return $this->db->where(['user_id'=>$user_id,'work_date'=>$date])->get('daily_worksheets')->row();
    }
    public function get_worksheets($user_id,$limit=30) {
        return $this->db->where('user_id',$user_id)->order_by('work_date','DESC')->limit($limit)->get('daily_worksheets')->result();
    }
    public function get_all_worksheets($date=null) {
        $this->db->select('dw.*, u.first_name, u.last_name, u.avatar, u.job_title')
            ->from('daily_worksheets dw')->join('users u','u.id=dw.user_id');
        if ($date) $this->db->where('dw.work_date',$date);
        return $this->db->order_by('dw.work_date DESC, u.first_name ASC')->get()->result();
    }

    // ══════════════════════════════════════
    // LEAVE REQUESTS
    // ══════════════════════════════════════

    public function create_leave($data) {
        $data['total_days'] = $data['leave_type']==='half_day' ? 0.5
            : max(1, round((strtotime($data['to_date'])-strtotime($data['from_date']))/86400)+1);
        $this->db->insert('leave_requests',$data); return $this->db->insert_id();
    }
    public function get_leave($id) {
        return $this->db->select('lr.*, u.first_name, u.last_name, u.email, u.avatar, u.job_title,
            a.first_name as approver_first, a.last_name as approver_last')
            ->from('leave_requests lr')->join('users u','u.id=lr.user_id')
            ->join('users a','a.id=lr.approved_by','left')->where('lr.id',$id)->get()->row();
    }
    public function get_user_leaves($user_id) {
        return $this->db->select('lr.*, a.first_name as approver_first, a.last_name as approver_last')
            ->from('leave_requests lr')->join('users a','a.id=lr.approved_by','left')
            ->where('lr.user_id',$user_id)->order_by('lr.created_at','DESC')->get()->result();
    }
    public function get_pending_leaves() {
        return $this->db->select('lr.*, u.first_name, u.last_name, u.avatar, u.job_title, r.name as role_name')
            ->from('leave_requests lr')->join('users u','u.id=lr.user_id')
            ->join('roles r','r.id=u.role_id')->where('lr.status','pending')
            ->order_by('lr.created_at','ASC')->get()->result();
    }
    public function get_all_leaves($month=null,$year=null) {
        $this->db->select('lr.*, u.first_name, u.last_name, u.avatar, u.job_title,
            a.first_name as approver_first, a.last_name as approver_last')
            ->from('leave_requests lr')->join('users u','u.id=lr.user_id')
            ->join('users a','a.id=lr.approved_by','left');
        if ($month) $this->db->where("MONTH(lr.from_date)",$month);
        if ($year)  $this->db->where("YEAR(lr.from_date)",$year);
        return $this->db->order_by('lr.created_at','DESC')->get()->result();
    }
    public function approve_leave($id,$uid) { $this->db->where('id',$id)->update('leave_requests',['status'=>'approved','approved_by'=>$uid,'approved_at'=>date('Y-m-d H:i:s')]); }
    public function reject_leave($id,$uid,$reason='') { $this->db->where('id',$id)->update('leave_requests',['status'=>'rejected','approved_by'=>$uid,'approved_at'=>date('Y-m-d H:i:s'),'reject_reason'=>$reason]); }
    public function cancel_leave($id,$user_id) { $this->db->where(['id'=>$id,'user_id'=>$user_id,'status'=>'pending'])->update('leave_requests',['status'=>'cancelled']); }
    public function count_pending_leaves() { return $this->db->where('status','pending')->count_all_results('leave_requests'); }

    // ══════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════

    private function _get_work_settings_for_user($user_id) {
        $sel = 'work_start, work_end';
        if ($this->_has_column('users', 'timezone')) {
            $sel .= ', timezone';
        }
        $user = $this->db->select($sel)->get_where('users',['id'=>$user_id])->row();
        return [
            'start' => ($user && $user->work_start) ? $user->work_start : $this->_get_setting('work_start','09:00'),
            'end'   => ($user && $user->work_end)   ? $user->work_end   : $this->_get_setting('work_end','18:00'),
            'timezone' => ($user && !empty($user->timezone)) ? $user->timezone : null,
        ];
    }

    private function _has_column($table, $column) {
        static $cache = [];
        $k = $table.'.'.$column;
        if (isset($cache[$k])) {
            return $cache[$k];
        }
        try {
            if (!$this->db->table_exists($table)) {
                return $cache[$k] = false;
            }
            $cache[$k] = $this->db->field_exists($column, $table);
            return $cache[$k];
        } catch (Exception $e) {
            return $cache[$k] = false;
        }
    }

    private function _breaks_table_exists() {
        static $v = null;
        if ($v !== null) {
            return $v;
        }
        try {
            return $v = $this->db->table_exists('attendance_breaks');
        } catch (Exception $e) {
            return $v = false;
        }
    }
    private function _get_setting($key,$default='') {
        try { $row=$this->db->where('setting_key',$key)->get('work_settings')->row(); return $row?$row->setting_value:$default; }
        catch(Exception $e){ return $default; }
    }
    private function _time_to_min($t) { $p=explode(':',$t); return (int)($p[0]??0)*60+(int)($p[1]??0); }
    private function _calc_expected_hours($s,$e) { return max(0,round(($this->_time_to_min($e)-$this->_time_to_min($s))/60,2)); }

    public function get_performance_summary($month=null,$year=null) {
        $month=$month?:date('m'); $year=$year?:date('Y');
        $users=$this->db->select('u.id, u.first_name, u.last_name, u.avatar, u.job_title, r.name as role_name')
            ->from('users u')->join('roles r','r.id=u.role_id')
            ->where_in('r.slug',['employee','project_manager','hr'])->where('u.status','active')->get()->result();
        foreach ($users as &$u) {
            $u->attendance   = $this->get_user_stats($u->id,$month,$year);
            $u->tasks_done   = $this->db->where(['assigned_to'=>$u->id,'status'=>'done'])->count_all_results('tasks');
            $u->tasks_total  = $this->db->where('assigned_to',$u->id)->count_all_results('tasks');
            $u->issues_fixed = $this->db->where(['assigned_to'=>$u->id,'status'=>'fixed'])->count_all_results('issues');
            $u->worksheets   = $this->db->where('user_id',$u->id)
                ->where("MONTH(work_date)",$month)->where("YEAR(work_date)",$year)
                ->count_all_results('daily_worksheets');
        }
        return $users;
    }
}

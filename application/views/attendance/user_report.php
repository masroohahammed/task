<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('attendance/performance') ?>">Performance</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= html_escape($report_user->first_name.' '.$report_user->last_name) ?></span>
    </div>
    <h1 class="page-title"><?= html_escape($report_user->first_name.' '.$report_user->last_name) ?></h1>
    <p class="page-subtitle">Attendance Report — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></p>
  </div>
  <div class="page-actions gap-2">
    <select class="form-select form-select-sm" id="fMonth" style="width:120px">
      <?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('M',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
    </select>
    <select class="form-select form-select-sm" id="fYear" style="width:90px">
      <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="window.location='<?= site_url('attendance/user_report/'.$report_user->id) ?>?month='+document.getElementById('fMonth').value+'&year='+document.getElementById('fYear').value">
      <i class="bi bi-filter me-1"></i>Apply
    </button>
  </div>
</div>

<div class="row g-4 mb-4">
  <div class="col-md-3">
    <div class="card card-modern text-center py-4">
      <?= user_avatar($report_user->first_name.' '.$report_user->last_name, $report_user->avatar??null, 64) ?>
      <h6 class="fw-800 mt-3 mb-1"><?= html_escape($report_user->first_name.' '.$report_user->last_name) ?></h6>
      <div class="small text-muted"><?= html_escape($report_user->job_title ?? '') ?></div>
      <span class="badge bg-primary mt-2"><?= html_escape($report_user->role_name) ?></span>
    </div>
  </div>
  <div class="col-md-9">
    <div class="row g-3">
      <div class="col-4"><div class="att-stat-card att-stat-green"><div class="att-stat-val"><?= $stats['present'] ?></div><div class="att-stat-lbl">Present</div></div></div>
      <div class="col-4"><div class="att-stat-card att-stat-amber"><div class="att-stat-val"><?= $stats['late'] ?></div><div class="att-stat-lbl">Late</div></div></div>
      <div class="col-4"><div class="att-stat-card att-stat-purple"><div class="att-stat-val"><?= number_format($stats['total_hours'],1) ?>h</div><div class="att-stat-lbl">Total Hours</div></div></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock me-2 text-primary"></i>Attendance Log</h6>
      </div>
      <div class="card-body p-0">
        <?php if(empty($history)): ?>
        <div class="empty-state py-4"><i class="bi bi-calendar-x"></i><p>No records</p></div>
        <?php else: foreach($history as $h): ?>
        <div class="list-item">
          <div class="list-item-icon <?= $h->status==='present'?'bg-success-soft text-success':($h->status==='late'?'bg-warning-soft text-warning':'bg-danger-soft text-danger') ?>">
            <i class="bi <?= $h->status==='present'?'bi-check-circle':($h->status==='late'?'bi-clock':'bi-dash-circle') ?>"></i>
          </div>
          <div class="list-item-body">
            <div class="fw-600 small"><?= date('D, M d', strtotime($h->work_date)) ?></div>
            <div class="small text-muted">
              <?= date('h:i A',strtotime($h->clock_in)) ?>
              <?= $h->clock_out ? ' — '.date('h:i A',strtotime($h->clock_out)) : ' (no out)' ?>
            </div>
          </div>
          <div class="text-end">
            <?= status_badge($h->status) ?>
            <?php if($h->total_hours): ?><div class="small text-primary fw-700 mt-1"><?= $h->total_hours ?>h</div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-journal-check me-2 text-success"></i>Worksheets</h6>
        <span class="badge bg-success"><?= count($worksheets) ?></span>
      </div>
      <div class="card-body p-0" style="max-height:500px;overflow-y:auto">
        <?php if(empty($worksheets)): ?>
        <div class="empty-state py-4"><i class="bi bi-journal-x"></i><p>No worksheets</p></div>
        <?php else: foreach($worksheets as $w): ?>
        <div class="ws-item">
          <div class="ws-item-head">
            <span class="ws-item-date"><?= date('D, M d', strtotime($w->work_date)) ?></span>
            <div class="d-flex gap-2 align-items-center">
              <span class="small text-muted"><?= $w->total_hours ?>h</span>
              <span><?= ['great'=>'😄','good'=>'🙂','neutral'=>'😐','stressed'=>'😰','bad'=>'😞'][$w->mood] ?></span>
            </div>
          </div>
          <?php if($w->tasks_done): ?><div class="ws-item-text"><?= nl2br(html_escape(substr($w->tasks_done,0,160))) ?></div><?php endif; ?>
          <?php if($w->blockers): ?><div class="ws-item-blocker"><i class="bi bi-exclamation-triangle me-1"></i><?= html_escape(substr($w->blockers,0,80)) ?></div><?php endif; ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-calendar-x me-2 text-warning"></i>Leave Requests</h6>
      </div>
      <div class="card-body p-0">
        <?php if(empty($leaves)): ?>
        <div class="empty-state py-4"><i class="bi bi-calendar-check"></i><p>No leave requests</p></div>
        <?php else: foreach($leaves as $lv): ?>
        <div class="list-item">
          <div class="list-item-icon bg-warning-soft text-warning"><i class="bi bi-calendar-x"></i></div>
          <div class="list-item-body">
            <div class="fw-600 small"><?= ucfirst($lv->leave_type) ?> — <?= $lv->total_days ?> day<?= $lv->total_days>1?'s':'' ?></div>
            <div class="small text-muted"><?= date('M d',strtotime($lv->from_date)) ?><?= $lv->from_date!==$lv->to_date?' – '.date('M d',strtotime($lv->to_date)):'' ?></div>
          </div>
          <?php
          $sb=['pending'=>'bg-warning-soft text-warning','approved'=>'bg-success-soft text-success',
               'rejected'=>'bg-danger-soft text-danger','cancelled'=>'bg-secondary-soft text-muted'];
          ?>
          <span class="badge <?= $sb[$lv->status]??'bg-secondary' ?>"><?= ucfirst($lv->status) ?></span>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.att-stat-card{background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);padding:20px;text-align:center;}
.att-stat-val{font-size:2rem;font-weight:800;line-height:1;}
.att-stat-lbl{font-size:.74rem;color:var(--text-3);margin-top:4px;font-weight:500;}
.att-stat-green .att-stat-val{color:#10b981;}
.att-stat-amber .att-stat-val{color:#f59e0b;}
.att-stat-purple .att-stat-val{color:var(--primary);}
.ws-item{padding:12px 18px;border-bottom:1px solid var(--border-lt);}
.ws-item:last-child{border-bottom:none;}
.ws-item-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;}
.ws-item-date{font-weight:700;font-size:.83rem;}
.ws-item-text{font-size:.8rem;color:var(--text-2);line-height:1.4;}
.ws-item-blocker{font-size:.78rem;color:var(--danger);margin-top:3px;}
</style>

<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('attendance/admin') ?>">Attendance</a>
      <i class="bi bi-chevron-right"></i><span>Performance</span>
    </div>
    <h1 class="page-title">Performance Report</h1>
    <p class="page-subtitle"><?= $month_name ?> <?= $year ?> — Team Overview</p>
  </div>
  <div class="page-actions gap-2">
    <select class="form-select form-select-sm" id="fMonth" style="width:120px">
      <?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
    </select>
    <select class="form-select form-select-sm" id="fYear" style="width:90px">
      <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="window.location='<?= site_url('attendance/performance') ?>?month='+$('#fMonth').val()+'&year='+$('#fYear').val()">
      <i class="bi bi-filter me-1"></i>Apply
    </button>
  </div>
</div>

<?php
$total_emp   = count($summary);
$total_tasks = array_sum(array_column($summary,'tasks_done'));
$total_hours = 0;
foreach($summary as $u) $total_hours += $u->attendance['total_hours'] ?? 0;
?>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="stat-card stat-primary"><div class="stat-icon"><i class="bi bi-people"></i></div><div class="stat-body"><div class="stat-value"><?= $total_emp ?></div><div class="stat-label">Employees</div></div></div></div>
  <div class="col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="bi bi-check2-square"></i></div><div class="stat-body"><div class="stat-value"><?= $total_tasks ?></div><div class="stat-label">Tasks Completed</div></div></div></div>
  <div class="col-md-3"><div class="stat-card stat-indigo"><div class="stat-icon"><i class="bi bi-clock-history"></i></div><div class="stat-body"><div class="stat-value"><?= number_format($total_hours,0) ?>h</div><div class="stat-label">Total Hours</div></div></div></div>
  <div class="col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="bi bi-journal-check"></i></div><div class="stat-body"><div class="stat-value"><?= array_sum(array_column($summary,'worksheets')) ?></div><div class="stat-label">Worksheets</div></div></div></div>
</div>

<div class="card card-modern">
  <div class="card-header-modern">
    <h6 class="card-title-modern"><i class="bi bi-trophy me-2 text-warning"></i>Employee Performance — <?= $month_name.' '.$year ?></h6>
  </div>
  <div class="card-body p-0">
    <?php if(empty($summary)): ?>
    <div class="empty-state py-5"><i class="bi bi-people"></i><h5>No employees found</h5></div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-modern">
        <thead><tr>
          <th>Employee</th><th>Present</th><th>Late</th><th>Half Day</th>
          <th>Total Hrs</th><th>Tasks Done</th><th>Issues Fixed</th><th>Worksheets</th><th>Score</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach($summary as $u):
          $att   = (array)$u->attendance;
          $score = min(($att['present']??0)*4,40) + min(($u->tasks_done??0)*3,30)
                 + min(($u->worksheets??0)*2,20) + min(($u->issues_fixed??0)*1,10);
          $sc    = $score>=80?'text-success':($score>=50?'text-warning':'text-danger');
          $bar   = $score>=80?'bg-success':($score>=50?'bg-warning':'bg-danger');
        ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <?= user_avatar($u->first_name.' '.$u->last_name, $u->avatar??null, 36) ?>
              <div>
                <div class="fw-600 small"><?= html_escape($u->first_name.' '.$u->last_name) ?></div>
                <div class="small text-muted"><?= html_escape($u->job_title ?? $u->role_name) ?></div>
              </div>
            </div>
          </td>
          <td><span class="fw-700 text-success"><?= $att['present']??0 ?></span></td>
          <td><span class="fw-700 text-warning"><?= $att['late']??0 ?></span></td>
          <td><span class="fw-700 text-info"><?= $att['half_day']??0 ?></span></td>
          <td><span class="fw-700 text-primary"><?= number_format($att['total_hours']??0,1) ?>h</span></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="fw-700"><?= $u->tasks_done ?></span>
              <?php if($u->tasks_total>0): ?>
              <div class="progress" style="width:46px;height:5px">
                <div class="progress-bar bg-success" style="width:<?= round($u->tasks_done/$u->tasks_total*100) ?>%"></div>
              </div>
              <?php endif; ?>
            </div>
          </td>
          <td><span class="fw-700 text-danger"><?= $u->issues_fixed ?></span></td>
          <td><span class="fw-700"><?= $u->worksheets ?></span></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="progress" style="width:56px;height:8px;border-radius:4px">
                <div class="progress-bar <?= $bar ?>" style="width:<?= $score ?>%;border-radius:4px"></div>
              </div>
              <span class="fw-800 small <?= $sc ?>"><?= $score ?>/100</span>
            </div>
          </td>
          <td>
            <a href="<?= site_url('attendance/user_report/'.$u->id.'?month='.$month.'&year='.$year) ?>"
               class="btn btn-sm btn-ghost"><i class="bi bi-eye"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

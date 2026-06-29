<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1"><a href="<?= site_url('attendance/performance') ?>">Performance</a><i class="bi bi-chevron-right"></i><span>OT & Delay Report</span></div>
    <h1 class="page-title">OT & Delay Report</h1>
    <p class="page-subtitle"><?= $month_name ?> <?= $year ?> — Overtime and Late Arrival Analysis</p>
  </div>
  <div class="page-actions gap-2">
    <select class="form-select form-select-sm" id="fMonth" style="width:120px">
      <?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?>
    </select>
    <select class="form-select form-select-sm" id="fYear" style="width:90px">
      <?php for($y=date('Y');$y>=date('Y')-3;$y--): ?><option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select>
    <button class="btn btn-primary btn-sm" onclick="window.location='<?= site_url('attendance/ot_report') ?>?month='+document.getElementById('fMonth').value+'&year='+document.getElementById('fYear').value">
      <i class="bi bi-filter me-1"></i>Apply
    </button>
  </div>
</div>

<div class="card card-modern">
  <div class="card-header-modern">
    <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-warning"></i>Employee Work Hours Summary — <?= $month_name.' '.$year ?></h6>
  </div>
  <div class="card-body p-0">
    <?php if(empty($report)): ?>
    <div class="empty-state py-5"><i class="bi bi-people"></i><h5>No data</h5></div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-modern">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Work Hours<br><small class="text-muted fw-400">Start → End</small></th>
            <th>Days</th>
            <th>Total Hrs</th>
            <th>Expected</th>
            <th class="text-success">OT Hours</th>
            <th class="text-danger">Delay Total</th>
            <th class="text-warning">Late Days</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($report as $row):
          $u    = $row['user'];
          $diff = round($row['total_hours'] - $row['total_expected'], 2);
          $cls  = $diff >= 0 ? 'text-success' : 'text-danger';
          $dh   = floor($row['total_delay'] / 60);
          $dm   = $row['total_delay'] % 60;
        ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <?= user_avatar($u->first_name.' '.$u->last_name, $u->avatar??null, 34) ?>
              <div>
                <div class="fw-600 small"><?= html_escape($u->first_name.' '.$u->last_name) ?></div>
                <div class="small text-muted"><?= html_escape($u->job_title??$u->role_name) ?></div>
              </div>
            </div>
          </td>
          <td>
            <div class="small fw-600 text-primary"><?= $row['work_start'] ?> – <?= $row['work_end'] ?></div>
            <div class="small text-muted"><?= $row['expected_daily'] ?>h/day</div>
          </td>
          <td><span class="fw-700"><?= $row['total_days'] ?></span></td>
          <td><span class="fw-700 <?= $cls ?>"><?= $row['total_hours'] ?>h</span></td>
          <td><span class="fw-700 text-muted"><?= $row['total_expected'] ?>h</span></td>
          <td>
            <span class="fw-800 <?= $row['total_ot']>0?'text-success':'text-muted' ?>">
              <?= $row['total_ot'] ?>h
            </span>
          </td>
          <td>
            <span class="fw-700 <?= $row['total_delay']>0?'text-danger':'text-muted' ?>">
              <?= $dh>0 ? $dh.'h ' : '' ?><?= $dm ?>m
            </span>
          </td>
          <td>
            <span class="badge <?= $row['late_count']>3?'bg-danger text-white':($row['late_count']>0?'bg-warning-soft text-warning':'bg-success-soft text-success') ?>">
              <?= $row['late_count'] ?> day<?= $row['late_count']!=1?'s':'' ?>
            </span>
          </td>
          <td>
            <button class="btn btn-sm btn-ghost" onclick="toggleDetail(this,<?= $u->id ?>)" data-uid="<?= $u->id ?>">
              <i class="bi bi-chevron-down"></i>
            </button>
          </td>
        </tr>
        <!-- Expandable detail rows -->
        <tr class="detail-row" id="detail-<?= $u->id ?>" style="display:none">
          <td colspan="9" class="p-0">
            <div class="detail-inner">
              <table class="table table-sm mb-0">
                <thead><tr class="bg-light">
                  <th>Date</th><th>Clock In</th><th>Clock Out</th><th>Hours</th><th>OT</th><th>Delay</th><th>Status</th>
                </tr></thead>
                <tbody>
                <?php foreach($row['records'] as $rd):
                  $r  = $rd['rec'];
                  $dm = floor($rd['delay_min']/60)>0 ? floor($rd['delay_min']/60).'h '.($rd['delay_min']%60).'m' : $rd['delay_min'].'m';
                ?>
                <tr>
                  <td class="small"><?= date('D, M d',strtotime($r->work_date)) ?></td>
                  <td class="small fw-600 <?= $r->status==='late'?'text-warning':'' ?>">
                    <?= date('h:i A',strtotime($r->clock_in)) ?>
                    <?php if($r->status==='late'): ?><i class="bi bi-clock-history text-warning ms-1" title="Late"></i><?php endif; ?>
                  </td>
                  <td class="small"><?= $r->clock_out ? date('h:i A',strtotime($r->clock_out)) : '<span class="text-muted">—</span>' ?></td>
                  <td class="small fw-600"><?= $r->total_hours ?>h</td>
                  <td class="small <?= $rd['ot']>0?'text-success fw-700':'' ?>"><?= $rd['ot']>0?'+'.$rd['ot'].'h':'—' ?></td>
                  <td class="small <?= $rd['delay_min']>0?'text-danger':'' ?>"><?= $rd['delay_min']>0?$dm:'—' ?></td>
                  <td><?= status_badge($r->status) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
.detail-inner{background:#fafbff;border-top:1px solid var(--border);}
[data-theme="dark"] .detail-inner{background:rgba(255,255,255,.03);}
</style>
<script>
function toggleDetail(btn,uid){
  var row=document.getElementById('detail-'+uid);
  var shown=row.style.display!=='none';
  row.style.display=shown?'none':'table-row';
  btn.innerHTML=shown?'<i class="bi bi-chevron-down"></i>':'<i class="bi bi-chevron-up"></i>';
}
</script>

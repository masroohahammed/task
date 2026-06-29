<div class="page-header">
  <div>
    <h1 class="page-title">Attendance Management</h1>
    <p class="page-subtitle">Team clock records, worksheets and leave requests</p>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('attendance/performance') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-graph-up me-1"></i> Performance Report
    </a>
  </div>
</div>

<!-- Admin tabs -->
<ul class="att-tabs" id="adminTabs">
  <li><a class="att-tab active" data-tab="adm-clock" href="#"><i class="bi bi-clock me-1"></i>Clock Records</a></li>
  <li><a class="att-tab" data-tab="adm-sheets" href="#"><i class="bi bi-journal-check me-1"></i>Worksheets</a></li>
  <li>
    <a class="att-tab" data-tab="adm-leaves" href="#">
      <i class="bi bi-calendar-x me-1"></i>Leave Requests
      <?php if ($pending_cnt > 0): ?>
      <span class="tab-badge"><?= $pending_cnt ?></span>
      <?php endif; ?>
    </a>
  </li>
</ul>

<!-- Date filter -->
<div class="filter-bar mb-4">
  <div class="filter-group">
    <label class="filter-label">Date</label>
    <input type="date" class="form-control form-control-sm" id="filterDate" value="<?= $date ?>" style="width:160px">
  </div>
  <button class="btn btn-ghost btn-sm" onclick="window.location='<?= site_url('attendance/admin') ?>?date='+document.getElementById('filterDate').value">
    <i class="bi bi-search me-1"></i> Filter
  </button>
  <button class="btn btn-ghost btn-sm" onclick="window.location='<?= site_url('attendance/admin') ?>'">Today</button>
</div>

<!-- Clock Records -->
<div class="att-panel active" id="tab-adm-clock">
  <div class="card card-modern">
    <div class="card-header-modern">
      <h6 class="card-title-modern"><i class="bi bi-clock me-2 text-primary"></i>Clock Records — <?= date('M d, Y', strtotime($date)) ?></h6>
      <span class="badge bg-primary"><?= count($records) ?> records</span>
    </div>
    <div class="card-body p-0">
      <?php if (empty($records)): ?>
      <div class="empty-state py-5"><i class="bi bi-clock-history"></i><h5>No records for this date</h5></div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-modern">
          <thead><tr><th>Employee</th><th>Clock In</th><th>Clock Out</th><th>Hours</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($records as $r): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-3">
                <?= user_avatar($r->first_name.' '.$r->last_name, $r->avatar ?? null, 36) ?>
                <div>
                  <div class="fw-600 small"><?= html_escape($r->first_name.' '.$r->last_name) ?></div>
                  <div class="small text-muted"><?= html_escape($r->job_title ?? '') ?></div>
                </div>
              </div>
            </td>
            <td><span class="small fw-700 text-success"><?= date('h:i A', strtotime($r->clock_in)) ?></span></td>
            <td>
              <?php if ($r->clock_out): ?>
              <span class="small fw-700 text-danger"><?= date('h:i A', strtotime($r->clock_out)) ?></span>
              <?php else: ?>
              <span class="badge bg-success-soft text-success"><i class="bi bi-record-circle me-1"></i>Active</span>
              <?php endif; ?>
            </td>
            <td><span class="fw-800 text-primary"><?= $r->total_hours ? $r->total_hours.'h' : '—' ?></span></td>
            <td><?= status_badge($r->status) ?></td>
            <td>
              <a href="<?= site_url('attendance/user_report/'.$r->user_id) ?>" class="btn btn-sm btn-ghost" title="Full Report">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Worksheets -->
<div class="att-panel" id="tab-adm-sheets">
  <div class="card card-modern">
    <div class="card-header-modern">
      <h6 class="card-title-modern"><i class="bi bi-journal-check me-2 text-success"></i>Worksheets — <?= date('M d, Y', strtotime($date)) ?></h6>
      <span class="badge bg-success"><?= count($worksheets) ?></span>
    </div>
    <div class="card-body p-0">
      <?php if (empty($worksheets)): ?>
      <div class="empty-state py-5"><i class="bi bi-journal-x"></i><p>No worksheets for this date</p></div>
      <?php else: foreach ($worksheets as $w): ?>
      <div class="ws-card-admin">
        <div class="ws-admin-header">
          <div class="d-flex align-items-center gap-3">
            <?= user_avatar($w->first_name.' '.$w->last_name, $w->avatar ?? null, 36) ?>
            <div>
              <div class="fw-700 small"><?= html_escape($w->first_name.' '.$w->last_name) ?></div>
              <div class="small text-muted"><?= html_escape($w->job_title ?? '') ?></div>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <span class="fw-700 text-primary small"><?= $w->total_hours ?>h</span>
            <span class="fs-5"><?= ['great'=>'😄','good'=>'🙂','neutral'=>'😐','stressed'=>'😰','bad'=>'😞'][$w->mood] ?></span>
          </div>
        </div>
        <?php if ($w->tasks_done): ?>
        <div class="ws-admin-section">
          <div class="ws-admin-lbl">✅ Tasks Done</div>
          <div class="ws-admin-val"><?= nl2br(html_escape($w->tasks_done)) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($w->plan_tomorrow): ?>
        <div class="ws-admin-section">
          <div class="ws-admin-lbl">📅 Tomorrow's Plan</div>
          <div class="ws-admin-val text-muted"><?= nl2br(html_escape($w->plan_tomorrow)) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($w->blockers): ?>
        <div class="ws-admin-section">
          <div class="ws-admin-lbl text-danger">🚧 Blockers</div>
          <div class="ws-admin-val text-danger"><?= nl2br(html_escape($w->blockers)) ?></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- Leave Requests -->
<div class="att-panel" id="tab-adm-leaves">
  <?php if (!empty($all_leaves)): ?>
  <div class="card card-modern">
    <div class="card-header-modern">
      <h6 class="card-title-modern"><i class="bi bi-calendar-x me-2 text-warning"></i>Leave Requests — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></h6>
    </div>
    <div class="table-responsive">
      <table class="table table-hover table-modern">
        <thead><tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>By</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($all_leaves as $lv): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <?= user_avatar($lv->first_name.' '.$lv->last_name, $lv->avatar ?? null, 30) ?>
              <span class="small fw-600"><?= html_escape($lv->first_name.' '.$lv->last_name) ?></span>
            </div>
          </td>
          <td><span class="small"><?= ucfirst($lv->leave_type) ?></span></td>
          <td><span class="small"><?= date('M d', strtotime($lv->from_date)) ?></span></td>
          <td><span class="small"><?= date('M d', strtotime($lv->to_date)) ?></span></td>
          <td><span class="fw-700"><?= $lv->total_days ?></span></td>
          <td><span class="small text-muted"><?= html_escape(substr($lv->reason,0,50)) ?><?= strlen($lv->reason)>50?'…':'' ?></span></td>
          <td>
            <?php
            $sbadge = ['pending'=>'bg-warning-soft text-warning','approved'=>'bg-success-soft text-success',
                       'rejected'=>'bg-danger-soft text-danger','cancelled'=>'bg-secondary-soft text-muted'];
            ?>
            <span class="badge <?= $sbadge[$lv->status] ?? 'bg-secondary' ?>"><?= ucfirst($lv->status) ?></span>
          </td>
          <td><span class="small text-muted"><?= $lv->approver_first ? html_escape($lv->approver_first.' '.$lv->approver_last) : '—' ?></span></td>
          <td>
            <?php if ($lv->status === 'pending'): ?>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-success" onclick="quickApprove(<?= $lv->id ?>,this)"><i class="bi bi-check-lg"></i></button>
              <button class="btn btn-sm btn-ghost text-danger" onclick="quickReject(<?= $lv->id ?>)"><i class="bi bi-x-lg"></i></button>
            </div>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php else: ?>
  <div class="empty-state py-5"><i class="bi bi-calendar-check"></i><h5>No leave requests this month</h5></div>
  <?php endif; ?>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title">Reject Leave</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="rejectId">
        <label class="form-label fw-600">Reason</label>
        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Reason for rejection..."></textarea>
      </div>
      <div class="modal-footer-modern">
        <button class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger btn-sm" id="btnDoReject"><i class="bi bi-x-lg me-1"></i>Reject</button>
      </div>
    </div>
  </div>
</div>

<style>
.ws-card-admin { padding:16px 20px; border-bottom:1px solid var(--border-lt); }
.ws-card-admin:last-child { border-bottom:none; }
.ws-admin-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
.ws-admin-section { margin-bottom:6px; }
.ws-admin-lbl { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:var(--text-3); margin-bottom:2px; }
.ws-admin-val { font-size:.83rem; line-height:1.5; }
</style>

<script>
var BASE = '<?= base_url() ?>';
document.querySelectorAll('.att-tab').forEach(function(tab){
  tab.addEventListener('click',function(e){
    e.preventDefault();
    document.querySelectorAll('.att-tab').forEach(function(t){ t.classList.remove('active'); });
    document.querySelectorAll('.att-panel').forEach(function(p){ p.classList.remove('active'); });
    this.classList.add('active');
    document.getElementById('tab-'+this.dataset.tab).classList.add('active');
  });
});

function quickApprove(id, btn) {
  btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';
  $.post(BASE+'attendance/leave_approve/'+id,{},function(r){
    if(r.success){ location.reload(); }
  },'json');
}

var _rId=null;
function quickReject(id){ _rId=id; document.getElementById('rejectReason').value=''; new bootstrap.Modal(document.getElementById('rejectModal')).show(); }
document.getElementById('btnDoReject').addEventListener('click',function(){
  if(!_rId)return;
  $.post(BASE+'attendance/leave_reject/'+_rId,{reason:document.getElementById('rejectReason').value},function(r){
    if(r.success){ bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide(); location.reload(); }
  },'json');
});
</script>

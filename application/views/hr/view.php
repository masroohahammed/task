<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1"><a href="<?= site_url('hr') ?>">HR</a><i class="bi bi-chevron-right"></i><span><?= html_escape($emp->first_name.' '.$emp->last_name) ?></span></div>
    <h1 class="page-title"><?= html_escape($emp->first_name.' '.$emp->last_name) ?></h1>
    <div class="d-flex gap-2 mt-1">
      <span class="badge <?= ['active'=>'bg-success','inactive'=>'bg-warning','resigned'=>'bg-danger'][$emp->status]??'bg-secondary' ?> text-white"><?= ucfirst($emp->status) ?></span>
      <span class="badge bg-primary text-white"><?= html_escape($emp->role_name) ?></span>
      <?php if($emp->employee_id): ?><span class="badge bg-secondary-soft text-muted"><?= html_escape($emp->employee_id) ?></span><?php endif; ?>
    </div>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('hr/edit/'.$emp->id) ?>" class="btn btn-ghost btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#resetModal"><i class="bi bi-key me-1"></i>Reset Password</button>
  </div>
</div>

<div class="row g-4">
  <!-- Profile Card -->
  <div class="col-xl-3">
    <div class="card card-modern text-center py-4 mb-4">
      <?= user_avatar($emp->first_name.' '.$emp->last_name, $emp->avatar??null, 80) ?>
      <h5 class="fw-800 mt-3 mb-1"><?= html_escape($emp->first_name.' '.$emp->last_name) ?></h5>
      <div class="small text-muted"><?= html_escape($emp->job_title??'') ?></div>
      <div class="small text-muted"><?= html_escape($emp->department??'') ?></div>
    </div>
    <div class="card card-modern">
      <div class="card-body p-0">
        <div class="info-list px-3 py-2">
          <?php if($emp->email): ?>
          <div class="info-row"><span class="info-label"><i class="bi bi-envelope me-1"></i>Email</span><span class="info-value small"><?= html_escape($emp->email) ?></span></div>
          <?php endif; ?>
          <?php if($emp->phone): ?>
          <div class="info-row"><span class="info-label"><i class="bi bi-telephone me-1"></i>Phone</span><span class="info-value"><?= html_escape($emp->phone) ?></span></div>
          <?php endif; ?>
          <?php if($emp->date_of_birth): ?>
          <div class="info-row"><span class="info-label"><i class="bi bi-cake me-1"></i>Birthday</span><span class="info-value"><?= date('M d, Y',strtotime($emp->date_of_birth)) ?></span></div>
          <?php endif; ?>
          <?php if($emp->joining_date): ?>
          <div class="info-row"><span class="info-label"><i class="bi bi-calendar2-check me-1"></i>Joined</span><span class="info-value"><?= date('M d, Y',strtotime($emp->joining_date)) ?></span></div>
          <?php endif; ?>
          <?php if($emp->address): ?>
          <div class="info-row"><span class="info-label"><i class="bi bi-geo-alt me-1"></i>Address</span><span class="info-value small"><?= html_escape($emp->address) ?></span></div>
          <?php endif; ?>
          <?php if($emp->emergency_contact): ?>
          <div class="info-row"><span class="info-label"><i class="bi bi-person-exclamation me-1"></i>Emergency</span><span class="info-value small"><?= html_escape($emp->emergency_contact) ?></span></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="col-xl-9">
    <!-- Attendance This Month -->
    <div class="row g-3 mb-4">
      <div class="col-3"><div class="att-stat-card att-stat-green"><div class="att-stat-val"><?= $attendance['present'] ?></div><div class="att-stat-lbl">Present</div></div></div>
      <div class="col-3"><div class="att-stat-card att-stat-amber"><div class="att-stat-val"><?= $attendance['late'] ?></div><div class="att-stat-lbl">Late</div></div></div>
      <div class="col-3"><div class="att-stat-card att-stat-blue"><div class="att-stat-val"><?= $attendance['half_day'] ?></div><div class="att-stat-lbl">Half Day</div></div></div>
      <div class="col-3"><div class="att-stat-card att-stat-purple"><div class="att-stat-val"><?= number_format($attendance['total_hours'],1) ?>h</div><div class="att-stat-lbl">Hours</div></div></div>
    </div>

    <!-- Tasks -->
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-check2-square me-2 text-primary"></i>Assigned Tasks</h6>
        <span class="badge bg-primary-soft text-primary"><?= count($tasks) ?></span>
      </div>
      <div class="card-body p-0">
        <?php if(empty($tasks)): ?><div class="empty-state py-3"><i class="bi bi-check-circle"></i><p>No tasks</p></div>
        <?php else: foreach($tasks as $t): ?>
        <div class="list-item">
          <div class="list-item-icon bg-primary-soft text-primary"><i class="bi bi-check2-square"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('tasks/view/'.$t->id) ?>" class="list-item-title"><?= html_escape($t->title) ?></a>
            <div class="list-item-meta"><span class="small text-muted"><?= html_escape($t->project_name) ?></span><?= status_badge($t->status) ?><?= priority_badge($t->priority) ?></div>
          </div>
          <div style="width:60px">
            <div class="progress" style="height:5px"><div class="progress-bar bg-primary" style="width:<?= $t->progress ?>%"></div></div>
            <div class="text-center small text-muted mt-1"><?= $t->progress ?>%</div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Leave Requests -->
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-calendar-x me-2 text-warning"></i>Leave History</h6>
      </div>
      <div class="card-body p-0">
        <?php if(empty($leaves)): ?><div class="empty-state py-3"><i class="bi bi-calendar-check"></i><p>No leave requests</p></div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover table-modern">
            <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($leaves as $lv): ?>
            <tr>
              <td><span class="small fw-600"><?= ucfirst($lv->leave_type) ?></span></td>
              <td><span class="small"><?= date('M d, Y',strtotime($lv->from_date)) ?></span></td>
              <td><span class="small"><?= date('M d, Y',strtotime($lv->to_date)) ?></span></td>
              <td><span class="fw-700"><?= $lv->total_days ?></span></td>
              <td><span class="small text-muted"><?= html_escape(substr($lv->reason,0,60)) ?></span></td>
              <td>
                <?php $sb=['pending'=>'bg-warning-soft text-warning','approved'=>'bg-success-soft text-success','rejected'=>'bg-danger-soft text-danger','cancelled'=>'bg-secondary-soft text-muted']; ?>
                <span class="badge <?= $sb[$lv->status]??'bg-secondary' ?>"><?= ucfirst($lv->status) ?></span>
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
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern"><h5 class="modal-title">Reset Password</h5><button class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button></div>
      <div class="modal-body p-4">
        <label class="form-label fw-600">New Password (min 6 chars)</label>
        <input type="password" class="form-control" id="newPass">
        <div id="resetMsg" class="mt-2" style="display:none"></div>
      </div>
      <div class="modal-footer-modern">
        <button class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-warning btn-sm" id="doReset"><i class="bi bi-key me-1"></i>Reset</button>
      </div>
    </div>
  </div>
</div>

<style>
.att-stat-card{background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);padding:16px;text-align:center;}
.att-stat-val{font-size:1.6rem;font-weight:800;line-height:1;}
.att-stat-lbl{font-size:.74rem;color:var(--text-3);margin-top:3px;}
.att-stat-green .att-stat-val{color:#10b981;} .att-stat-amber .att-stat-val{color:#f59e0b;}
.att-stat-blue .att-stat-val{color:#06b6d4;} .att-stat-purple .att-stat-val{color:var(--primary);}
</style>

<script>
var BASE='<?= base_url() ?>';
$('#doReset').click(function(){
  var pass=$('#newPass').val();
  if(!pass||pass.length<6){$('#resetMsg').show().className='alert alert-warning';$('#resetMsg').text('Min 6 characters');return;}
  $.post(BASE+'hr/reset_password/<?= $emp->id ?>',{password:pass},function(r){
    if(r.success){$('#resetMsg').show().className='alert alert-success';$('#resetMsg').text(r.message);setTimeout(function(){bootstrap.Modal.getInstance($('#resetModal')[0]).hide();},1500);}
    else{$('#resetMsg').show().className='alert alert-danger';$('#resetMsg').text(r.message);}
  },'json');
});
</script>

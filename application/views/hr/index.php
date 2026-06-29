<div class="page-header">
  <div>
    <h1 class="page-title">Employee Management</h1>
    <p class="page-subtitle">HR Portal — Manage all employee profiles</p>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('hr/settings') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-gear me-1"></i>Work Settings</a>
    <button class="btn btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#sendNotifModal"><i class="bi bi-megaphone me-1"></i>Announce</button>
    <a href="<?= site_url('hr/create') ?>" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i>Add Employee</a>
  </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <?php
  $active   = array_filter($employees, fn($e) => $e->status === 'active');
  $inactive = array_filter($employees, fn($e) => $e->status === 'inactive');
  $resigned = array_filter($employees, fn($e) => $e->status === 'resigned');
  ?>
  <div class="col-md-3"><div class="stat-card stat-primary"><div class="stat-icon"><i class="bi bi-people-fill"></i></div><div class="stat-body"><div class="stat-value"><?= count($employees) ?></div><div class="stat-label">Total Employees</div></div></div></div>
  <div class="col-md-3"><div class="stat-card stat-success"><div class="stat-icon"><i class="bi bi-person-check"></i></div><div class="stat-body"><div class="stat-value"><?= count($active) ?></div><div class="stat-label">Active</div></div></div></div>
  <div class="col-md-3"><div class="stat-card stat-warning"><div class="stat-icon"><i class="bi bi-person-dash"></i></div><div class="stat-body"><div class="stat-value"><?= count($inactive) ?></div><div class="stat-label">Inactive</div></div></div></div>
  <div class="col-md-3"><div class="stat-card stat-danger"><div class="stat-icon"><i class="bi bi-person-x"></i></div><div class="stat-body"><div class="stat-value"><?= count($resigned) ?></div><div class="stat-label">Resigned</div></div></div></div>
</div>

<!-- Filters -->
<div class="filter-bar mb-3">
  <input type="text" class="form-control form-control-sm" id="empSearch" placeholder="Search name, email, role..." style="max-width:260px">
  <select class="form-select form-select-sm" id="empStatus" style="width:140px">
    <option value="">All Status</option>
    <option value="active">Active</option>
    <option value="inactive">Inactive</option>
    <option value="resigned">Resigned</option>
  </select>
  <select class="form-select form-select-sm" id="empRole" style="width:160px">
    <option value="">All Roles</option>
    <?php foreach($roles as $r): ?>
    <option value="<?= html_escape($r->slug) ?>"><?= html_escape($r->name) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<!-- Employee Grid -->
<div class="row g-3" id="empGrid">
  <?php foreach($employees as $emp): ?>
  <div class="col-xl-3 col-md-4 col-sm-6 emp-card-wrap"
    data-name="<?= strtolower($emp->first_name.' '.$emp->last_name.' '.$emp->email) ?>"
    data-status="<?= $emp->status ?>"
    data-role="<?= $emp->role_slug ?>">
    <div class="emp-card">
      <div class="emp-card-status-bar status-<?= $emp->status ?>"></div>
      <div class="emp-card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div class="position-relative">
            <?= user_avatar($emp->first_name.' '.$emp->last_name, $emp->avatar??null, 52) ?>
            <span class="emp-status-dot dot-<?= $emp->status ?>"></span>
          </div>
          <div class="dropdown">
            <button class="btn btn-icon btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= site_url('hr/view/'.$emp->id) ?>"><i class="bi bi-eye me-2"></i>View Profile</a></li>
              <li><a class="dropdown-item" href="<?= site_url('hr/edit/'.$emp->id) ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
              <li><hr class="dropdown-divider m-0"></li>
              <li><a class="dropdown-item" href="#" onclick="resetPass(<?= $emp->id ?>,event)"><i class="bi bi-key me-2"></i>Reset Password</a></li>
              <li><a class="dropdown-item <?= $emp->status==='active'?'text-warning':'text-success' ?>"
                href="#" onclick="toggleStatus(<?= $emp->id ?>,this,event)">
                <i class="bi <?= $emp->status==='active'?'bi-person-dash':'bi-person-check' ?> me-2"></i>
                <?= $emp->status==='active'?'Deactivate':'Activate' ?></a></li>
            </ul>
          </div>
        </div>
        <h6 class="emp-name mb-0"><?= html_escape($emp->first_name.' '.$emp->last_name) ?></h6>
        <div class="emp-title"><?= html_escape($emp->job_title??'—') ?></div>
        <?php if($emp->employee_id): ?><div class="emp-id"><?= html_escape($emp->employee_id) ?></div><?php endif; ?>
        <div class="d-flex align-items-center gap-2 mt-2">
          <span class="badge <?= ['active'=>'bg-success','inactive'=>'bg-warning','resigned'=>'bg-danger'][$emp->status]??'bg-secondary' ?> text-white" style="font-size:.68rem"><?= ucfirst($emp->status) ?></span>
          <span class="badge bg-primary text-white" style="font-size:.68rem"><?= html_escape($emp->role_name) ?></span>
        </div>
        <div class="emp-meta mt-2">
          <?php if($emp->email): ?><div class="emp-meta-row"><i class="bi bi-envelope"></i><?= html_escape($emp->email) ?></div><?php endif; ?>
          <?php if($emp->phone): ?><div class="emp-meta-row"><i class="bi bi-telephone"></i><?= html_escape($emp->phone) ?></div><?php endif; ?>
          <?php if($emp->joining_date): ?><div class="emp-meta-row"><i class="bi bi-calendar2-check"></i>Joined <?= date('M d, Y',strtotime($emp->joining_date)) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="emp-card-footer">
        <a href="<?= site_url('hr/view/'.$emp->id) ?>" class="btn btn-ghost btn-sm w-100">View Profile <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern"><h5 class="modal-title">Reset Password</h5><button class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button></div>
      <div class="modal-body p-4">
        <input type="hidden" id="resetId">
        <label class="form-label fw-600">New Password</label>
        <input type="password" class="form-control" id="resetPass" placeholder="Min 6 characters">
        <div id="resetMsg" class="mt-2" style="display:none"></div>
      </div>
      <div class="modal-footer-modern">
        <button class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-warning btn-sm" id="btnDoReset"><i class="bi bi-key me-1"></i>Reset</button>
      </div>
    </div>
  </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendNotifModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern"><h5 class="modal-title"><i class="bi bi-megaphone me-2 text-primary"></i>Send Announcement</h5><button class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button></div>
      <div class="modal-body p-4">
        <div class="mb-3">
          <label class="form-label fw-600">Target</label>
          <select class="form-select" id="notifTarget">
            <option value="all">🌐 All Users</option>
            <option value="role:employee">👨‍💻 Employees Only</option>
            <option value="role:project_manager">📋 Project Managers</option>
            <option value="role:hr">👥 HR Only</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="notifTitle" placeholder="Announcement title">
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Message <span class="text-danger">*</span></label>
          <textarea class="form-control" id="notifMessage" rows="4" placeholder="Your announcement..."></textarea>
        </div>
        <div id="notifResult" style="display:none"></div>
      </div>
      <div class="modal-footer-modern">
        <button class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="btnSendNotif"><i class="bi bi-send me-1"></i>Send</button>
      </div>
    </div>
  </div>
</div>

<style>
.emp-card{background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:all var(--tr);}
.emp-card:hover{box-shadow:var(--sh-md);transform:translateY(-2px);}
.emp-card-status-bar{height:4px;}
.status-active{background:linear-gradient(90deg,#10b981,#059669);}
.status-inactive{background:linear-gradient(90deg,#f59e0b,#d97706);}
.status-resigned{background:linear-gradient(90deg,#ef4444,#dc2626);}
.emp-card-body{padding:18px;}
.emp-name{font-weight:800;font-size:.95rem;color:var(--text-1);}
.emp-title{font-size:.78rem;color:var(--text-3);margin-top:2px;}
.emp-id{font-size:.72rem;color:var(--primary);font-weight:700;background:var(--primary-soft);padding:2px 7px;border-radius:4px;display:inline-block;margin-top:3px;}
.emp-status-dot{position:absolute;bottom:2px;right:2px;width:12px;height:12px;border-radius:50%;border:2px solid var(--card-bg);}
.dot-active{background:#10b981;} .dot-inactive{background:#f59e0b;} .dot-resigned{background:#ef4444;}
.emp-meta{display:flex;flex-direction:column;gap:3px;}
.emp-meta-row{display:flex;align-items:center;gap:6px;font-size:.76rem;color:var(--text-3);}
.emp-meta-row i{width:14px;flex-shrink:0;}
.emp-card-footer{padding:10px 16px;border-top:1px solid var(--border-lt);background:var(--page-bg);}
</style>

<script>
var BASE='<?= base_url() ?>';

// Search / Filter
function filterEmp(){
  var s=$('#empSearch').val().toLowerCase(), st=$('#empStatus').val(), rl=$('#empRole').val();
  $('.emp-card-wrap').each(function(){
    var nm=$(this).data('name'), ss=$(this).data('status'), rr=$(this).data('role');
    $(this).toggle((!s||nm.includes(s))&&(!st||ss===st)&&(!rl||rr===rl));
  });
}
$('#empSearch').on('input',filterEmp); $('#empStatus,#empRole').change(filterEmp);

// Reset password
function resetPass(id,e){ e.preventDefault(); document.getElementById('resetId').value=id; document.getElementById('resetPass').value=''; new bootstrap.Modal(document.getElementById('resetModal')).show(); }
$('#btnDoReset').click(function(){
  var id=$('#resetId').val(), pass=$('#resetPass').val();
  if(!pass||pass.length<6){$('#resetMsg').show().className='alert alert-warning'.replace('className','class');$('#resetMsg').text('Min 6 chars');return;}
  $.post(BASE+'hr/reset_password/'+id,{password:pass},function(r){
    if(r.success){bootstrap.Modal.getInstance($('#resetModal')[0]).hide();showTst('Password reset!','success');}
    else showTst(r.message,'danger');
  },'json');
});

// Toggle status
function toggleStatus(id,btn,e){
  e.preventDefault();
  if(!confirm('Change employee status?'))return;
  $.post(BASE+'hr/toggle_status/'+id,{},function(r){
    if(r.success)location.reload();
  },'json');
}

// Send notification
$('#btnSendNotif').click(function(){
  var t=$('#notifTitle').val(),m=$('#notifMessage').val();
  if(!t||!m){alert('Title and message required.');return;}
  $(this).prop('disabled',true).text('Sending...');
  var self=this;
  $.post(BASE+'hr/send_notification',{title:t,message:m,target:$('#notifTarget').val()},function(r){
    $(self).prop('disabled',false).text('Send');
    if(r.success){$('#notifResult').show().className='alert alert-success';$('#notifResult').text(r.message);setTimeout(function(){bootstrap.Modal.getInstance($('#sendNotifModal')[0]).hide();},1500);}
  },'json');
});

function showTst(msg,type){var t=document.createElement('div');t.style.cssText='position:fixed;bottom:22px;right:22px;z-index:9999;background:'+(type==='success'?'#10b981':'#ef4444')+';color:#fff;padding:12px 18px;border-radius:10px;font-weight:600;font-size:.86rem;';t.textContent=msg;document.body.appendChild(t);setTimeout(function(){t.remove();},3000);}
</script>

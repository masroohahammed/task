<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('dashboard') ?>">Dashboard</a>
      <i class="bi bi-chevron-right"></i>
      <span>Team Members</span>
    </div>
    <h1 class="page-title">Team Members</h1>
    <p class="page-subtitle">Manage portal users and their project access for your company.</p>
  </div>
  <div class="page-actions">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-person-plus me-1"></i> Add Team Member
    </button>
  </div>
</div>

<div class="card card-modern">
  <div class="card-body p-0">
    <?php if (empty($users)): ?>
    <div class="empty-state py-5">
      <i class="bi bi-people"></i>
      <p>No team members yet. Add sub-users and assign them to specific projects.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-modern mb-0">
        <thead><tr>
          <th>Name</th><th>Email</th><th>Role</th><th>Status</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <?= user_avatar($u->first_name.' '.$u->last_name, $u->avatar ?? null, 36) ?>
              <div>
                <div class="fw-600 small"><?= html_escape($u->first_name.' '.$u->last_name) ?></div>
                <div class="small text-muted"><?= html_escape($u->job_title ?? '') ?></div>
              </div>
            </div>
          </td>
          <td><span class="small"><?= html_escape($u->email) ?></span></td>
          <td>
            <?php if (!empty($u->is_client_admin)): ?>
            <span class="badge bg-primary-soft text-primary">Primary Admin</span>
            <?php else: ?>
            <span class="badge bg-secondary-soft text-muted">Sub-user</span>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?= $u->status === 'active' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' ?>">
              <?= ucfirst($u->status) ?>
            </span>
          </td>
          <td class="text-end">
            <?php if (empty($u->is_client_admin)): ?>
            <button class="btn btn-sm btn-ghost" onclick="openProjects(<?= $u->id ?>, '<?= html_escape($u->first_name.' '.$u->last_name, true) ?>')" title="Project Access">
              <i class="bi bi-folder2-open"></i>
            </button>
            <button class="btn btn-sm btn-ghost" onclick="resetPassword(<?= $u->id ?>)" title="Reset Password">
              <i class="bi bi-key"></i>
            </button>
            <button class="btn btn-sm btn-ghost text-danger" onclick="removeUser(<?= $u->id ?>, this)" title="Remove">
              <i class="bi bi-person-dash"></i>
            </button>
            <?php else: ?>
            <span class="small text-muted">All projects</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Add Team Member</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="newUserFirst">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600">Last Name</label>
            <input type="text" class="form-control" id="newUserLast">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="newUserEmail">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600">Job Title</label>
            <input type="text" class="form-control" id="newUserTitle" placeholder="e.g. Project Lead">
          </div>
          <div class="col-12">
            <label class="form-label fw-600">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="newUserPass" placeholder="Min 6 characters">
          </div>
          <?php if (!empty($projects)): ?>
          <div class="col-12">
            <label class="form-label fw-600">Project Access</label>
            <div class="border rounded p-3" style="max-height:220px;overflow-y:auto">
              <?php foreach ($projects as $p): ?>
              <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div class="small fw-600"><?= html_escape($p->name) ?></div>
                <select class="form-select form-select-sm add-proj-perm" data-project="<?= $p->id ?>" style="width:140px">
                  <option value="">No access</option>
                  <option value="view">View only</option>
                  <option value="tickets">View + Tickets</option>
                  <option value="manage">Full access</option>
                </select>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
        <div id="addUserMsg" class="mt-3 d-none"></div>
      </div>
      <div class="modal-footer-modern">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveNewUser">
          <i class="bi bi-check-lg me-1"></i> Add Member
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Project Access Modal -->
<div class="modal fade" id="projectsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title"><i class="bi bi-folder2-open me-2 text-primary"></i>Project Access — <span id="projUserName"></span></h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="projUserId">
        <?php if (!empty($projects)): foreach ($projects as $p): ?>
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
          <div class="small fw-600"><?= html_escape($p->name) ?></div>
          <select class="form-select form-select-sm edit-proj-perm" data-project="<?= $p->id ?>" style="width:140px">
            <option value="">No access</option>
            <option value="view">View only</option>
            <option value="tickets">View + Tickets</option>
            <option value="manage">Full access</option>
          </select>
        </div>
        <?php endforeach; else: ?>
        <p class="text-muted small mb-0">No projects available yet.</p>
        <?php endif; ?>
        <div id="projMsg" class="mt-3 d-none"></div>
      </div>
      <div class="modal-footer-modern">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveProjects">Save Access</button>
      </div>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPassModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title">Reset Password</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="resetUserId">
        <label class="form-label fw-600">New Password</label>
        <input type="password" class="form-control" id="newPassInput" placeholder="Min 6 characters">
        <div id="resetPassMsg" class="mt-2 d-none"></div>
      </div>
      <div class="modal-footer-modern">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" id="doResetPass">Reset</button>
      </div>
    </div>
  </div>
</div>

<script>
var BASE_URL = '<?= base_url() ?>';

function collectProjectPerms(selector) {
  var projects = {};
  $(selector).each(function() {
    var val = $(this).val();
    if (val) projects[$(this).data('project')] = val;
  });
  return projects;
}

$('#saveNewUser').click(function() {
  var $btn = $(this).prop('disabled', true).html('<i class="bi bi-check-lg me-1"></i> Saving...');
  $.ajax({
    url: BASE_URL + 'client-portal/add_user',
    method: 'POST',
    dataType: 'json',
    data: {
      first_name: $('#newUserFirst').val(),
      last_name:  $('#newUserLast').val(),
      email:      $('#newUserEmail').val(),
      job_title:  $('#newUserTitle').val(),
      password:   $('#newUserPass').val(),
      projects:   collectProjectPerms('.add-proj-perm')
    },
    success: function(res) {
      $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Add Member');
      if (res.success) {
        $('#addUserModal').modal('hide');
        location.reload();
      } else {
        $('#addUserMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Error');
      }
    },
    error: function() {
      $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Add Member');
      $('#addUserMsg').removeClass('d-none').addClass('alert alert-danger').text('Error. Please try again.');
    }
  });
});

function removeUser(uid, btn) {
  if (!confirm('Remove this team member?')) return;
  $.post(BASE_URL + 'client-portal/remove_user/' + uid, {}, function(res) {
    if (res.success) $(btn).closest('tr').fadeOut(300, function(){ $(this).remove(); });
  }, 'json');
}

function resetPassword(uid) {
  $('#resetUserId').val(uid);
  $('#newPassInput').val('');
  $('#resetPassMsg').addClass('d-none').removeClass('alert alert-success alert-danger').text('');
  $('#resetPassModal').modal('show');
}

$('#doResetPass').click(function() {
  var uid  = $('#resetUserId').val();
  var pass = $('#newPassInput').val();
  $.post(BASE_URL + 'client-portal/reset_password/' + uid, { password: pass }, function(res) {
    if (res.success) {
      $('#resetPassMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text(res.message);
      setTimeout(function(){ $('#resetPassModal').modal('hide'); }, 1500);
    } else {
      $('#resetPassMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message);
    }
  }, 'json');
});

function openProjects(uid, name) {
  $('#projUserId').val(uid);
  $('#projUserName').text(name);
  $('.edit-proj-perm').val('');
  $('#projMsg').addClass('d-none').removeClass('alert alert-success alert-danger').text('');
  $.get(BASE_URL + 'client-portal/user_projects/' + uid, function(res) {
    if (res.success && res.projects) {
      $.each(res.projects, function(pid, perm) {
        $('.edit-proj-perm[data-project="'+pid+'"]').val(perm);
      });
    }
    $('#projectsModal').modal('show');
  }, 'json');
}

$('#saveProjects').click(function() {
  var uid = $('#projUserId').val();
  $.post(BASE_URL + 'client-portal/save_user_projects/' + uid, {
    projects: collectProjectPerms('.edit-proj-perm')
  }, function(res) {
    if (res.success) {
      $('#projMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text(res.message);
      setTimeout(function(){ $('#projectsModal').modal('hide'); }, 1200);
    } else {
      $('#projMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Error');
    }
  }, 'json');
});
</script>

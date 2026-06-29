<div class="page-header">
  <div>
    <h1 class="page-title">User Management</h1>
    <p class="page-subtitle">Manage all users, roles, and permissions</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('hr/create') ?>" class="btn btn-primary">
      <i class="bi bi-person-plus me-1"></i> Add Employee (HR)
    </a>
  </div>
</div>

<!-- Role Filter Tabs -->
<div class="role-tabs mb-4">
  <button class="role-tab active" data-role="">All Users</button>
  <button class="role-tab" data-role="admin">Admin</button>
  <button class="role-tab" data-role="project_manager">Project Managers</button>
  <button class="role-tab" data-role="hr">HR</button>
  <button class="role-tab" data-role="client">Clients</button>
</div>

<div class="card card-modern">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover table-modern" id="usersTable">
        <thead><tr>
          <th>User</th><th>Email</th><th>Role</th><th>Department</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr data-role="<?= html_escape($u->role_slug) ?>">
          <td>
            <div class="d-flex align-items-center gap-3">
              <?= user_avatar($u->first_name . ' ' . $u->last_name, $u->avatar ?? null, 38) ?>
              <div>
                <div class="fw-600"><?= html_escape($u->first_name . ' ' . $u->last_name) ?></div>
                <div class="small text-muted"><?= html_escape($u->job_title ?? '') ?></div>
              </div>
            </div>
          </td>
          <td><span class="small"><?= html_escape($u->email) ?></span></td>
          <td>
            <span class="badge <?= [
              'admin'=>'bg-primary text-white',
              'project_manager'=>'bg-success text-white',
              'employee'=>'bg-info-soft text-info',
              'hr'=>'bg-secondary-soft text-secondary',
              'client'=>'bg-warning-soft text-warning',
            ][$u->role_slug] ?? 'bg-secondary' ?>"><?= html_escape($u->role_name) ?></span>
          </td>
          <td><span class="small text-muted"><?= html_escape($u->department ?? '—') ?></span></td>
          <td>
            <span class="badge <?= $u->status === 'active' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' ?>">
              <?= ucfirst($u->status) ?>
            </span>
          </td>
          <td><span class="small text-muted"><?= $u->last_login ? time_ago($u->last_login) : 'Never' ?></span></td>
          <td class="text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="<?= site_url('users/permissions/'.$u->id) ?>" class="btn btn-sm btn-ghost" title="Permissions">
                <i class="bi bi-shield-check"></i>
              </a>
              <a href="<?= site_url('users/edit/'.$u->id) ?>" class="btn btn-sm btn-ghost" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <?php if ($u->id != $current_user->id): ?>
              <a href="<?= site_url('users/delete/'.$u->id) ?>" class="btn btn-sm btn-ghost text-danger"
                onclick="return confirm('Delete this user?')" title="Delete">
                <i class="bi bi-trash"></i>
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.role-tabs { display:flex; gap:8px; flex-wrap:wrap; }
.role-tab {
  padding:7px 16px; border-radius:8px;
  border:1px solid var(--border);
  background:var(--surface); font-size:0.82rem; font-weight:600;
  cursor:pointer; color:var(--text-secondary);
  transition:all var(--transition);
}
.role-tab:hover, .role-tab.active { background:var(--primary); color:#fff; border-color:var(--primary); }
</style>

<script>
$('.role-tab').click(function() {
  $('.role-tab').removeClass('active');
  $(this).addClass('active');
  const role = $(this).data('role');
  $('#usersTable tbody tr').each(function() {
    const r = $(this).data('role');
    $(this).toggle(!role || r === role);
  });
});
</script>

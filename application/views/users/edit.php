<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('users') ?>">Users</a>
      <i class="bi bi-chevron-right"></i>
      <span>Edit User</span>
    </div>
    <h1 class="page-title">Edit User</h1>
    <p class="page-subtitle text-muted small">Internal staff are added under <a href="<?= site_url('hr/create') ?>">HR → Add Employee</a>. Client logins are created from <a href="<?= site_url('clients') ?>">Clients</a>.</p>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <form action="<?= site_url('users/update/'.$user->id) ?>" method="post">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <div class="form-card">
        <div class="form-card-header">
          <h6><i class="bi bi-person me-2 text-primary"></i>User Information</h6>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="first_name" required
                value="<?= html_escape($user->first_name) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="last_name" required
                value="<?= html_escape($user->last_name) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Email</label>
              <input type="email" class="form-control" value="<?= html_escape($user->email) ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Phone</label>
              <input type="text" class="form-control" name="phone"
                value="<?= html_escape($user->phone ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Password <span class="text-muted fw-400">(leave blank to keep)</span></label>
              <input type="password" class="form-control" name="password" placeholder="Min 6 characters" minlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Role <span class="text-danger">*</span></label>
              <select class="form-select" name="role_id" required id="roleSelect">
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r->id ?>" <?= ($user->role_id == $r->id) ? 'selected' : '' ?>>
                  <?= html_escape($r->name) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Job Title</label>
              <input type="text" class="form-control" name="job_title"
                value="<?= html_escape($user->job_title ?? '') ?>" placeholder="e.g. Senior Developer">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Department</label>
              <input type="text" class="form-control" name="department"
                value="<?= html_escape($user->department ?? '') ?>" placeholder="e.g. Engineering">
            </div>
            <div class="col-md-6" id="clientField" style="<?= $user->role_slug !== 'client' ? 'display:none' : '' ?>">
              <label class="form-label fw-600">Client</label>
              <select class="form-select" name="client_id">
                <option value="">Select client...</option>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c->id ?>" <?= ($user->client_id == $c->id) ? 'selected' : '' ?>>
                  <?= html_escape($c->company_name) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions mt-4">
        <a href="<?= site_url('users') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i> Update User
        </button>
      </div>
    </form>
  </div>
</div>

<script>
$('#roleSelect').change(function() {
  const role = $(this).find(':selected').text().toLowerCase();
  $('#clientField').toggle(role.includes('client'));
});
</script>

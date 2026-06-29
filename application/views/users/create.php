<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('users') ?>">Users</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= isset($user) ? 'Edit User' : 'Add User' ?></span>
    </div>
    <h1 class="page-title"><?= isset($user) ? 'Edit User' : 'Add New User' ?></h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <form action="<?= isset($user) ? site_url('users/update/'.$user->id) : site_url('users/store') ?>" method="post">
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
                value="<?= isset($user) ? html_escape($user->first_name) : set_value('first_name') ?>">
              <?= form_error('first_name','<div class="text-danger small">','</div>') ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="last_name" required
                value="<?= isset($user) ? html_escape($user->last_name) : set_value('last_name') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required
                value="<?= isset($user) ? html_escape($user->email) : set_value('email') ?>"
                <?= isset($user) ? 'readonly' : '' ?>>
              <?= form_error('email','<div class="text-danger small">','</div>') ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Phone</label>
              <input type="text" class="form-control" name="phone"
                value="<?= isset($user) ? html_escape($user->phone ?? '') : set_value('phone') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Password <?= isset($user) ? '(leave blank to keep)' : '<span class="text-danger">*</span>' ?></label>
              <input type="password" class="form-control" name="password"
                <?= !isset($user) ? 'required min-length="6"' : '' ?> placeholder="Min 6 characters">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Role <span class="text-danger">*</span></label>
              <select class="form-select" name="role_id" required id="roleSelect">
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r->id ?>" <?= (isset($user) && $user->role_id == $r->id) ? 'selected' : '' ?>>
                  <?= html_escape($r->name) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Job Title</label>
              <input type="text" class="form-control" name="job_title"
                value="<?= isset($user) ? html_escape($user->job_title ?? '') : '' ?>" placeholder="e.g. Senior Developer">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Department</label>
              <input type="text" class="form-control" name="department"
                value="<?= isset($user) ? html_escape($user->department ?? '') : '' ?>" placeholder="e.g. Engineering">
            </div>
            <!-- Client (only show for client role) -->
            <div class="col-md-6" id="clientField" style="<?= (!isset($user) || $user->role_slug !== 'client') ? 'display:none' : '' ?>">
              <label class="form-label fw-600">Client</label>
              <select class="form-select" name="client_id">
                <option value="">Select client...</option>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c->id ?>" <?= (isset($user) && $user->client_id == $c->id) ? 'selected' : '' ?>>
                  <?= html_escape($c->company_name) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if (isset($user)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?= $user->status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $user->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <?php endif; ?>
            <?php if (!empty($show_timezone)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">Timezone (IANA)</label>
              <input type="text" class="form-control" name="timezone" maxlength="64"
                placeholder="e.g. America/New_York"
                value="<?= isset($user) ? html_escape($user->timezone ?? '') : set_value('timezone') ?>">
              <div class="form-text">Used for dates and attendance when set.</div>
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="auto_detect_timezone" value="1" id="autoTz"
                  <?= (!isset($user) || !isset($user->auto_detect_timezone) || (int)$user->auto_detect_timezone) ? 'checked' : '' ?>>
                <label class="form-check-label" for="autoTz">Auto-detect timezone &amp; country on login</label>
              </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($show_work_hours)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">Duty start</label>
              <input type="time" class="form-control" name="work_start"
                value="<?= isset($user) ? html_escape($user->work_start ?? '') : set_value('work_start') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Duty end</label>
              <input type="time" class="form-control" name="work_end"
                value="<?= isset($user) ? html_escape($user->work_end ?? '') : set_value('work_end') ?>">
              <div class="form-text">Leave blank to use company defaults from HR settings.</div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-actions mt-4">
        <a href="<?= site_url('users') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i> <?= isset($user) ? 'Update User' : 'Create User' ?>
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

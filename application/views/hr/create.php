<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('hr') ?>">HR</a><i class="bi bi-chevron-right"></i>
      <span><?= isset($emp) ? 'Edit Employee' : 'Add Employee' ?></span>
    </div>
    <h1 class="page-title"><?= isset($emp) ? 'Edit Employee Profile' : 'Add New Employee' ?></h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-9">
    <?= validation_errors('<div class="alert alert-danger">','</div>') ?>

    <?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= $this->session->flashdata('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form action="<?= isset($emp) ? site_url('hr/update/'.$emp->id) : site_url('hr/store') ?>"
      method="post" enctype="multipart/form-data">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-person me-2 text-primary"></i>Personal Information</h6></div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="first_name" required value="<?= html_escape(isset($emp)?$emp->first_name:set_value('first_name')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="last_name" required value="<?= html_escape(isset($emp)?$emp->last_name:set_value('last_name')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required
                value="<?= html_escape(isset($emp)?$emp->email:set_value('email')) ?>"
                <?= isset($emp)?'readonly':'' ?>>
              <?php if(!isset($emp)): ?>
              <div class="form-text">This will be their login email.</div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Phone</label>
              <input type="text" class="form-control" name="phone" value="<?= html_escape(isset($emp)?($emp->phone??''):'') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Date of Birth</label>
              <input type="date" class="form-control" name="date_of_birth" value="<?= isset($emp)?($emp->date_of_birth??''):'' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Emergency Contact</label>
              <input type="text" class="form-control" name="emergency_contact" placeholder="Name & phone" value="<?= html_escape(isset($emp)?($emp->emergency_contact??''):'') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Address</label>
              <textarea class="form-control" name="address" rows="2"><?= html_escape(isset($emp)?($emp->address??''):'') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Profile Photo</label>
              <input type="file" class="form-control" name="avatar" accept="image/*">
              <?php if(isset($emp)&&$emp->avatar): ?>
              <div class="mt-2"><?= user_avatar($emp->first_name.' '.$emp->last_name,$emp->avatar,40) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-briefcase me-2 text-success"></i>Job Details</h6></div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Role / Position <span class="text-danger">*</span></label>
              <select class="form-select" name="role_id" required>
                <?php foreach($roles as $r): ?>
                <option value="<?= $r->id ?>" <?= (isset($emp)&&$emp->role_id==$r->id)?'selected':'' ?>><?= html_escape($r->name) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Client portal accounts are created from <a href="<?= site_url('clients') ?>">Clients</a>, not here.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Job Title</label>
              <input type="text" class="form-control" name="job_title" placeholder="e.g. Senior Developer" value="<?= html_escape(isset($emp)?($emp->job_title??''):'') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Department</label>
              <input type="text" class="form-control" name="department" placeholder="e.g. Engineering" value="<?= html_escape(isset($emp)?($emp->department??''):'') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Joining Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="joining_date" required value="<?= isset($emp)?($emp->joining_date??''):'' ?>">
            </div>
            <?php if(isset($emp)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?= $emp->status==='active'?'selected':'' ?>>✅ Active</option>
                <option value="inactive" <?= $emp->status==='inactive'?'selected':'' ?>>⏸ Inactive</option>
                <option value="resigned" <?= $emp->status==='resigned'?'selected':'' ?>>🚪 Resigned (blocks login)</option>
              </select>
              <div class="form-text text-danger">Setting to <strong>Resigned</strong> will immediately block login access.</div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!empty($show_timezone)): ?>
      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-globe2 me-2 text-info"></i>Schedule &amp; timezone</h6></div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Duty start</label>
              <input type="time" class="form-control" name="work_start"
                value="<?= html_escape(isset($emp) ? ($emp->work_start ?? '') : '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Duty end</label>
              <input type="time" class="form-control" name="work_end"
                value="<?= html_escape(isset($emp) ? ($emp->work_end ?? '') : '') ?>">
              <div class="form-text">Optional; company defaults from HR work settings apply if empty.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Timezone (IANA)</label>
              <input type="text" class="form-control" name="timezone" maxlength="64"
                placeholder="e.g. Asia/Dubai"
                value="<?= html_escape(isset($emp) ? ($emp->timezone ?? '') : '') ?>">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="auto_detect_timezone" value="1" id="hrAutoTz"
                  <?= (!isset($emp) || !isset($emp->auto_detect_timezone) || (int)$emp->auto_detect_timezone) ? 'checked' : '' ?>>
                <label class="form-check-label" for="hrAutoTz">Update timezone from location on sign-in</label>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if(!isset($emp)): ?>
      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-shield-lock me-2 text-warning"></i>Login Credentials</h6></div>
        <div class="form-card-body">
          <div class="p-3 mb-3 rounded" style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15)">
            <i class="bi bi-info-circle text-primary me-2"></i>
            <span class="small">If no password set, auto-generates as: <strong>FirstName@Year</strong> (e.g. John@2024)</span>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600">Set Password <span class="text-muted fw-400">(optional)</span></label>
            <input type="password" class="form-control" name="password" placeholder="Leave blank for auto-generated">
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-key me-2 text-warning"></i>Change Password</h6></div>
        <div class="form-card-body">
          <div class="col-md-6">
            <label class="form-label fw-600">New Password <span class="text-muted fw-400">(leave blank to keep current)</span></label>
            <input type="password" class="form-control" name="new_password" placeholder="Min 6 characters">
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="form-actions">
        <a href="<?= site_url('hr') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i><?= isset($emp)?'Update Employee':'Create Employee & Login' ?>
        </button>
      </div>
    </form>
  </div>
</div>

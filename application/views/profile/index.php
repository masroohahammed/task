<div class="page-header">
  <div>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage your personal information and preferences</p>
  </div>
</div>

<div class="row g-4">
  <!-- Profile Card -->
  <div class="col-xl-4">
    <div class="card card-modern text-center">
      <div class="card-body py-5">
        <div class="profile-avatar-wrap mb-3">
          <?= user_avatar($user->first_name . ' ' . $user->last_name, $user->avatar ?? null, 80) ?>
        </div>
        <h5 class="fw-800 mb-0"><?= html_escape($user->first_name . ' ' . $user->last_name) ?></h5>
        <div class="text-muted small mb-2"><?= html_escape($user->email) ?></div>
        <span class="badge bg-primary"><?= html_escape($user->role_name) ?></span>
        <?php if ($user->job_title): ?>
        <div class="text-muted small mt-2"><?= html_escape($user->job_title) ?></div>
        <?php endif; ?>
        <?php if ($user->department): ?>
        <div class="text-muted small"><?= html_escape($user->department) ?></div>
        <?php endif; ?>
        <hr>
        <div class="d-flex justify-content-around">
          <div>
            <div class="fw-700 fs-5 text-primary"><?= $total_tasks ?></div>
            <div class="small text-muted">Tasks</div>
          </div>
          <div>
            <div class="fw-700 fs-5 text-success"><?= $done_tasks ?></div>
            <div class="small text-muted">Completed</div>
          </div>
          <div>
            <div class="fw-700 fs-5 text-warning"><?= $total_tasks - $done_tasks ?></div>
            <div class="small text-muted">Pending</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card card-modern mt-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-teal"></i>My Activity</h6>
      </div>
      <div class="activity-timeline px-3">
        <?php if (empty($activities)): ?>
        <div class="empty-state py-3"><i class="bi bi-clock"></i><p>No activity yet</p></div>
        <?php else: foreach ($activities as $a): ?>
        <div class="activity-item">
          <div class="activity-dot"></div>
          <div class="activity-content small"><?= html_escape($a->action) ?></div>
          <div class="activity-time"><?= time_ago($a->created_at) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Edit Form -->
  <div class="col-xl-8">
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-circle me-2"></i><?= $this->session->flashdata('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form action="<?= site_url('profile/update') ?>" method="post" enctype="multipart/form-data">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <div class="form-card mb-4">
        <div class="form-card-header">
          <h6><i class="bi bi-person me-2 text-primary"></i>Personal Information</h6>
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
              <div class="form-text">Email cannot be changed.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Phone</label>
              <input type="text" class="form-control" name="phone"
                value="<?= html_escape($user->phone ?? '') ?>">
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
            <?php if (!empty($show_timezone)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">Timezone (IANA)</label>
              <input type="text" class="form-control" name="timezone" maxlength="64"
                placeholder="e.g. Europe/London"
                value="<?= html_escape($user->timezone ?? '') ?>">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="auto_detect_timezone" value="1" id="profAutoTz"
                  <?= (!isset($user->auto_detect_timezone) || (int)$user->auto_detect_timezone) ? 'checked' : '' ?>>
                <label class="form-check-label" for="profAutoTz">Update timezone from my location when I sign in</label>
              </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($show_work_hours)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">My duty start</label>
              <input type="time" class="form-control" name="work_start"
                value="<?= html_escape($user->work_start ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">My duty end</label>
              <input type="time" class="form-control" name="work_end"
                value="<?= html_escape($user->work_end ?? '') ?>">
              <div class="form-text">Optional; company defaults apply if empty.</div>
            </div>
            <?php endif; ?>
            <div class="col-12">
              <label class="form-label fw-600">Profile Photo</label>
              <input type="file" class="form-control" name="avatar" accept="image/*">
              <div class="form-text">Max 2MB. JPG, PNG or GIF.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-card-header">
          <h6><i class="bi bi-lock me-2 text-warning"></i>Change Password</h6>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">New Password</label>
              <input type="password" class="form-control" name="new_password"
                placeholder="Leave blank to keep current" minlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Confirm Password</label>
              <input type="password" class="form-control" name="confirm_password"
                placeholder="Repeat new password">
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> Save Changes
        </button>
      </div>
    </form>

    <!-- My Tasks -->
    <?php if (!empty($my_tasks)): ?>
    <div class="card card-modern mt-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-check2-square me-2 text-primary"></i>My Recent Tasks</h6>
        <a href="<?= site_url('projects') ?>" class="btn btn-sm btn-ghost">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach ($my_tasks as $t): ?>
        <div class="list-item">
          <div class="list-item-icon bg-primary-soft text-primary"><i class="bi bi-check2-square"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('tasks/view/'.$t->id) ?>" class="list-item-title"><?= html_escape($t->title) ?></a>
            <div class="list-item-meta">
              <span class="small text-muted"><?= html_escape($t->project_name) ?></span>
              <?= status_badge($t->status) ?>
              <?= priority_badge($t->priority) ?>
            </div>
          </div>
          <div>
            <div class="progress" style="width:60px;height:6px">
              <div class="progress-bar bg-primary" style="width:<?= $t->progress ?>%"></div>
            </div>
            <div class="text-center small text-muted mt-1"><?= $t->progress ?>%</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

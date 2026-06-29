<?php
$perm_labels = ['view' => 'View', 'tickets' => 'Tickets', 'manage' => 'Full'];
?>
<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('dashboard') ?>">Dashboard</a>
      <i class="bi bi-chevron-right"></i>
      <span>Team &amp; Projects</span>
    </div>
    <h1 class="page-title">Team &amp; Project Access</h1>
    <p class="page-subtitle">Add portal users and assign them to one or more projects with specific permissions.</p>
  </div>
  <div class="page-actions">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-person-plus me-1"></i> Add User
    </button>
  </div>
</div>

<?php if (empty($projects)): ?>
<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
  <i class="bi bi-info-circle fs-5 mt-1"></i>
  <div class="small">
    <strong>No projects yet.</strong> Your company does not have any projects assigned. Contact your account manager to set up projects, then you can assign team members to them.
  </div>
</div>
<?php endif; ?>

<div class="card card-modern">
  <div class="card-header-modern">
    <h6 class="card-title-modern"><i class="bi bi-people me-2 text-primary"></i>Portal Users</h6>
    <span class="small text-muted"><?= count($users) ?> user<?= count($users) === 1 ? '' : 's' ?></span>
  </div>
  <div class="card-body p-0">
    <?php if (empty($users)): ?>
    <div class="empty-state py-5">
      <i class="bi bi-people"></i>
      <p class="mb-3">No team members yet.</p>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-1"></i> Add First User
      </button>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-modern mb-0">
        <thead><tr>
          <th>Name</th>
          <th>Email</th>
          <th>Assigned Projects</th>
          <th>Status</th>
          <th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u):
          $assignments = $user_projects[$u->id] ?? [];
        ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-3">
              <?= user_avatar($u->first_name.' '.$u->last_name, $u->avatar ?? null, 36) ?>
              <div>
                <div class="fw-600 small"><?= html_escape($u->first_name.' '.$u->last_name) ?></div>
                <div class="small text-muted">
                  <?= html_escape($u->job_title ?? '') ?>
                  <?php if (!empty($u->is_client_admin)): ?>
                  <span class="badge bg-primary-soft text-primary ms-1">Admin</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </td>
          <td><span class="small"><?= html_escape($u->email) ?></span></td>
          <td>
            <?php if (!empty($u->is_client_admin)): ?>
            <span class="badge bg-primary-soft text-primary">All projects</span>
            <?php elseif (empty($assignments)): ?>
            <span class="small text-muted">No projects assigned</span>
            <?php else: ?>
            <div class="d-flex flex-wrap gap-1">
              <?php foreach ($assignments as $a): ?>
              <span class="badge bg-secondary-soft text-muted" title="<?= html_escape($perm_labels[$a->permission] ?? $a->permission) ?>">
                <?= html_escape($a->project_name) ?>
              </span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge <?= $u->status === 'active' ? 'bg-success-soft text-success' : 'bg-danger-soft text-danger' ?>">
              <?= ucfirst($u->status) ?>
            </span>
          </td>
          <td class="text-end">
            <?php if (empty($u->is_client_admin)): ?>
            <button class="btn btn-sm btn-ghost" onclick="openProjects(<?= $u->id ?>, <?= json_encode($u->first_name.' '.$u->last_name) ?>)" title="Assign Projects">
              <i class="bi bi-folder2-open"></i>
            </button>
            <button class="btn btn-sm btn-ghost" onclick="resetPassword(<?= $u->id ?>)" title="Reset Password">
              <i class="bi bi-key"></i>
            </button>
            <button class="btn btn-sm btn-ghost text-danger" onclick="removeUser(<?= $u->id ?>, this)" title="Remove">
              <i class="bi bi-person-dash"></i>
            </button>
            <?php else: ?>
            <span class="small text-muted">—</span>
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
        <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Add User &amp; Assign Projects</h5>
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
            <label class="form-label fw-600">Project Access <span class="text-muted fw-400">(select one or more)</span></label>
            <div class="border rounded p-3" style="max-height:260px;overflow-y:auto" id="addProjectList">
              <?php foreach ($projects as $p): ?>
              <div class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom border-light">
                <div class="form-check mb-0">
                  <input type="checkbox" class="form-check-input proj-check add-proj-check" id="addProj<?= $p->id ?>" data-project="<?= $p->id ?>">
                  <label class="form-check-label small fw-600" for="addProj<?= $p->id ?>"><?= html_escape($p->name) ?></label>
                </div>
                <select class="form-select form-select-sm add-proj-perm" data-project="<?= $p->id ?>" style="width:150px" disabled>
                  <option value="view">View only</option>
                  <option value="tickets" selected>View + Tickets</option>
                  <option value="manage">Full access</option>
                </select>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="form-text">A user can be assigned to multiple projects with different permission levels.</div>
          </div>
          <?php endif; ?>
        </div>
        <div id="addUserMsg" class="mt-3 d-none"></div>
      </div>
      <div class="modal-footer-modern">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveNewUser">
          <i class="bi bi-check-lg me-1"></i> Create User
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
        <h5 class="modal-title"><i class="bi bi-folder2-open me-2 text-primary"></i>Assign Projects — <span id="projUserName"></span></h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="projUserId">
        <p class="small text-muted mb-3">Check the projects this user should access. You can assign multiple projects.</p>
        <?php if (!empty($projects)): foreach ($projects as $p): ?>
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom border-light">
          <div class="form-check mb-0">
            <input type="checkbox" class="form-check-input proj-check edit-proj-check" id="editProj<?= $p->id ?>" data-project="<?= $p->id ?>">
            <label class="form-check-label small fw-600" for="editProj<?= $p->id ?>"><?= html_escape($p->name) ?></label>
          </div>
          <select class="form-select form-select-sm edit-proj-perm" data-project="<?= $p->id ?>" style="width:150px" disabled>
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
        <button type="button" class="btn btn-primary" id="saveProjects">Save Assignments</button>
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

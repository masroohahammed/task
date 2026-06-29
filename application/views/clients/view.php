<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('clients') ?>">Clients</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= html_escape($client->company_name) ?></span>
    </div>
    <h1 class="page-title"><?= html_escape($client->company_name) ?></h1>
    <div class="d-flex align-items-center gap-2 mt-1">
      <i class="bi bi-person-fill text-muted small"></i>
      <span class="small text-muted"><?= html_escape($client->contact_person) ?></span>
      <span class="text-muted">·</span>
      <i class="bi bi-envelope text-muted small"></i>
      <span class="small text-muted"><?= html_escape($client->email) ?></span>
      <?php if ($client->phone): ?>
      <span class="text-muted">·</span>
      <i class="bi bi-telephone text-muted small"></i>
      <span class="small text-muted"><?= html_escape($client->phone) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('clients/edit/'.$client->id) ?>" class="btn btn-ghost">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-person-plus me-1"></i> Add Portal User
    </button>
  </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="stat-card stat-primary">
      <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= count($projects) ?></div>
        <div class="stat-label">Projects</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card stat-warning">
      <div class="stat-icon"><i class="bi bi-ticket-detailed"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= count($tickets) ?></div>
        <div class="stat-label">Tickets</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card stat-success">
      <div class="stat-icon"><i class="bi bi-people"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= count($users) ?></div>
        <div class="stat-label">Portal Users</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card stat-teal">
      <div class="stat-icon"><i class="bi bi-buildings"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= html_escape($client->status ?? 'active') ?></div>
        <div class="stat-label">Status</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Projects -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-folder2-open me-2 text-primary"></i>Projects</h6>
        <?php if (has_permission('can_create_project')): ?>
        <a href="<?= site_url('projects/create') ?>" class="btn btn-sm btn-ghost">
          <i class="bi bi-plus me-1"></i>New
        </a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($projects)): ?>
        <div class="empty-state py-4"><i class="bi bi-folder-x"></i><p>No projects yet</p></div>
        <?php else: foreach ($projects as $p): ?>
        <div class="list-item">
          <div class="list-item-icon bg-primary-soft text-primary"><i class="bi bi-folder2-open"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('projects/view/'.$p->id) ?>" class="list-item-title"><?= html_escape($p->name) ?></a>
            <div class="list-item-meta">
              <?= status_badge($p->status) ?>
              <span class="small text-muted">Due: <?= $p->end_date ? date('M d, Y', strtotime($p->end_date)) : '—' ?></span>
            </div>
          </div>
          <div>
            <div class="progress" style="width:60px;height:5px">
              <div class="progress-bar bg-primary" style="width:<?= $p->progress ?>%"></div>
            </div>
            <div class="text-center small text-muted mt-1"><?= $p->progress ?>%</div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Tickets -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-ticket-detailed me-2 text-warning"></i>Support Tickets</h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($tickets)): ?>
        <div class="empty-state py-4"><i class="bi bi-ticket-detailed"></i><p>No tickets raised</p></div>
        <?php else: foreach ($tickets as $t): ?>
        <div class="list-item">
          <div class="list-item-icon bg-warning-soft text-warning"><i class="bi bi-ticket-detailed"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('tickets/view/'.$t->id) ?>" class="list-item-title"><?= html_escape($t->title) ?></a>
            <div class="list-item-meta">
              <span class="small text-muted"><?= html_escape($t->ticket_number ?? '') ?></span>
              <?= status_badge($t->status) ?>
              <?= priority_badge($t->priority) ?>
            </div>
          </div>
          <div class="list-item-time"><?= time_ago($t->created_at) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Portal Users (client logins) -->
  <div class="col-12">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-shield-lock me-2 text-success"></i>Portal Users (Client Logins)</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="bi bi-plus me-1"></i> Add User
        </button>
      </div>
      <div class="card-body p-0">
        <?php if (empty($users)): ?>
        <div class="empty-state py-4">
          <i class="bi bi-person-x"></i>
          <p>No portal users yet. Add one so the client can log in.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover table-modern">
            <thead><tr>
              <th>Name</th><th>Email</th><th>Job Title</th><th>Role</th><th>Status</th><th>Last Login</th><th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-3">
                  <?= user_avatar($u->first_name.' '.$u->last_name, $u->avatar ?? null, 36) ?>
                  <div>
                    <div class="fw-600 small"><?= html_escape($u->first_name.' '.$u->last_name) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="small"><?= html_escape($u->email) ?></span></td>
              <td><span class="small text-muted"><?= html_escape($u->job_title ?? '—') ?></span></td>
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
              <td><span class="small text-muted"><?= $u->last_login ? time_ago($u->last_login) : 'Never' ?></span></td>
              <td class="text-end">
                <?php if (empty($u->is_client_admin)): ?>
                <button class="btn btn-sm btn-ghost" onclick="openProjects(<?= $u->id ?>, '<?= html_escape($u->first_name.' '.$u->last_name, true) ?>')" title="Project Access">
                  <i class="bi bi-folder2-open"></i>
                </button>
                <?php endif; ?>
                <button class="btn btn-sm btn-ghost" onclick="resetPassword(<?= $u->id ?>)" title="Reset Password">
                  <i class="bi bi-key"></i>
                </button>
                <button class="btn btn-sm btn-ghost text-danger" onclick="removeUser(<?= $u->id ?>, this)" title="Remove">
                  <i class="bi bi-person-dash"></i>
                </button>
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

  <!-- Client Info -->
  <?php if (!empty($client->address) || !empty($client->website ?? null) || !empty($client->notes ?? null)): ?>
  <div class="col-12">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-info-circle me-2"></i>Additional Info</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php if ($client->address): ?>
          <div class="col-md-4">
            <div class="detail-label">Address</div>
            <div class="detail-text"><?= nl2br(html_escape($client->address)) ?></div>
          </div>
          <?php endif; ?>
          <?php if (isset($client->website) && $client->website): ?>
          <div class="col-md-4">
            <div class="detail-label">Website</div>
            <a href="<?= html_escape($client->website) ?>" target="_blank" class="detail-text"><?= html_escape($client->website) ?></a>
          </div>
          <?php endif; ?>
          <?php if (isset($client->notes) && $client->notes): ?>
          <div class="col-md-4">
            <div class="detail-label">Notes</div>
            <div class="detail-text"><?= nl2br(html_escape($client->notes)) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Add Portal User</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="newUserFirst" placeholder="John">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-600">Last Name</label>
            <input type="text" class="form-control" id="newUserLast" placeholder="Smith">
          </div>
          <div class="col-12">
            <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="newUserEmail" placeholder="john@company.com">
          </div>
          <div class="col-12">
            <label class="form-label fw-600">Job Title</label>
            <input type="text" class="form-control" id="newUserTitle" placeholder="e.g. Project Lead">
          </div>
          <div class="col-12">
            <label class="form-label fw-600">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="newUserPass" placeholder="Min 6 characters">
          </div>
        </div>
        <div id="addUserMsg" class="mt-3 d-none"></div>
      </div>
      <div class="modal-footer-modern">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveNewUser">
          <i class="bi bi-check-lg me-1"></i> Add User
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
        <p class="text-muted small mb-0">No projects for this client yet.</p>
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

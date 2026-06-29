<div class="page-header">
  <div>
    <h1 class="page-title">Client Portal</h1>
    <p class="page-subtitle">Welcome back! Track your projects and support requests.</p>
  </div>
  <div class="page-actions">
    <?php if (is_client_admin()): ?>
    <a href="<?= site_url('client-portal/users') ?>" class="btn btn-primary">
      <i class="bi bi-person-plus me-1"></i> Add Users &amp; Assign Projects
    </a>
    <?php endif; ?>
    <a href="<?= site_url('tickets/create') ?>" class="btn btn-ghost">
      <i class="bi bi-plus-lg me-1"></i> New Ticket
    </a>
  </div>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card stat-primary">
    <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $total_projects ?></div>
      <div class="stat-label">My Projects</div>
    </div>
  </div>
  <div class="stat-card stat-warning">
    <div class="stat-icon"><i class="bi bi-ticket-detailed"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $open_tickets ?></div>
      <div class="stat-label">Open Tickets</div>
    </div>
  </div>
  <div class="stat-card stat-success">
    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= count($my_tickets ?? []) - $open_tickets ?></div>
      <div class="stat-label">Resolved</div>
    </div>
  </div>
</div>

<?php if (!empty($team_users) && is_client_admin()): ?>
<div class="row g-4 mt-1">
  <div class="col-12">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-people me-2 text-primary"></i>Team &amp; Project Access</h6>
        <a href="<?= site_url('client-portal/users') ?>" class="btn btn-sm btn-primary">
          <i class="bi bi-person-plus me-1"></i> Manage Users
        </a>
      </div>
      <div class="card-body p-0">
        <?php if (count($team_users) <= 1): ?>
        <div class="empty-state py-4">
          <i class="bi bi-person-plus"></i>
          <p class="mb-2">Add sub-users and assign them to specific projects.</p>
          <a href="<?= site_url('client-portal/users') ?>" class="btn btn-primary btn-sm">Add User</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-modern table-hover mb-0">
            <thead><tr>
              <th>User</th><th>Projects</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($team_users as $tu):
              if (!empty($tu->is_client_admin)) continue;
              $assignments = $user_projects[$tu->id] ?? [];
            ?>
            <tr>
              <td>
                <div class="fw-600 small"><?= html_escape($tu->first_name.' '.$tu->last_name) ?></div>
                <div class="small text-muted"><?= html_escape($tu->email) ?></div>
              </td>
              <td>
                <?php if (empty($assignments)): ?>
                <span class="small text-muted">No projects</span>
                <?php else: ?>
                <div class="d-flex flex-wrap gap-1">
                  <?php foreach ($assignments as $a): ?>
                  <span class="badge bg-secondary-soft text-muted"><?= html_escape($a->project_name) ?></span>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>
              </td>
              <td class="text-end">
                <a href="<?= site_url('client-portal/users') ?>" class="btn btn-sm btn-ghost">Edit</a>
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
<?php endif; ?>

<div class="row g-4 mt-1">
  <div class="col-xl-7">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-folder2-open me-2 text-primary"></i>My Projects</h6>
      </div>
      <div class="card-body p-0">
        <?php if (empty($projects)): ?>
        <div class="empty-state py-4"><i class="bi bi-folder-x"></i><p>No projects assigned</p></div>
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
            <div class="progress" style="width:70px;height:6px">
              <div class="progress-bar bg-primary" style="width:<?= $p->progress ?>%"></div>
            </div>
            <div class="text-center small text-muted mt-1"><?= $p->progress ?>%</div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-ticket-detailed me-2 text-warning"></i>My Tickets</h6>
        <a href="<?= site_url('tickets/create') ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($my_tickets)): ?>
        <div class="empty-state py-4"><i class="bi bi-ticket-detailed"></i><p>No tickets yet</p></div>
        <?php else: foreach ($my_tickets as $t): ?>
        <div class="list-item">
          <div class="list-item-icon bg-warning-soft text-warning"><i class="bi bi-ticket-detailed"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('tickets/view/'.$t->id) ?>" class="list-item-title small"><?= html_escape($t->title) ?></a>
            <div class="list-item-meta">
              <span class="small text-muted"><?= html_escape($t->ticket_number ?? '') ?></span>
              <?= status_badge($t->status) ?>
            </div>
          </div>
          <div class="list-item-time"><?= time_ago($t->created_at) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

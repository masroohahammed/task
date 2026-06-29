<div class="page-header">
  <div>
    <h1 class="page-title">Projects</h1>
    <p class="page-subtitle">Manage all your projects in one place</p>
  </div>
  <div class="page-actions">
    <?php if (has_permission('can_create_project')): ?>
    <a href="<?= site_url('projects/create') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Project
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Search & Filter -->
<div class="filter-bar mb-4">
  <input type="text" class="form-control" id="searchProjects" placeholder="Search projects..." style="max-width:280px">
  <select class="form-select" id="filterStatus" style="width:150px">
    <option value="">All Status</option>
    <option value="planning">Planning</option>
    <option value="active">Active</option>
    <option value="on_hold">On Hold</option>
    <option value="completed">Completed</option>
  </select>
  <div class="ms-auto">
    <div class="btn-group">
      <button class="btn btn-outline-secondary active" id="viewGrid"><i class="bi bi-grid-3x2"></i></button>
      <button class="btn btn-outline-secondary" id="viewList"><i class="bi bi-list-ul"></i></button>
    </div>
  </div>
</div>

<!-- Projects Grid -->
<div class="row g-4" id="projectsGrid">
  <?php if (empty($projects)): ?>
  <div class="col-12">
    <div class="empty-page-state">
      <div class="empty-icon"><i class="bi bi-folder-plus"></i></div>
      <h4>No projects yet</h4>
      <p>Create your first project to get started</p>
      <?php if (has_permission('can_create_project')): ?>
      <a href="<?= site_url('projects/create') ?>" class="btn btn-primary">Create Project</a>
      <?php endif; ?>
    </div>
  </div>
  <?php else: foreach ($projects as $p): ?>
  <div class="col-xl-4 col-md-6 project-item" data-status="<?= $p->status ?>">
    <div class="project-card">
      <div class="project-card-header">
        <div class="project-color" style="background:<?= ['#194999','#8b5cf6','#06b6d4','#10b981','#f59e0b'][crc32($p->name) % 5] ?>"></div>
        <div class="project-status-wrap">
          <?= status_badge($p->status) ?>
          <?= priority_badge($p->priority) ?>
        </div>
        <?php if (has_permission('can_manage_project')): ?>
        <div class="project-menu">
          <div class="dropdown">
            <button class="btn btn-icon btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= site_url('projects/view/'.$p->id) ?>"><i class="bi bi-eye me-2"></i>View</a></li>
              <li><a class="dropdown-item" href="<?= site_url('projects/kanban/'.$p->id) ?>"><i class="bi bi-kanban me-2"></i>Kanban</a></li>
              <li><a class="dropdown-item" href="<?= site_url('projects/edit/'.$p->id) ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= site_url('projects/delete/'.$p->id) ?>"
                onclick="return confirm('Delete this project?')"><i class="bi bi-trash me-2"></i>Delete</a></li>
            </ul>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <div class="project-card-body">
        <h6 class="project-name">
          <a href="<?= site_url('projects/view/'.$p->id) ?>"><?= html_escape($p->name) ?></a>
        </h6>
        <?php if (isset($p->company_name) && $p->company_name): ?>
        <div class="project-client"><i class="bi bi-buildings me-1"></i><?= html_escape($p->company_name) ?></div>
        <?php endif; ?>
        <div class="project-desc text-muted small"><?= html_escape(substr($p->description ?? '', 0, 80)) ?>...</div>

        <div class="project-progress mt-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="small text-muted">Progress</span>
            <span class="small fw-600"><?= $p->progress ?>%</span>
          </div>
          <div class="progress" style="height:6px">
            <div class="progress-bar" style="width:<?= $p->progress ?>%;background:<?= ['#194999','#8b5cf6','#06b6d4','#10b981','#f59e0b'][crc32($p->name) % 5] ?>"></div>
          </div>
        </div>

        <div class="project-stats mt-3">
          <div class="project-stat">
            <i class="bi bi-check2-square text-primary"></i>
            <span><?= $p->task_count ?? 0 ?> Tasks</span>
          </div>
          <div class="project-stat">
            <i class="bi bi-bug text-danger"></i>
            <span><?= $p->issue_count ?? 0 ?> Issues</span>
          </div>
          <div class="project-stat">
            <i class="bi bi-ticket-detailed text-warning"></i>
            <span><?= $p->ticket_count ?? 0 ?> Tickets</span>
          </div>
        </div>
      </div>

      <div class="project-card-footer">
        <div class="avatar-group">
          <?php if (isset($p->members)): foreach (array_slice($p->members, 0, 4) as $m): ?>
          <?= user_avatar($m->first_name . ' ' . $m->last_name, $m->avatar, 28) ?>
          <?php endforeach; if (count($p->members) > 4): ?>
          <div class="avatar-more">+<?= count($p->members) - 4 ?></div>
          <?php endif; endif; ?>
        </div>
        <div class="project-date">
          <i class="bi bi-calendar3"></i>
          <?= $p->end_date ? date('M d, Y', strtotime($p->end_date)) : 'No deadline' ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

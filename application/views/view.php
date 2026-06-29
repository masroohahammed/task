<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('projects') ?>">Projects</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= html_escape($project->name) ?></span>
    </div>
    <h1 class="page-title"><?= html_escape($project->name) ?></h1>
    <div class="d-flex align-items-center gap-2 mt-1">
      <?= status_badge($project->status) ?>
      <?= priority_badge($project->priority) ?>
      <?php if ($project->company_name): ?>
      <span class="small text-muted"><i class="bi bi-buildings me-1"></i><?= html_escape($project->company_name) ?></span>
      <?php endif; ?>
    </div>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('projects/kanban/'.$project->id) ?>" class="btn btn-primary">
      <i class="bi bi-kanban me-1"></i> Task Board
    </a>
    <a href="<?= site_url('discussions/'.$project->id) ?>" class="btn btn-ghost">
      <i class="bi bi-chat-dots me-1"></i> Discussion
    </a>
    <a href="<?= site_url('issues/kanban/'.$project->id) ?>" class="btn btn-outline-danger">
      <i class="bi bi-bug me-1"></i> Issue Board
    </a>
    <?php if (has_permission('can_manage_project')): ?>
    <a href="<?= site_url('projects/edit/'.$project->id) ?>" class="btn btn-ghost">
      <i class="bi bi-pencil me-1"></i> Edit
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
  <?php
  $tstats = $task_stats;
  $istats = $issue_stats;
  $tkstats = $ticket_stats;
  ?>
  <div class="col-md-3">
    <div class="stat-card stat-primary">
      <div class="stat-icon"><i class="bi bi-check2-square"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= $tstats['total'] ?></div>
        <div class="stat-label">Total Tasks</div>
        <div class="stat-sub text-success"><?= $tstats['done'] ?> done</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card stat-danger">
      <div class="stat-icon"><i class="bi bi-bug"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= $istats['total'] ?></div>
        <div class="stat-label">Issues</div>
        <div class="stat-sub text-danger"><?= $istats['open'] ?> open</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card stat-warning">
      <div class="stat-icon"><i class="bi bi-ticket-detailed"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= $tkstats['total'] ?></div>
        <div class="stat-label">Tickets</div>
        <div class="stat-sub text-warning"><?= $tkstats['open'] ?> open</div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card stat-success">
      <div class="stat-icon"><i class="bi bi-people"></i></div>
      <div class="stat-body">
        <div class="stat-value"><?= count($members) ?></div>
        <div class="stat-label">Team Members</div>
      </div>
    </div>
  </div>
</div>

<!-- Progress -->
<div class="card card-modern mb-4">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between mb-2">
      <span class="fw-600">Overall Progress</span>
      <span class="fw-700 text-primary"><?= $project->progress ?>%</span>
    </div>
    <div class="progress" style="height:12px;border-radius:8px">
      <div class="progress-bar bg-primary" style="width:<?= $project->progress ?>%;border-radius:8px"></div>
    </div>
    <div class="d-flex gap-4 mt-2">
      <?php foreach (['todo'=>['secondary','To Do'],'in_progress'=>['primary','In Progress'],'testing'=>['info','Testing'],'done'=>['success','Done']] as $s=>[$c,$l]): ?>
      <div class="d-flex align-items-center gap-1 small text-muted">
        <span class="legend-dot bg-<?= $c ?>"></span>
        <span><?= $l ?> (<?= $tstats[$s] ?? 0 ?>)</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent Tasks -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-check2-square me-2 text-primary"></i>Recent Tasks</h6>
        <a href="<?= site_url('projects/kanban/'.$project->id) ?>" class="btn btn-sm btn-ghost">Kanban <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recent_tasks)): ?>
        <div class="empty-state py-4"><i class="bi bi-check-circle"></i><p>No tasks yet</p></div>
        <?php else: foreach ($recent_tasks as $t): ?>
        <div class="list-item">
          <div class="list-item-icon bg-primary-soft text-primary"><i class="bi bi-check2-square"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('tasks/view/'.$t->id) ?>" class="list-item-title"><?= html_escape($t->title) ?></a>
            <div class="list-item-meta">
              <?= status_badge($t->status) ?>
              <?= priority_badge($t->priority) ?>
              <?php if ($t->first_name): ?>
              <span class="small text-muted"><?= html_escape($t->first_name . ' ' . $t->last_name) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Issues -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-bug me-2 text-danger"></i>Recent Issues</h6>
        <a href="<?= site_url('issues/kanban/'.$project->id) ?>" class="btn btn-sm btn-ghost">Board <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recent_issues)): ?>
        <div class="empty-state py-4"><i class="bi bi-check-circle"></i><p>No issues reported</p></div>
        <?php else: foreach ($recent_issues as $i): ?>
        <div class="list-item">
          <div class="list-item-icon bg-danger-soft text-danger"><i class="bi bi-bug"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('issues/view/'.$i->id) ?>" class="list-item-title"><?= html_escape($i->title) ?></a>
            <div class="list-item-meta">
              <?= status_badge($i->status) ?>
              <span class="badge severity-<?= $i->severity ?>"><?= ucfirst($i->severity) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- Team Members -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-people me-2 text-success"></i>Team Members</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <?php foreach ($members as $m): ?>
          <div class="col-md-6">
            <div class="d-flex align-items-center gap-3 p-2 rounded" style="border:1px solid var(--border)">
              <?= user_avatar($m->first_name . ' ' . $m->last_name, $m->avatar ?? null, 40) ?>
              <div>
                <div class="fw-600 small"><?= html_escape($m->first_name . ' ' . $m->last_name) ?></div>
                <div class="small text-muted"><?= html_escape($m->job_title ?? $m->role) ?></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Activity -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-teal"></i>Activity</h6>
      </div>
      <div class="activity-timeline">
        <?php if (empty($activities)): ?>
        <div class="empty-state py-3"><i class="bi bi-clock"></i><p>No activity</p></div>
        <?php else: foreach ($activities as $a): ?>
        <div class="activity-item">
          <div class="activity-dot"></div>
          <div class="activity-content small">
            <span class="activity-user"><?= html_escape($a->first_name . ' ' . $a->last_name) ?></span>
            <span class="activity-action text-muted"> <?= html_escape($a->action) ?></span>
          </div>
          <div class="activity-time"><?= time_ago($a->created_at) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="page-header">
  <div>
    <h1 class="page-title">My Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?= html_escape($current_user->first_name) ?>! Here are your assignments.</p>
  </div>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr)">
  <div class="stat-card stat-primary">
    <div class="stat-icon"><i class="bi bi-check2-square"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $total_my_tasks ?></div>
      <div class="stat-label">Total Tasks</div>
    </div>
  </div>
  <div class="stat-card stat-warning">
    <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $pending_tasks ?></div>
      <div class="stat-label">To Do</div>
    </div>
  </div>
  <div class="stat-card stat-indigo">
    <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $in_progress_tasks ?></div>
      <div class="stat-label">In Progress</div>
    </div>
  </div>
  <div class="stat-card stat-success">
    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $done_tasks ?></div>
      <div class="stat-label">Done</div>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <!-- My Tasks -->
  <div class="col-xl-8">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-check2-square me-2 text-primary"></i>My Tasks</h6>
        <a href="<?= site_url('projects') ?>" class="btn btn-sm btn-ghost">All Projects <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($my_tasks)): ?>
        <div class="empty-state py-4"><i class="bi bi-check-circle"></i><p>No tasks assigned to you</p></div>
        <?php else: foreach ($my_tasks as $t): ?>
        <div class="list-item">
          <div class="list-item-icon bg-primary-soft text-primary"><i class="bi bi-check2-square"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('tasks/view/'.$t->id) ?>" class="list-item-title"><?= html_escape($t->title) ?></a>
            <div class="list-item-meta">
              <span class="small text-muted"><?= html_escape($t->project_name) ?></span>
              <?= status_badge($t->status) ?>
              <?= priority_badge($t->priority) ?>
              <?php if ($t->deadline): ?>
              <span class="small <?= strtotime($t->deadline) < time() ? 'text-danger' : 'text-muted' ?>">
                <i class="bi bi-calendar3"></i> <?= date('M d', strtotime($t->deadline)) ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <div class="progress" style="width:60px;height:6px">
              <div class="progress-bar bg-primary" style="width:<?= $t->progress ?>%"></div>
            </div>
            <div class="text-center small text-muted mt-1"><?= $t->progress ?>%</div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <!-- My Issues & Activity -->
  <div class="col-xl-4">
    <?php if (!empty($my_issues)): ?>
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-bug me-2 text-danger"></i>My Issues</h6>
      </div>
      <div class="card-body p-0">
        <?php foreach ($my_issues as $i): ?>
        <div class="list-item">
          <div class="list-item-icon bg-danger-soft text-danger"><i class="bi bi-bug"></i></div>
          <div class="list-item-body">
            <a href="<?= site_url('issues/view/'.$i->id) ?>" class="list-item-title small"><?= html_escape($i->title) ?></a>
            <div class="list-item-meta"><?= status_badge($i->status) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-teal"></i>My Activity</h6>
      </div>
      <div class="activity-timeline">
        <?php if (empty($recent_activities)): ?>
        <div class="empty-state py-3"><i class="bi bi-clock"></i><p>No recent activity</p></div>
        <?php else: foreach ($recent_activities as $a): ?>
        <div class="activity-item">
          <div class="activity-dot"></div>
          <div class="activity-content small"><span class="activity-action"><?= html_escape($a->action) ?></span></div>
          <div class="activity-time"><?= time_ago($a->created_at) ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

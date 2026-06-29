<div class="page-header">
  <div>
    <h1 class="page-title">Reports & Analytics</h1>
    <p class="page-subtitle">Overview of your system performance and activity</p>
  </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid mb-4">
  <div class="stat-card stat-primary">
    <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $total_projects ?></div>
      <div class="stat-label">Total Projects</div>
      <div class="stat-sub text-success"><?= $active_projects ?> active · <?= $completed_projects ?> done</div>
    </div>
  </div>
  <div class="stat-card stat-indigo">
    <div class="stat-icon"><i class="bi bi-check2-square"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $total_tasks ?></div>
      <div class="stat-label">Total Tasks</div>
      <div class="stat-sub text-success"><?= $task_stats['done'] ?? 0 ?> completed</div>
    </div>
  </div>
  <div class="stat-card stat-danger">
    <div class="stat-icon"><i class="bi bi-bug"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $open_issues ?></div>
      <div class="stat-label">Open Issues</div>
      <div class="stat-sub text-muted"><?= $closed_issues ?> closed</div>
    </div>
  </div>
  <div class="stat-card stat-warning">
    <div class="stat-icon"><i class="bi bi-ticket-detailed"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $open_tickets ?></div>
      <div class="stat-label">Open Tickets</div>
      <div class="stat-sub text-muted"><?= $resolved_tickets ?> resolved</div>
    </div>
  </div>
  <div class="stat-card stat-teal">
    <div class="stat-icon"><i class="bi bi-buildings"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $total_clients ?></div>
      <div class="stat-label">Clients</div>
    </div>
  </div>
  <div class="stat-card stat-success">
    <div class="stat-icon"><i class="bi bi-people"></i></div>
    <div class="stat-body">
      <div class="stat-value"><?= $total_users ?></div>
      <div class="stat-label">Team Members</div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Task Breakdown -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-bar-chart me-2 text-primary"></i>Task Status Breakdown</h6>
      </div>
      <div class="card-body">
        <?php
        $ts = $task_stats;
        $total_t = max(array_sum($ts), 1);
        $bars = [
          'todo'        => ['To Do',       'bg-secondary', $ts['todo'] ?? 0],
          'in_progress' => ['In Progress', 'bg-primary',   $ts['in_progress'] ?? 0],
          'testing'     => ['Testing',     'bg-info',      $ts['testing'] ?? 0],
          'done'        => ['Done',        'bg-success',   $ts['done'] ?? 0],
        ];
        foreach ($bars as [$label, $cls, $val]):
        $pct = round($val / $total_t * 100);
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="small fw-600"><?= $label ?></span>
            <span class="small text-muted"><?= $val ?> tasks (<?= $pct ?>%)</span>
          </div>
          <div class="progress" style="height:10px;border-radius:6px">
            <div class="progress-bar <?= $cls ?>" style="width:<?= $pct ?>%;border-radius:6px"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Issue Breakdown -->
  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-bug me-2 text-danger"></i>Issue Status Breakdown</h6>
      </div>
      <div class="card-body">
        <?php
        $is = $issue_stats;
        $total_i = max(array_sum($is), 1);
        $ibars = [
          'open'        => ['Open',        'bg-danger',    $is['open'] ?? 0],
          'in_progress' => ['In Progress', 'bg-primary',   $is['in_progress'] ?? 0],
          'fixed'       => ['Fixed',       'bg-success',   $is['fixed'] ?? 0],
          'retesting'   => ['Retesting',   'bg-warning',   $is['retesting'] ?? 0],
          'closed'      => ['Closed',      'bg-dark',      $is['closed'] ?? 0],
        ];
        foreach ($ibars as [$label, $cls, $val]):
        $pct = round($val / $total_i * 100);
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="small fw-600"><?= $label ?></span>
            <span class="small text-muted"><?= $val ?> issues (<?= $pct ?>%)</span>
          </div>
          <div class="progress" style="height:10px;border-radius:6px">
            <div class="progress-bar <?= $cls ?>" style="width:<?= $pct ?>%;border-radius:6px"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Projects Table -->
  <div class="col-12">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-folder2-open me-2 text-primary"></i>Project Status Report</h6>
        <a href="<?= site_url('projects') ?>" class="btn btn-sm btn-ghost">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-modern">
          <thead><tr>
            <th>Project</th><th>Status</th><th>Priority</th><th>Start</th><th>End</th><th>Progress</th>
          </tr></thead>
          <tbody>
          <?php foreach ($recent_projects as $p): ?>
          <tr>
            <td>
              <a href="<?= site_url('projects/view/'.$p->id) ?>" class="fw-600 link-hover text-dark">
                <?= html_escape($p->name) ?>
              </a>
              <?php if (isset($p->company_name) && $p->company_name): ?>
              <div class="small text-muted"><?= html_escape($p->company_name) ?></div>
              <?php endif; ?>
            </td>
            <td><?= status_badge($p->status) ?></td>
            <td><?= priority_badge($p->priority) ?></td>
            <td><span class="small text-muted"><?= $p->start_date ? date('M d, Y', strtotime($p->start_date)) : '—' ?></span></td>
            <td><span class="small text-muted"><?= $p->end_date ? date('M d, Y', strtotime($p->end_date)) : '—' ?></span></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-1" style="height:6px;min-width:80px">
                  <div class="progress-bar bg-primary" style="width:<?= $p->progress ?>%"></div>
                </div>
                <span class="small text-muted"><?= $p->progress ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Activity Log -->
  <div class="col-12">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-teal"></i>Recent Activity Log</h6>
      </div>
      <div class="activity-timeline px-4 py-2">
        <?php foreach ($recent_activities as $a): ?>
        <div class="activity-item">
          <div class="activity-dot"></div>
          <div class="activity-content">
            <span class="activity-user fw-600"><?= html_escape($a->first_name . ' ' . $a->last_name) ?></span>
            <span class="activity-action text-muted"> — <?= html_escape($a->action) ?></span>
          </div>
          <div class="activity-time"><?= time_ago($a->created_at) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

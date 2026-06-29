<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('projects') ?>">Projects</a>
      <i class="bi bi-chevron-right"></i>
      <a href="<?= site_url('projects/view/'.$project->id) ?>"><?= html_escape($project->name) ?></a>
      <i class="bi bi-chevron-right"></i>
      <span>Issue Board</span>
    </div>
    <h1 class="page-title">Issue Tracker Board</h1>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('projects/kanban/'.$project->id) ?>" class="btn btn-outline-primary">
      <i class="bi bi-kanban me-1"></i> Task Board
    </a>
    <?php if (has_permission('can_raise_issue')): ?>
    <a href="<?= site_url('issues/create/'.$project->id) ?>" class="btn btn-danger">
      <i class="bi bi-plus-lg me-1"></i> Report Issue
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters -->
<div class="kanban-filters mb-3">
  <div class="filter-group">
    <label class="filter-label">Priority</label>
    <select class="form-select form-select-sm" id="filterPriority" style="width:130px">
      <option value="">All</option>
      <option value="critical">Critical</option>
      <option value="high">High</option>
      <option value="medium">Medium</option>
      <option value="low">Low</option>
    </select>
  </div>
  <div class="filter-group">
    <label class="filter-label">Severity</label>
    <select class="form-select form-select-sm" id="filterSeverity" style="width:130px">
      <option value="">All</option>
      <option value="blocker">Blocker</option>
      <option value="major">Major</option>
      <option value="minor">Minor</option>
    </select>
  </div>
  <div class="filter-group">
    <label class="filter-label">Assigned to</label>
    <select class="form-select form-select-sm" id="filterAssignee" style="width:160px">
      <option value="">All Members</option>
      <?php foreach ($members as $m): ?>
      <option value="<?= $m->user_id ?>"><?= html_escape($m->first_name . ' ' . $m->last_name) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<!-- Issue Kanban -->
<div class="kanban-board kanban-issue-board" id="issueBoardKanban">

  <?php
  $columns = [
    'open'        => ['Open',       'danger',    $issues_open],
    'in_progress' => ['In Progress','primary',   $issues_progress],
    'fixed'       => ['Fixed',      'success',   $issues_fixed],
    'retesting'   => ['Retesting',  'warning',   $issues_retesting],
    'closed'      => ['Closed',     'dark',      $issues_closed],
  ];
  foreach ($columns as $status => [$label, $color, $issues]):
  ?>
  <div class="kanban-col" data-status="<?= $status ?>">
    <div class="kanban-col-header">
      <div class="kanban-col-title">
        <span class="kanban-col-dot dot-<?= $color ?>"></span>
        <span><?= $label ?></span>
        <span class="kanban-col-count"><?= count($issues) ?></span>
      </div>
    </div>
    <div class="kanban-cards issue-sortable" id="issue-col-<?= $status ?>" data-status="<?= $status ?>">
      <?php foreach ($issues as $issue): ?>
      <div class="kanban-card issue-card"
        data-issue-id="<?= $issue->id ?>"
        data-priority="<?= $issue->priority ?>"
        data-severity="<?= $issue->severity ?>"
        data-assignee="<?= $issue->assigned_to ?>"
        draggable="true">
        
        <div class="card-priority-bar priority-<?= $issue->priority ?>"></div>
        <div class="card-body-inner">

          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="issue-number"><?= html_escape($issue->issue_number ?? 'ISS') ?></span>
            <span class="badge severity-<?= $issue->severity ?>"><?= ucfirst($issue->severity) ?></span>
          </div>

          <div class="card-title-text mb-2">
            <a href="<?= site_url('issues/view/'.$issue->id) ?>"><?= html_escape($issue->title) ?></a>
          </div>

          <div class="card-meta mb-2">
            <?= priority_badge($issue->priority) ?>
            <span class="small text-muted"><i class="bi bi-clock"></i> <?= time_ago($issue->created_at) ?></span>
          </div>

          <div class="card-footer-inner">
            <div class="d-flex gap-1 align-items-center">
              <span class="small text-muted"><i class="bi bi-person"></i>
                <?= html_escape($issue->reporter_first . ' ' . $issue->reporter_last) ?>
              </span>
            </div>
            <?php if ($issue->assignee_first): ?>
            <div>
              <?= user_avatar($issue->assignee_first . ' ' . $issue->assignee_last, $issue->assignee_avatar ?? null, 24) ?>
            </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
      <?php endforeach; ?>
      <div class="kanban-drop-placeholder">Drop here</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<script>
const PROJECT_ID = <?= $project->id ?>;
const CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
const BASE_URL = '<?= base_url() ?>';
</script>

<div class="page-header">
  <div>
    <h1 class="page-title">Issue Tracker</h1>
    <p class="page-subtitle">All reported bugs and issues across projects</p>
  </div>
  <div class="page-actions gap-2">
    <?php if (has_permission('can_raise_issue')): ?>
    <a href="<?= site_url('issues/create') ?>" class="btn btn-danger">
      <i class="bi bi-plus-lg me-1"></i> Report Issue
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filters -->
<div class="filter-bar mb-4">
  <input type="text" class="form-control" id="searchIssues" placeholder="Search issues..." style="max-width:240px">
  <select class="form-select" id="filterStatus" style="width:150px">
    <option value="">All Status</option>
    <option value="open">Open</option>
    <option value="in_progress">In Progress</option>
    <option value="fixed">Fixed</option>
    <option value="retesting">Retesting</option>
    <option value="closed">Closed</option>
  </select>
  <select class="form-select" id="filterPriority" style="width:130px">
    <option value="">All Priority</option>
    <option value="critical">Critical</option>
    <option value="high">High</option>
    <option value="medium">Medium</option>
    <option value="low">Low</option>
  </select>
  <select class="form-select" id="filterProject" style="width:180px">
    <option value="">All Projects</option>
    <?php foreach ($projects as $p): ?>
    <option value="<?= $p->id ?>"><?= html_escape($p->name) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div class="card card-modern">
  <div class="card-body p-0">
    <?php if (empty($issues)): ?>
    <div class="empty-state py-5">
      <i class="bi bi-bug"></i>
      <h5>No issues found</h5>
      <p>No bugs have been reported yet</p>
      <?php if (has_permission('can_raise_issue')): ?>
      <a href="<?= site_url('issues/create') ?>" class="btn btn-danger btn-sm">Report Issue</a>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-modern" id="issuesTable">
        <thead><tr>
          <th>Issue</th><th>Project</th><th>Priority</th><th>Severity</th>
          <th>Status</th><th>Reported By</th><th>Assigned To</th><th>Created</th><th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($issues as $i): ?>
        <tr data-status="<?= $i->status ?>" data-priority="<?= $i->priority ?>" data-project="<?= $i->project_id ?>">
          <td>
            <a href="<?= site_url('issues/view/'.$i->id) ?>" class="fw-600 text-dark link-hover">
              <?= html_escape($i->title) ?>
            </a>
            <div class="small text-muted"><?= html_escape($i->issue_number ?? '') ?></div>
          </td>
          <td><span class="small text-muted"><?= html_escape($i->project_name) ?></span></td>
          <td><?= priority_badge($i->priority) ?></td>
          <td><span class="badge severity-<?= $i->severity ?>"><?= ucfirst($i->severity) ?></span></td>
          <td><?= status_badge($i->status) ?></td>
          <td><span class="small"><?= html_escape($i->reporter_first . ' ' . $i->reporter_last) ?></span></td>
          <td>
            <?php if ($i->assignee_first): ?>
            <div class="d-flex align-items-center gap-2">
              <?= user_avatar($i->assignee_first . ' ' . $i->assignee_last, null, 26) ?>
              <span class="small"><?= html_escape($i->assignee_first) ?></span>
            </div>
            <?php else: ?><span class="badge bg-secondary-soft text-secondary small">Unassigned</span><?php endif; ?>
          </td>
          <td><span class="small text-muted"><?= time_ago($i->created_at) ?></span></td>
          <td class="text-end">
            <a href="<?= site_url('issues/view/'.$i->id) ?>" class="btn btn-sm btn-ghost"><i class="bi bi-eye"></i></a>
            <?php if (has_permission('can_manage_project')): ?>
            <a href="<?= site_url('issues/delete/'.$i->id) ?>" class="btn btn-sm btn-ghost text-danger"
              onclick="return confirm('Delete issue?')"><i class="bi bi-trash"></i></a>
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

<script>
$('#searchIssues').on('input', filterIssues);
$('#filterStatus, #filterPriority, #filterProject').change(filterIssues);

function filterIssues() {
  const search  = $('#searchIssues').val().toLowerCase();
  const status  = $('#filterStatus').val();
  const priority= $('#filterPriority').val();
  const project = $('#filterProject').val();
  $('#issuesTable tbody tr').each(function() {
    const ok = (!search || $(this).text().toLowerCase().includes(search))
             && (!status || $(this).data('status') === status)
             && (!priority || $(this).data('priority') === priority)
             && (!project || String($(this).data('project')) === project);
    $(this).toggle(ok);
  });
}
</script>

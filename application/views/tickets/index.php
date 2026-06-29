<div class="page-header">
  <div>
    <h1 class="page-title">Support Tickets</h1>
    <p class="page-subtitle">Track and manage client support requests</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('tickets/create') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> New Ticket
    </a>
  </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar mb-4">
  <input type="text" class="form-control" id="searchTickets" placeholder="Search tickets..." style="max-width:250px">
  <select class="form-select" id="filterStatus" style="width:150px">
    <option value="">All Status</option>
    <option value="open">Open</option>
    <option value="in_progress">In Progress</option>
    <option value="resolved">Resolved</option>
    <option value="closed">Closed</option>
  </select>
  <select class="form-select" id="filterPriority" style="width:130px">
    <option value="">All Priority</option>
    <option value="critical">Critical</option>
    <option value="high">High</option>
    <option value="medium">Medium</option>
    <option value="low">Low</option>
  </select>
</div>

<!-- Tickets Table -->
<div class="card card-modern">
  <div class="card-body p-0">
    <?php if (empty($tickets)): ?>
    <div class="empty-state py-5">
      <i class="bi bi-ticket-detailed"></i>
      <h5>No tickets found</h5>
      <p>No support tickets have been raised yet</p>
      <a href="<?= site_url('tickets/create') ?>" class="btn btn-primary btn-sm">Create Ticket</a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-modern" id="ticketsTable">
        <thead><tr>
          <th>Ticket</th>
          <th>Project</th>
          <?php if (!has_role('client')): ?><th>Client</th><?php endif; ?>
          <th>Priority</th>
          <th>Status</th>
          <th>Assigned To</th>
          <th>Created</th>
          <th class="text-end">Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
        <tr data-status="<?= $t->status ?>" data-priority="<?= $t->priority ?>">
          <td>
            <a href="<?= site_url('tickets/view/'.$t->id) ?>" class="fw-600 text-dark link-hover">
              <?= html_escape($t->title) ?>
            </a>
            <div class="small text-muted"><?= html_escape($t->ticket_number ?? '') ?></div>
          </td>
          <td><span class="text-muted small"><?= html_escape($t->project_name ?? '') ?></span></td>
          <?php if (!has_role('client')): ?>
          <td><span class="text-muted small"><?= html_escape($t->company_name ?? '') ?></span></td>
          <?php endif; ?>
          <td><?= priority_badge($t->priority) ?></td>
          <td><?= status_badge($t->status) ?></td>
          <td>
            <?php if ($t->assignee_first ?? null): ?>
            <div class="d-flex align-items-center gap-2">
              <?= user_avatar($t->assignee_first . ' ' . $t->assignee_last, null, 28) ?>
              <span class="small"><?= html_escape($t->assignee_first . ' ' . $t->assignee_last) ?></span>
            </div>
            <?php else: ?><span class="badge bg-secondary-soft text-secondary">Unassigned</span><?php endif; ?>
          </td>
          <td><span class="small text-muted"><?= time_ago($t->created_at) ?></span></td>
          <td class="text-end">
            <a href="<?= site_url('tickets/view/'.$t->id) ?>" class="btn btn-sm btn-ghost">
              <i class="bi bi-eye"></i>
            </a>
            <?php if (has_permission('can_manage_ticket')): ?>
            <a href="<?= site_url('tickets/delete/'.$t->id) ?>" class="btn btn-sm btn-ghost text-danger"
              onclick="return confirm('Delete this ticket?')">
              <i class="bi bi-trash"></i>
            </a>
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

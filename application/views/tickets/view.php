<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('tickets') ?>">Tickets</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= html_escape($ticket->ticket_number) ?></span>
    </div>
    <h1 class="page-title"><?= html_escape($ticket->title) ?></h1>
  </div>
  <div class="page-actions gap-2">
    <?= status_badge($ticket->status) ?>
    <?= priority_badge($ticket->priority) ?>
  </div>
</div>

<div class="row g-4">
  <!-- Main Content -->
  <div class="col-xl-8">
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-info-circle me-2 text-primary"></i>Ticket Details</h6>
      </div>
      <div class="card-body">
        <div class="detail-content mb-4">
          <h6 class="detail-label">Description</h6>
          <div class="detail-text"><?= nl2br(html_escape($ticket->description)) ?></div>
        </div>
        <?php if ($ticket->image): ?>
        <div class="ticket-image mb-4">
          <h6 class="detail-label">Attachment</h6>
          <a href="<?= base_url('uploads/'.$ticket->image) ?>" target="_blank">
            <img src="<?= base_url('uploads/'.$ticket->image) ?>" class="img-fluid rounded border" style="max-height:300px" alt="Ticket image">
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comments -->
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-chat-dots me-2 text-success"></i>Discussion</h6>
        <span class="badge bg-success-soft text-success"><?= count($comments) ?> replies</span>
      </div>
      <div class="card-body">
        <div class="comments-thread" id="commentsThread">
          <?php if (empty($comments)): ?>
          <div class="empty-state py-3"><i class="bi bi-chat-dots"></i><p>No replies yet</p></div>
          <?php else: foreach ($comments as $c): ?>
          <div class="comment-item <?= ($c->role_slug === 'client') ? 'comment-client' : 'comment-staff' ?>">
            <div class="comment-avatar">
              <?= user_avatar($c->first_name . ' ' . $c->last_name, $c->avatar ?? null, 36) ?>
            </div>
            <div class="comment-bubble">
              <div class="comment-header">
                <span class="comment-author"><?= html_escape($c->first_name . ' ' . $c->last_name) ?></span>
                <span class="comment-badge badge <?= $c->role_slug === 'client' ? 'bg-info-soft text-info' : 'bg-primary-soft text-primary' ?>">
                  <?= $c->role_slug === 'client' ? 'Client' : 'Staff' ?>
                </span>
                <span class="comment-time"><?= time_ago($c->created_at) ?></span>
              </div>
              <div class="comment-text"><?= nl2br(html_escape($c->comment)) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- Add Comment -->
        <div class="add-comment mt-4">
          <div class="d-flex gap-3">
            <?= user_avatar($current_user->first_name . ' ' . $current_user->last_name, $current_user->avatar, 36) ?>
            <div class="flex-1 w-100">
              <textarea class="form-control" id="newComment" rows="3" placeholder="Write a reply..."></textarea>
              <div class="mt-2 text-end">
                <button class="btn btn-primary" id="submitComment" data-ticket-id="<?= $ticket->id ?>">
                  <i class="bi bi-send me-1"></i> Send Reply
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-xl-4">
    <!-- Status Update -->
    <?php if (has_permission('can_manage_ticket')): ?>
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-gear me-2 text-warning"></i>Manage Ticket</h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-600">Status</label>
          <select class="form-select" id="ticketStatus">
            <?php foreach (['open','in_progress','resolved','closed'] as $s): ?>
            <option value="<?= $s ?>" <?= $ticket->status === $s ? 'selected' : '' ?>>
              <?= ucwords(str_replace('_',' ',$s)) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-600">Assign To</label>
          <select class="form-select" id="ticketAssignee">
            <option value="">Unassigned</option>
            <?php foreach ($users as $u): ?>
            <option value="<?= $u->id ?>" <?= $ticket->assigned_to == $u->id ? 'selected' : '' ?>>
              <?= html_escape($u->first_name . ' ' . $u->last_name) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-primary w-100" id="updateTicketStatus" data-ticket-id="<?= $ticket->id ?>">
          <i class="bi bi-check-lg me-1"></i> Update
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Ticket Info -->
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-info me-2"></i>Ticket Info</h6>
      </div>
      <div class="card-body">
        <div class="info-list">
          <div class="info-row">
            <span class="info-label">Ticket #</span>
            <span class="info-value"><?= html_escape($ticket->ticket_number) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Project</span>
            <span class="info-value"><?= html_escape($ticket->project_name) ?></span>
          </div>
          <?php if (!has_role('client')): ?>
          <div class="info-row">
            <span class="info-label">Client</span>
            <span class="info-value"><?= html_escape($ticket->company_name ?? '') ?></span>
          </div>
          <?php endif; ?>
          <div class="info-row">
            <span class="info-label">Priority</span>
            <span class="info-value"><?= priority_badge($ticket->priority) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value"><?= status_badge($ticket->status) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Submitted by</span>
            <span class="info-value"><?= html_escape($ticket->creator_first . ' ' . $ticket->creator_last) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Created</span>
            <span class="info-value small"><?= date('M d, Y H:i', strtotime($ticket->created_at)) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
const BASE_URL = '<?= base_url() ?>';

$('#submitComment').click(function() {
  const comment = $('#newComment').val().trim();
  const ticket_id = $(this).data('ticket-id');
  if (!comment) return;
  $.post(BASE_URL + 'tickets/add_comment', {
    ticket_id, comment, csrf_token: CSRF_TOKEN
  }, function(res) {
    if (res.success) {
      $('#newComment').val('');
      location.reload();
    }
  });
});

$('#updateTicketStatus').click(function() {
  const ticket_id = $(this).data('ticket-id');
  $.post(BASE_URL + 'tickets/update_status', {
    ticket_id,
    status: $('#ticketStatus').val(),
    assigned_to: $('#ticketAssignee').val(),
    csrf_token: CSRF_TOKEN
  }, function(res) {
    if (res.success) location.reload();
  });
});
</script>

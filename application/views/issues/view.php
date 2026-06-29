<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('issues') ?>">Issues</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= html_escape($issue->issue_number) ?></span>
    </div>
    <h1 class="page-title"><?= html_escape($issue->title) ?></h1>
  </div>
  <div class="page-actions gap-2">
    <span class="badge severity-<?= $issue->severity ?>"><?= ucfirst($issue->severity) ?></span>
    <?= priority_badge($issue->priority) ?>
    <?= status_badge($issue->status) ?>
  </div>
</div>

<div class="row g-4">
  <!-- Main Content -->
  <div class="col-xl-8">
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-bug me-2 text-danger"></i>Issue Details</h6>
      </div>
      <div class="card-body">
        <?php if ($issue->description): ?>
        <div class="mb-4">
          <div class="detail-label">Description</div>
          <div class="detail-text"><?= nl2br(html_escape($issue->description)) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($issue->steps_to_reproduce): ?>
        <div class="mb-4">
          <div class="detail-label">Steps to Reproduce</div>
          <div class="detail-text"><?= nl2br(html_escape($issue->steps_to_reproduce)) ?></div>
        </div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
          <?php if ($issue->expected_result): ?>
          <div class="col-md-6">
            <div class="detail-label">Expected Result</div>
            <div class="p-3 rounded border border-success bg-success-soft">
              <div class="detail-text small"><?= nl2br(html_escape($issue->expected_result)) ?></div>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($issue->actual_result): ?>
          <div class="col-md-6">
            <div class="detail-label">Actual Result</div>
            <div class="p-3 rounded border border-danger bg-danger-soft">
              <div class="detail-text small"><?= nl2br(html_escape($issue->actual_result)) ?></div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <?php if ($issue->screenshot): ?>
        <div class="mb-4">
          <div class="detail-label">Screenshot</div>
          <a href="<?= base_url('uploads/'.$issue->screenshot) ?>" target="_blank">
            <img src="<?= base_url('uploads/'.$issue->screenshot) ?>" class="img-fluid rounded border" style="max-height:400px" alt="Screenshot">
          </a>
        </div>
        <?php endif; ?>

        <?php if ($issue->tags): ?>
        <div>
          <div class="detail-label">Tags</div>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach (explode(',', $issue->tags) as $tag): ?>
            <span class="badge bg-danger-soft text-danger"><?= html_escape(trim($tag)) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Comments -->
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-chat-dots me-2"></i>Discussion</h6>
      </div>
      <div class="card-body">
        <div class="comments-thread mb-4">
          <?php if (empty($comments)): ?>
          <div class="empty-state py-3"><i class="bi bi-chat-dots"></i><p>No comments yet</p></div>
          <?php else: foreach ($comments as $c): ?>
          <div class="comment-item">
            <div class="comment-avatar"><?= user_avatar($c->first_name . ' ' . $c->last_name, $c->avatar ?? null, 36) ?></div>
            <div class="comment-bubble">
              <div class="comment-header">
                <span class="comment-author"><?= html_escape($c->first_name . ' ' . $c->last_name) ?></span>
                <span class="comment-time"><?= time_ago($c->created_at) ?></span>
              </div>
              <div class="comment-text"><?= nl2br(html_escape($c->comment)) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
        <div class="d-flex gap-3">
          <?= user_avatar($current_user->first_name . ' ' . $current_user->last_name, $current_user->avatar, 36) ?>
          <div class="flex-1 w-100">
            <textarea class="form-control" id="issueComment" rows="3" placeholder="Add a comment..."></textarea>
            <div class="mt-2 text-end">
              <button class="btn btn-primary btn-sm" id="submitIssueComment" data-issue-id="<?= $issue->id ?>">
                <i class="bi bi-send me-1"></i> Comment
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="col-xl-4">
    <!-- Status Management -->
    <?php if (has_permission('can_raise_issue') || has_permission('can_test_issue') || has_role(['admin','project_manager'])): ?>
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-gear me-2 text-warning"></i>Manage Issue</h6>
      </div>
      <div class="card-body">
        <label class="form-label fw-600">Change Status</label>
        <select class="form-select mb-3" id="issueStatusSelect">
          <?php
          $workflow = ['open','in_progress','fixed','retesting','closed','reopened'];
          foreach ($workflow as $s):
          ?>
          <option value="<?= $s ?>" <?= $issue->status === $s ? 'selected' : '' ?>>
            <?= ucwords(str_replace('_',' ',$s)) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button class="btn btn-primary w-100" id="updateIssueStatus" data-issue-id="<?= $issue->id ?>">
          <i class="bi bi-check-lg me-1"></i> Update Status
        </button>

        <!-- Workflow Guide -->
        <div class="mt-3 p-3 rounded" style="background:var(--surface-2);border:1px solid var(--border)">
          <div class="small fw-600 mb-2 text-muted">Issue Workflow</div>
          <div class="small text-muted">
            Open → In Progress → Fixed → Retesting → Closed<br>
            <span class="text-danger">If retest fails: Reopened</span>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Issue Info -->
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-info me-2"></i>Issue Info</h6>
      </div>
      <div class="card-body">
        <div class="info-list">
          <div class="info-row">
            <span class="info-label">Issue #</span>
            <span class="info-value"><?= html_escape($issue->issue_number) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Project</span>
            <span class="info-value small"><?= html_escape($issue->project_name) ?></span>
          </div>
          <?php if ($issue->task_title): ?>
          <div class="info-row">
            <span class="info-label">Related Task</span>
            <span class="info-value small"><?= html_escape($issue->task_title) ?></span>
          </div>
          <?php endif; ?>
          <div class="info-row"><span class="info-label">Priority</span><?= priority_badge($issue->priority) ?></div>
          <div class="info-row"><span class="info-label">Severity</span>
            <span class="badge severity-<?= $issue->severity ?>"><?= ucfirst($issue->severity) ?></span>
          </div>
          <div class="info-row">
            <span class="info-label">Reported By</span>
            <div class="d-flex align-items-center gap-2">
              <?= user_avatar($issue->reporter_first . ' ' . $issue->reporter_last, $issue->reporter_avatar ?? null, 24) ?>
              <span class="small"><?= html_escape($issue->reporter_first . ' ' . $issue->reporter_last) ?></span>
            </div>
          </div>
          <div class="info-row">
            <span class="info-label">Assigned To</span>
            <?php if ($issue->assignee_first): ?>
            <div class="d-flex align-items-center gap-2">
              <?= user_avatar($issue->assignee_first . ' ' . $issue->assignee_last, $issue->assignee_avatar ?? null, 24) ?>
              <span class="small"><?= html_escape($issue->assignee_first . ' ' . $issue->assignee_last) ?></span>
            </div>
            <?php else: ?><span class="text-muted small">Unassigned</span><?php endif; ?>
          </div>
          <div class="info-row">
            <span class="info-label">Reported</span>
            <span class="small text-muted"><?= date('M d, Y', strtotime($issue->created_at)) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
const BASE_URL = '<?= base_url() ?>';
</script>

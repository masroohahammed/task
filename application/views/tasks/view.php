<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('projects') ?>">Projects</a>
      <i class="bi bi-chevron-right"></i>
      <a href="<?= site_url('projects/kanban/'.$project->id) ?>"><?= html_escape($project->name) ?></a>
      <i class="bi bi-chevron-right"></i>
      <span><?= html_escape($task->title) ?></span>
    </div>
    <h1 class="page-title"><?= html_escape($task->title) ?></h1>
  </div>
  <div class="page-actions gap-2">
    <?= status_badge($task->status) ?>
    <?= priority_badge($task->priority) ?>
    <?php if (has_permission('can_raise_issue')): ?>
    <a href="<?= site_url('issues/create/'.$task->project_id) ?>" class="btn btn-outline-danger btn-sm">
      <i class="bi bi-bug me-1"></i> Report Issue
    </a>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4">
  <!-- Main -->
  <div class="col-xl-8">

    <!-- Task Details -->
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-info-circle me-2 text-primary"></i>Task Details</h6>
      </div>
      <div class="card-body">
        <div class="mb-4">
          <div class="detail-label">Description</div>
          <div class="detail-text"><?= nl2br(html_escape($task->description ?? 'No description provided.')) ?></div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-4">
          <div class="d-flex justify-content-between mb-2">
            <div class="detail-label">Progress</div>
            <span class="fw-700 text-primary"><?= $task->progress ?>%</span>
          </div>
          <div class="progress" style="height:10px;border-radius:8px">
            <div class="progress-bar bg-primary" style="width:<?= $task->progress ?>%;border-radius:8px"></div>
          </div>
        </div>

        <!-- Tags -->
        <?php if ($task->tags): ?>
        <div>
          <div class="detail-label">Tags</div>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach (explode(',', $task->tags) as $tag): ?>
            <span class="badge bg-primary-soft text-primary"><?= html_escape(trim($tag)) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Daily Update -->
    <?php if (has_permission('can_update_task') &&
      ($task->assigned_to == $current_user->id || has_role(['admin','project_manager']))): ?>
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-calendar-check me-2 text-success"></i>Daily Update</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-600">Work Done Today <span class="text-danger">*</span></label>
            <textarea class="form-control" id="workDone" rows="3" placeholder="Describe what you worked on today..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-600">Progress: <span id="progressValue" class="text-primary"><?= $task->progress ?>%</span></label>
            <input type="range" class="form-range" id="progressRange" min="0" max="100" step="5" value="<?= $task->progress ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label fw-600">Blockers / Issues</label>
            <textarea class="form-control" id="blockers" rows="2" placeholder="Any blockers or issues?"></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-600">Hours Spent</label>
            <input type="number" class="form-control" id="hoursSpent" step="0.5" min="0" placeholder="e.g. 3.5">
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-success" id="saveDailyUpdate" data-task-id="<?= $task->id ?>">
              <i class="bi bi-check-lg me-1"></i> Submit Update
            </button>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Update History -->
    <?php if (!empty($updates)): ?>
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-teal"></i>Update History</h6>
      </div>
      <div class="card-body p-0">
        <?php foreach ($updates as $u): ?>
        <div class="list-item">
          <?= user_avatar($u->first_name . ' ' . $u->last_name, $u->avatar ?? null, 36) ?>
          <div class="flex-1">
            <div class="fw-600 small"><?= html_escape($u->first_name . ' ' . $u->last_name) ?>
              <span class="text-muted fw-400">— <?= date('M d, Y', strtotime($u->update_date)) ?></span>
            </div>
            <div class="small mt-1"><?= nl2br(html_escape($u->work_done)) ?></div>
            <?php if ($u->blockers): ?>
            <div class="small text-danger mt-1"><i class="bi bi-exclamation-triangle me-1"></i><?= html_escape($u->blockers) ?></div>
            <?php endif; ?>
            <div class="d-flex gap-3 mt-1">
              <span class="small text-primary fw-600"><?= $u->progress ?>% progress</span>
              <?php if ($u->hours_spent): ?>
              <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= $u->hours_spent ?>h</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="list-item-time"><?= time_ago($u->created_at) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Comments -->
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-chat-dots me-2 text-success"></i>Comments</h6>
        <span class="badge bg-success-soft text-success"><?= count($comments) ?></span>
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
            <textarea class="form-control" id="taskComment" rows="3" placeholder="Add a comment..."></textarea>
            <div class="mt-2 text-end">
              <button class="btn btn-primary btn-sm" id="submitTaskComment" data-task-id="<?= $task->id ?>">
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
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-info me-2"></i>Task Info</h6>
      </div>
      <div class="card-body">
        <div class="info-list">
          <div class="info-row"><span class="info-label">Project</span>
            <a href="<?= site_url('projects/view/'.$project->id) ?>" class="info-value small"><?= html_escape($project->name) ?></a>
          </div>
          <div class="info-row"><span class="info-label">Assigned To</span>
            <div class="d-flex align-items-center gap-2">
              <?php if ($task->assignee_first): ?>
              <?= user_avatar($task->assignee_first . ' ' . $task->assignee_last, $task->assignee_avatar ?? null, 26) ?>
              <span class="small fw-600"><?= html_escape($task->assignee_first . ' ' . $task->assignee_last) ?></span>
              <?php else: ?><span class="text-muted small">Unassigned</span><?php endif; ?>
            </div>
          </div>
          <div class="info-row"><span class="info-label">Priority</span><?= priority_badge($task->priority) ?></div>
          <div class="info-row"><span class="info-label">Status</span><?= status_badge($task->status) ?></div>
          <div class="info-row"><span class="info-label">Deadline</span>
            <span class="small <?= ($task->deadline && strtotime($task->deadline) < time()) ? 'text-danger fw-600' : '' ?>">
              <?= $task->deadline ? date('M d, Y', strtotime($task->deadline)) : '—' ?>
            </span>
          </div>
          <div class="info-row"><span class="info-label">Created by</span>
            <span class="small"><?= html_escape($task->creator_first . ' ' . $task->creator_last) ?></span>
          </div>
          <div class="info-row"><span class="info-label">Created</span>
            <span class="small text-muted"><?= date('M d, Y', strtotime($task->created_at)) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Files -->
    <?php if (!empty($files)): ?>
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-paperclip me-2"></i>Attachments</h6>
      </div>
      <div class="card-body p-0">
        <?php foreach ($files as $f): ?>
        <a href="<?= base_url('uploads/' . $f->file_path) ?>" target="_blank" class="list-item">
          <div class="list-item-icon bg-primary-soft text-primary"><i class="bi bi-file-earmark"></i></div>
          <div class="list-item-body">
            <div class="list-item-title"><?= html_escape($f->file_name) ?></div>
            <div class="small text-muted"><?= round($f->file_size / 1024) ?>KB</div>
          </div>
          <i class="bi bi-download text-muted"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
const CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
const BASE_URL = '<?= base_url() ?>';
</script>

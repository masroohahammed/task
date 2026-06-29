<div class="kanban-card" 
  data-task-id="<?= $task->id ?>"
  data-assignee="<?= $task->assigned_to ?>"
  data-priority="<?= $task->priority ?>"
  draggable="true">
  
  <div class="card-priority-bar priority-<?= $task->priority ?>"></div>
  
  <div class="card-body-inner">
    <!-- Tags -->
    <?php if ($task->tags): ?>
    <div class="card-tags">
      <?php foreach (explode(',', $task->tags) as $tag): ?>
      <span class="card-tag"><?= html_escape(trim($tag)) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Title -->
    <div class="card-title-text">
      <a href="#" class="task-detail-link" data-task-id="<?= $task->id ?>"><?= html_escape($task->title) ?></a>
    </div>

    <!-- Meta -->
    <div class="card-meta">
      <?= priority_badge($task->priority) ?>
      <?php if ($task->deadline): ?>
      <span class="card-deadline <?= strtotime($task->deadline) < time() ? 'overdue' : '' ?>">
        <i class="bi bi-calendar3"></i> <?= date('M d', strtotime($task->deadline)) ?>
      </span>
      <?php endif; ?>
    </div>

    <!-- Progress -->
    <?php if ($task->progress > 0): ?>
    <div class="card-progress mt-2">
      <div class="progress" style="height:4px">
        <div class="progress-bar bg-primary" style="width:<?= $task->progress ?>%"></div>
      </div>
      <span class="progress-label"><?= $task->progress ?>%</span>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="card-footer-inner">
      <div class="card-assignee">
        <?php if ($task->first_name): ?>
        <?= user_avatar($task->first_name . ' ' . $task->last_name, $task->avatar, 24) ?>
        <span class="small text-muted ms-1"><?= html_escape($task->first_name) ?></span>
        <?php else: ?>
        <span class="small text-muted"><i class="bi bi-person-dash"></i> Unassigned</span>
        <?php endif; ?>
      </div>
      <div class="card-actions">
        <a href="<?= site_url('tasks/view/'.$task->id) ?>" class="card-action-btn" title="View">
          <i class="bi bi-eye"></i>
        </a>
        <?php if (has_permission('can_raise_issue')): ?>
        <a href="<?= site_url('issues/create/'.$task->project_id) ?>" class="card-action-btn text-danger" title="Report Issue">
          <i class="bi bi-bug"></i>
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

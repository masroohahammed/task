<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('projects') ?>">Projects</a>
      <i class="bi bi-chevron-right"></i>
      <a href="<?= site_url('projects/view/'.$project->id) ?>"><?= html_escape($project->name) ?></a>
      <i class="bi bi-chevron-right"></i>
      <span>Kanban</span>
    </div>
    <h1 class="page-title">Task Board</h1>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('issues/kanban/'.$project->id) ?>" class="btn btn-outline-danger">
      <i class="bi bi-bug me-1"></i> Issue Board
    </a>
    <?php if (has_permission('can_create_task')): ?>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
      <i class="bi bi-plus-lg me-1"></i> Add Task
    </button>
    <?php endif; ?>
  </div>
</div>

<!-- Kanban Filters -->
<div class="kanban-filters mb-3">
  <div class="filter-group">
    <label class="filter-label">Assignee</label>
    <select class="form-select form-select-sm" id="filterAssignee" style="width:160px">
      <option value="">All Members</option>
      <?php foreach ($members as $m): ?>
      <option value="<?= $m->user_id ?>"><?= html_escape($m->first_name . ' ' . $m->last_name) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
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
    <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Search tasks..." style="width:200px">
  </div>
</div>

<!-- Kanban Board -->
<div class="kanban-board" id="kanbanBoard">

  <!-- TO DO -->
  <div class="kanban-col" data-status="todo">
    <div class="kanban-col-header">
      <div class="kanban-col-title">
        <span class="kanban-col-dot dot-secondary"></span>
        <span>To Do</span>
        <span class="kanban-col-count" id="count-todo"><?= count($tasks_todo) ?></span>
      </div>
      <?php if (has_permission('can_create_task')): ?>
      <button class="btn-add-card" data-status="todo" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="bi bi-plus"></i>
      </button>
      <?php endif; ?>
    </div>
    <div class="kanban-cards sortable-list" id="col-todo" data-status="todo">
      <?php foreach ($tasks_todo as $task): ?>
      <?php include('_task_card.php'); ?>
      <?php endforeach; ?>
      <div class="kanban-drop-placeholder">Drop here</div>
    </div>
  </div>

  <!-- IN PROGRESS -->
  <div class="kanban-col" data-status="in_progress">
    <div class="kanban-col-header">
      <div class="kanban-col-title">
        <span class="kanban-col-dot dot-primary"></span>
        <span>In Progress</span>
        <span class="kanban-col-count" id="count-in_progress"><?= count($tasks_prog) ?></span>
      </div>
      <?php if (has_permission('can_create_task')): ?>
      <button class="btn-add-card" data-status="in_progress" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="bi bi-plus"></i>
      </button>
      <?php endif; ?>
    </div>
    <div class="kanban-cards sortable-list" id="col-in_progress" data-status="in_progress">
      <?php foreach ($tasks_prog as $task): ?>
      <?php include('_task_card.php'); ?>
      <?php endforeach; ?>
      <div class="kanban-drop-placeholder">Drop here</div>
    </div>
  </div>

  <!-- TESTING -->
  <div class="kanban-col" data-status="testing">
    <div class="kanban-col-header">
      <div class="kanban-col-title">
        <span class="kanban-col-dot dot-info"></span>
        <span>Testing</span>
        <span class="kanban-col-count" id="count-testing"><?= count($tasks_test) ?></span>
      </div>
      <?php if (has_permission('can_create_task')): ?>
      <button class="btn-add-card" data-status="testing" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="bi bi-plus"></i>
      </button>
      <?php endif; ?>
    </div>
    <div class="kanban-cards sortable-list" id="col-testing" data-status="testing">
      <?php foreach ($tasks_test as $task): ?>
      <?php include('_task_card.php'); ?>
      <?php endforeach; ?>
      <div class="kanban-drop-placeholder">Drop here</div>
    </div>
  </div>

  <!-- DONE -->
  <div class="kanban-col" data-status="done">
    <div class="kanban-col-header">
      <div class="kanban-col-title">
        <span class="kanban-col-dot dot-success"></span>
        <span>Done</span>
        <span class="kanban-col-count" id="count-done"><?= count($tasks_done) ?></span>
      </div>
    </div>
    <div class="kanban-cards sortable-list" id="col-done" data-status="done">
      <?php foreach ($tasks_done as $task): ?>
      <?php include('_task_card.php'); ?>
      <?php endforeach; ?>
      <div class="kanban-drop-placeholder">Drop here</div>
    </div>
  </div>

</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-modern">
      <div class="modal-header modal-header-modern">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-primary"></i>Create Task</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <form id="createTaskForm">
          <?= form_hidden('csrf_token', $this->security->get_csrf_hash()) ?>
          <input type="hidden" name="project_id" value="<?= $project->id ?>">
          <input type="hidden" name="status" id="newTaskStatus" value="todo">

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-600">Task Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" placeholder="Enter task title..." required>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Description</label>
              <textarea class="form-control" name="description" rows="3" placeholder="Describe the task..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Assigned To</label>
              <select class="form-select" name="assigned_to">
                <option value="">Unassigned</option>
                <?php foreach ($members as $m): ?>
                <option value="<?= $m->user_id ?>"><?= html_escape($m->first_name . ' ' . $m->last_name) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Priority</label>
              <select class="form-select" name="priority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Deadline</label>
              <input type="date" class="form-control" name="deadline">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Estimated Hours</label>
              <input type="number" class="form-control" name="estimated_hours" step="0.5" min="0">
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Tags</label>
              <input type="text" class="form-control" name="tags" placeholder="e.g. frontend, api, design">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer modal-footer-modern">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveTask">
          <i class="bi bi-check-lg me-1"></i> Create Task
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Task Detail Modal -->
<div class="modal fade" id="taskDetailModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content modal-modern">
      <div class="modal-header modal-header-modern">
        <h5 class="modal-title" id="taskDetailTitle">Task Details</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body" id="taskDetailBody">
        <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
      </div>
    </div>
  </div>
</div>

<script>
const PROJECT_ID = <?= $project->id ?>;
const CSRF_TOKEN = '<?= $this->security->get_csrf_hash() ?>';
const BASE_URL = '<?= base_url() ?>';
</script>

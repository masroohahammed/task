<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('issues') ?>">Issues</a>
      <i class="bi bi-chevron-right"></i>
      <span>Report Issue</span>
    </div>
    <h1 class="page-title">Report New Issue</h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-9">
    <form action="<?= site_url('issues/store') ?>" method="post" enctype="multipart/form-data">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <div class="form-card">
        <div class="form-card-header">
          <h6><i class="bi bi-bug me-2 text-danger"></i>Issue Information</h6>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Project <span class="text-danger">*</span></label>
              <select class="form-select" name="project_id" required id="projectSelect">
                <option value="">Select Project</option>
                <?php foreach ($projects as $p): ?>
                <option value="<?= $p->id ?>" <?= ($project_id == $p->id) ? 'selected' : '' ?>><?= html_escape($p->name) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Related Task (Optional)</label>
              <select class="form-select" name="task_id" id="taskSelect">
                <option value="">None</option>
                <?php foreach ($tasks as $t): ?>
                <option value="<?= $t->id ?>"><?= html_escape($t->title) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Issue Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" required placeholder="Brief summary of the issue...">
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Description</label>
              <textarea class="form-control" name="description" rows="3" placeholder="Detailed description of the issue..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Steps to Reproduce</label>
              <textarea class="form-control" name="steps_to_reproduce" rows="4" placeholder="1. Go to...&#10;2. Click on...&#10;3. Observe..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Expected Result</label>
              <textarea class="form-control" name="expected_result" rows="3" placeholder="What should happen?"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Actual Result</label>
              <textarea class="form-control" name="actual_result" rows="3" placeholder="What actually happened?"></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">Priority</label>
              <select class="form-select" name="priority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">Severity</label>
              <select class="form-select" name="severity">
                <option value="minor" selected>Minor</option>
                <option value="major">Major</option>
                <option value="blocker">Blocker</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">Assign To</label>
              <select class="form-select" name="assigned_to">
                <option value="">Unassigned</option>
                <?php foreach ($users as $u): ?>
                <option value="<?= $u->id ?>"><?= html_escape($u->first_name . ' ' . $u->last_name) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Tags</label>
              <input type="text" class="form-control" name="tags" placeholder="e.g. UI, Backend, API">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Screenshot</label>
              <input type="file" class="form-control" name="screenshot" accept="image/*">
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions mt-4">
        <a href="<?= site_url('issues') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-danger">
          <i class="bi bi-bug me-1"></i> Report Issue
        </button>
      </div>
    </form>
  </div>
</div>

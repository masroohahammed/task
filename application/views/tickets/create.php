<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('tickets') ?>">Tickets</a>
      <i class="bi bi-chevron-right"></i>
      <span>New Ticket</span>
    </div>
    <h1 class="page-title">Create Support Ticket</h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <form action="<?= site_url('tickets/store') ?>" method="post" enctype="multipart/form-data">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <div class="form-card">
        <div class="form-card-header">
          <h6><i class="bi bi-ticket-detailed me-2 text-warning"></i>Ticket Information</h6>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-600">Project <span class="text-danger">*</span></label>
              <select class="form-select" name="project_id" required>
                <option value="">Select project...</option>
                <?php foreach ($projects as $p): ?>
                <option value="<?= $p->id ?>"><?= html_escape($p->name) ?></option>
                <?php endforeach; ?>
              </select>
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
            <div class="col-12">
              <label class="form-label fw-600">Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="title" required placeholder="Brief description of your issue...">
              <?= form_error('title','<div class="text-danger small">','</div>') ?>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Description</label>
              <textarea class="form-control" name="description" rows="5"
                placeholder="Please describe your issue in detail. Include any relevant steps, errors, or expected behavior..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Screenshot / Attachment</label>
              <input type="file" class="form-control" name="image" accept="image/*">
              <div class="form-text">Supported formats: JPG, PNG, GIF. Max 5MB.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-actions mt-4">
        <a href="<?= site_url('tickets') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-warning text-dark fw-600">
          <i class="bi bi-ticket-detailed me-1"></i> Submit Ticket
        </button>
      </div>
    </form>
  </div>
</div>

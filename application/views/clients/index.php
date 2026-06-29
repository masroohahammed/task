<div class="page-header">
  <div>
    <h1 class="page-title">Clients</h1>
    <p class="page-subtitle">Manage client accounts and their projects</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('clients/create') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Add Client
    </a>
  </div>
</div>

<div class="row g-4">
  <?php if (empty($clients)): ?>
  <div class="col-12">
    <div class="empty-page-state">
      <div class="empty-icon"><i class="bi bi-buildings"></i></div>
      <h4>No clients yet</h4>
      <p>Add your first client to get started</p>
      <a href="<?= site_url('clients/create') ?>" class="btn btn-primary">Add Client</a>
    </div>
  </div>
  <?php else: foreach ($clients as $c): ?>
  <div class="col-xl-4 col-md-6">
    <div class="card card-modern h-100">
      <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3 mb-3">
          <div style="width:46px;height:46px;background:linear-gradient(135deg,var(--primary),var(--purple));
            border-radius:12px;display:flex;align-items:center;justify-content:center;
            color:#fff;font-size:1.2rem;font-weight:800;flex-shrink:0;">
            <?= strtoupper(substr($c->company_name, 0, 1)) ?>
          </div>
          <div class="flex-1">
            <h6 class="mb-0 fw-700">
              <a href="<?= site_url('clients/view/'.$c->id) ?>" class="text-dark link-hover">
                <?= html_escape($c->company_name) ?>
              </a>
            </h6>
            <div class="small text-muted"><?= html_escape($c->contact_person) ?></div>
          </div>
          <div class="dropdown">
            <button class="btn btn-icon btn-sm" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= site_url('clients/view/'.$c->id) ?>"><i class="bi bi-eye me-2"></i>View</a></li>
              <li><a class="dropdown-item" href="<?= site_url('clients/edit/'.$c->id) ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= site_url('clients/delete/'.$c->id) ?>"
                onclick="return confirm('Delete this client?')"><i class="bi bi-trash me-2"></i>Delete</a></li>
            </ul>
          </div>
        </div>

        <div class="d-flex flex-column gap-2 mb-3">
          <div class="d-flex align-items-center gap-2 small text-muted">
            <i class="bi bi-envelope"></i> <?= html_escape($c->email) ?>
          </div>
          <?php if ($c->phone): ?>
          <div class="d-flex align-items-center gap-2 small text-muted">
            <i class="bi bi-telephone"></i> <?= html_escape($c->phone) ?>
          </div>
          <?php endif; ?>
        </div>

        <div class="d-flex gap-3 pt-3" style="border-top:1px solid var(--border-light)">
          <div class="text-center">
            <div class="fw-700 text-primary"><?= $c->project_count ?></div>
            <div class="small text-muted">Projects</div>
          </div>
          <div class="text-center">
            <div class="fw-700 text-warning"><?= $c->ticket_count ?></div>
            <div class="small text-muted">Tickets</div>
          </div>
          <div class="ms-auto">
            <a href="<?= site_url('clients/view/'.$c->id) ?>" class="btn btn-sm btn-ghost">View <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

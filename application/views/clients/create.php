<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('clients') ?>">Clients</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= isset($client) ? 'Edit' : 'New Client' ?></span>
    </div>
    <h1 class="page-title"><?= isset($client) ? 'Edit Client' : 'Add New Client' ?></h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-8">
    <?= validation_errors('<div class="alert alert-danger mb-3">','</div>') ?>

    <form action="<?= isset($client) ? site_url('clients/update/'.$client->id) : site_url('clients/store') ?>" method="post">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <!-- Company Info -->
      <div class="form-card mb-4">
        <div class="form-card-header">
          <h6><i class="bi bi-buildings me-2 text-primary"></i>Company Information</h6>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-600">Company Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="company_name" required
                value="<?= html_escape(isset($client) ? $client->company_name : set_value('company_name')) ?>"
                placeholder="e.g. Acme Corporation">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Contact Person <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="contact_person" required
                value="<?= html_escape(isset($client) ? $client->contact_person : set_value('contact_person')) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required
                value="<?= html_escape(isset($client) ? $client->email : set_value('email')) ?>"
                <?= isset($client) ? 'readonly' : '' ?>>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Phone</label>
              <input type="text" class="form-control" name="phone"
                value="<?= html_escape(isset($client) ? ($client->phone ?? '') : '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Website</label>
              <input type="url" class="form-control" name="website"
                placeholder="https://example.com"
                value="<?= html_escape(isset($client) ? ($client->website ?? '') : '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Address</label>
              <textarea class="form-control" name="address" rows="2"><?= html_escape(isset($client) ? ($client->address ?? '') : '') ?></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Notes</label>
              <textarea class="form-control" name="notes" rows="2"
                placeholder="Internal notes about this client..."><?= html_escape(isset($client) ? ($client->notes ?? '') : '') ?></textarea>
            </div>
            <?php if (isset($timezones)): ?>
            <div class="col-md-6">
              <label class="form-label fw-600">Default timezone for new portal users</label>
              <input type="text" class="form-control" name="default_timezone" maxlength="64"
                placeholder="IANA e.g. Asia/Dubai"
                value="<?= html_escape(isset($client) ? ($client->default_timezone ?? '') : '') ?>">
              <div class="form-text">Applied to client logins you create for this company.</div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!isset($client)): ?>
      <!-- Portal Login -->
      <div class="form-card mb-4">
        <div class="form-card-header">
          <h6><i class="bi bi-shield-lock me-2 text-success"></i>Client Portal Login</h6>
          <span class="small text-muted">Auto-creates a login using the email above</span>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-12">
              <div class="d-flex align-items-center gap-3 p-3 rounded" style="background:var(--primary-soft);border:1px solid rgba(25,73,153,.2)">
                <i class="bi bi-info-circle text-primary fs-5"></i>
                <div class="small">
                  A portal user will be created with the <strong>company email</strong> above.<br>
                  After creating, you can add more users from the client details page.
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Portal Password <span class="text-muted fw-400">(optional)</span></label>
              <input type="password" class="form-control" name="password" placeholder="Leave blank to skip login creation">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="small text-muted pb-2">
                <i class="bi bi-lightbulb me-1"></i>
                Leave blank if client doesn't need portal access yet.
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="form-actions">
        <a href="<?= site_url('clients') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>
          <?= isset($client) ? 'Update Client' : 'Create Client' ?>
        </button>
      </div>
    </form>
  </div>
</div>

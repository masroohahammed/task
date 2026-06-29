<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('users') ?>">Users</a>
      <i class="bi bi-chevron-right"></i>
      <span>Permissions: <?= html_escape($user->first_name . ' ' . $user->last_name) ?></span>
    </div>
    <h1 class="page-title">Manage Permissions</h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-7">
    <div class="card card-modern mb-4" style="border-color:var(--primary)">
      <div class="card-body d-flex align-items-center gap-4 p-4">
        <?= user_avatar($user->first_name . ' ' . $user->last_name, $user->avatar ?? null, 56) ?>
        <div>
          <h5 class="mb-0 fw-800"><?= html_escape($user->first_name . ' ' . $user->last_name) ?></h5>
          <div class="text-muted small"><?= html_escape($user->email) ?></div>
          <span class="badge bg-primary mt-1"><?= html_escape($user->role_name) ?></span>
        </div>
      </div>
    </div>

    <form action="<?= site_url('users/save_permissions') ?>" method="post">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
      <input type="hidden" name="user_id" value="<?= $user->id ?>">

      <div class="form-card">
        <div class="form-card-header">
          <h6><i class="bi bi-shield-check me-2 text-primary"></i>Permissions</h6>
          <span class="small text-muted">Override default role permissions</span>
        </div>
        <div class="form-card-body">
          <?php foreach ($permissions as $p): ?>
          <div class="permission-row">
            <label class="permission-label">
              <input type="checkbox" name="permissions[]" value="<?= $p->id ?>"
                <?= in_array($p->slug, $user_perms) ? 'checked' : '' ?>>
              <div class="perm-info">
                <div class="perm-name"><?= html_escape($p->name) ?></div>
                <div class="perm-slug"><code><?= html_escape($p->slug) ?></code></div>
                <div class="perm-desc small text-muted"><?= html_escape($p->description) ?></div>
              </div>
              <div class="perm-toggle">
                <div class="toggle-track">
                  <div class="toggle-thumb"></div>
                </div>
              </div>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-actions mt-4">
        <a href="<?= site_url('users') ?>" class="btn btn-ghost">Back to Users</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-shield-check me-1"></i> Save Permissions
        </button>
      </div>
    </form>
  </div>
</div>

<style>
.permission-row { border-bottom: 1px solid var(--border-light); }
.permission-row:last-child { border-bottom: none; }
.permission-label {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 0; cursor: pointer;
}
.permission-label input { display: none; }
.perm-info { flex: 1; }
.perm-name { font-weight: 600; font-size: 0.87rem; }
.perm-slug { margin: 2px 0; }
.perm-slug code { font-size: 0.75rem; background: var(--bg); padding: 2px 6px; border-radius: 4px; }

.toggle-track {
  width: 44px; height: 24px;
  background: var(--border); border-radius: 12px;
  position: relative; transition: background var(--transition);
}
.toggle-thumb {
  width: 18px; height: 18px;
  background: #fff; border-radius: 50%;
  position: absolute; top: 3px; left: 3px;
  transition: transform var(--transition);
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.permission-label input:checked ~ .perm-toggle .toggle-track { background: var(--primary); }
.permission-label input:checked ~ .perm-toggle .toggle-thumb { transform: translateX(20px); }
</style>

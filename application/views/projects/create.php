<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('projects') ?>">Projects</a>
      <i class="bi bi-chevron-right"></i>
      <span><?= isset($project) ? 'Edit' : 'New Project' ?></span>
    </div>
    <h1 class="page-title"><?= isset($project) ? 'Edit Project' : 'Create New Project' ?></h1>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-9">
    <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3">
      <?= $this->session->flashdata('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?= validation_errors('<div class="alert alert-danger mb-3">','</div>') ?>

    <form action="<?= isset($project) ? site_url('projects/update/'.$project->id) : site_url('projects/store') ?>" method="post" id="projectForm">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <!-- Project Info -->
      <div class="form-card mb-4">
        <div class="form-card-header">
          <h6><i class="bi bi-folder2 me-2 text-primary"></i>Project Information</h6>
        </div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-600">Project Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" required
                value="<?= html_escape(isset($project) ? $project->name : set_value('name')) ?>"
                placeholder="Enter project name...">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">Client</label>
              <select class="form-select" name="client_id">
                <option value="">Internal Project</option>
                <?php foreach ($clients as $c): ?>
                <option value="<?= $c->id ?>" <?= (isset($project) && $project->client_id == $c->id) ? 'selected' : '' ?>>
                  <?= html_escape($c->company_name) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Description</label>
              <textarea class="form-control" name="description" rows="3"
                placeholder="Describe the project scope and goals..."><?= html_escape(isset($project) ? ($project->description ?? '') : '') ?></textarea>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-600">Start Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="start_date" required
                value="<?= isset($project) ? $project->start_date : '' ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-600">End Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="end_date" required
                value="<?= isset($project) ? $project->end_date : '' ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-600">Status</label>
              <select class="form-select" name="status">
                <?php foreach (['planning','active','on_hold','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= (isset($project) && $project->status===$s)?'selected':($s==='planning'?'selected':'') ?>>
                  <?= ucwords(str_replace('_',' ',$s)) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-600">Priority</label>
              <select class="form-select" name="priority">
                <?php foreach (['low','medium','high','critical'] as $p): ?>
                <option value="<?= $p ?>" <?= (isset($project) && $project->priority===$p)?'selected':($p==='medium'?'selected':'') ?>>
                  <?= ucfirst($p) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Members -->
      <div class="form-card mb-4">
        <div class="form-card-header">
          <h6><i class="bi bi-people me-2 text-success"></i>Team Members</h6>
          <span class="small text-muted" id="memberCount">0 selected</span>
        </div>
        <div class="form-card-body">
          <!-- Search filter -->
          <input type="text" class="form-control mb-3" id="memberSearch" placeholder="Search team members...">

          <div class="members-grid" id="membersGrid">
            <?php
            $selected_members = isset($members) ? $members : [];
            ?>
            <?php foreach ($users as $u): ?>
            <?php $is_selected = in_array($u->id, $selected_members); ?>
            <div class="member-card<?= $is_selected ? ' selected' : '' ?>" data-name="<?= strtolower($u->first_name.' '.$u->last_name) ?>" role="button" tabindex="0">
              <div class="member-card-avatar">
                <?= user_avatar($u->first_name.' '.$u->last_name, $u->avatar ?? null, 38) ?>
              </div>
              <div class="member-card-info">
                <input type="checkbox" class="form-check-input member-pick-cb" name="members[]" value="<?= $u->id ?>"
                  id="member_pick_<?= (int)$u->id ?>" <?= $is_selected ? 'checked' : '' ?>
                  aria-label="<?= html_escape($u->first_name.' '.$u->last_name) ?>">
                <div class="member-card-text">
                  <label class="member-card-name mb-0" for="member_pick_<?= (int)$u->id ?>"><?= html_escape($u->first_name.' '.$u->last_name) ?></label>
                  <div class="member-card-role"><?= html_escape($u->role_name) ?></div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="<?= site_url('projects') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>
          <?= isset($project) ? 'Update Project' : 'Create Project' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<style>
.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 10px;
}
.member-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--tr);
  position: relative;
  background: var(--card-bg);
}
.member-card:hover {
  border-color: var(--primary);
  background: var(--primary-soft);
}
.member-card.selected {
  border-color: var(--primary);
  background: var(--primary-soft);
}
.member-card-avatar { flex-shrink: 0; }
.member-card-info {
  flex: 1; min-width: 0;
  display: flex; align-items: flex-start; gap: 10px;
}
.member-pick-cb {
  width: 1.15rem; height: 1.15rem; margin-top: 2px; flex-shrink: 0;
  cursor: pointer; accent-color: var(--primary);
}
.member-card-text { flex: 1; min-width: 0; }
.member-card-name {
  font-size: .83rem; font-weight: 600; color: var(--text-1);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  cursor: pointer; display: block;
}
.member-card-role { font-size: .72rem; color: var(--text-3); }
.member-card.hidden { display: none; }
</style>

<script>
// Member card: div wrapper avoids label+JS double-toggle (native label would flip checkbox twice)
$(document).on('click', '.member-card', function(e) {
  if ($(e.target).is('input.member-pick-cb')) return;
  if ($(e.target).closest('label.member-card-name').length) {
    return;
  }
  var cb = $(this).find('input.member-pick-cb');
  cb.prop('checked', !cb.prop('checked')).trigger('change');
});
$(document).on('keydown', '.member-card', function(e) {
  if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); $(this).trigger('click'); }
});
$(document).on('change', '.member-card input.member-pick-cb', function() {
  $(this).closest('.member-card').toggleClass('selected', this.checked);
  updateMemberCount();
});

function updateMemberCount() {
  var n = $('.member-card input.member-pick-cb:checked').length;
  $('#memberCount').text(n + ' selected');
}

// Member search
$('#memberSearch').on('input', function() {
  var q = $(this).val().toLowerCase();
  $('.member-card').each(function() {
    var name = $(this).data('name') || '';
    $(this).toggleClass('hidden', q.length > 0 && !name.includes(q));
  });
});

// Init count
updateMemberCount();
</script>

<div class="page-header">
  <div>
    <h1 class="page-title">Team Worksheets</h1>
    <p class="page-subtitle">Daily work summary from all team members</p>
  </div>
  <div class="page-actions">
    <input type="date" class="form-control form-control-sm" id="wDate" value="<?= $date ?>" style="width:160px">
    <button class="btn btn-primary btn-sm" onclick="window.location='<?= site_url('worksheet/admin') ?>?date='+document.getElementById('wDate').value">
      <i class="bi bi-search me-1"></i>View
    </button>
    <button class="btn btn-ghost btn-sm" onclick="window.location='<?= site_url('worksheet/admin') ?>'">Today</button>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="stat-card stat-primary"><div class="stat-icon"><i class="bi bi-journal-check"></i></div><div class="stat-body"><div class="stat-value"><?= count($worksheets) ?></div><div class="stat-label">Submitted Today</div></div></div></div>
</div>

<?php if (empty($worksheets)): ?>
<div class="empty-page-state">
  <div class="empty-icon"><i class="bi bi-journal-x"></i></div>
  <h4>No worksheets for <?= date('M d, Y', strtotime($date)) ?></h4>
  <p>No team members submitted worksheets for this date.</p>
</div>
<?php else: ?>
<div class="row g-4">
  <?php foreach ($worksheets as $w): ?>
  <div class="col-xl-4 col-md-6">
    <div class="card card-modern h-100">
      <div class="card-body p-0">
        <div class="ws-team-header">
          <?= user_avatar($w->first_name.' '.$w->last_name, $w->avatar??null, 40) ?>
          <div class="flex-1">
            <div class="fw-700 small"><?= html_escape($w->first_name.' '.$w->last_name) ?></div>
            <div class="small text-muted"><?= html_escape($w->job_title ?? '') ?></div>
          </div>
          <div class="text-end">
            <span style="font-size:1.4rem"><?= ['great'=>'😄','good'=>'🙂','neutral'=>'😐','stressed'=>'😰','bad'=>'😞'][$w->mood ?? 'good'] ?></span>
            <div class="small fw-700 text-primary"><?= $w->total_hours ?>h</div>
          </div>
        </div>

        <?php if ($w->tasks_done): ?>
        <div class="ws-team-section">
          <div class="ws-team-lbl">✅ Tasks Done</div>
          <div class="ws-team-val"><?= nl2br(html_escape($w->tasks_done)) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($w->plan_tomorrow): ?>
        <div class="ws-team-section">
          <div class="ws-team-lbl">📅 Tomorrow</div>
          <div class="ws-team-val text-muted"><?= nl2br(html_escape($w->plan_tomorrow)) ?></div>
        </div>
        <?php endif; ?>

        <?php if ($w->blockers): ?>
        <div class="ws-team-section">
          <div class="ws-team-lbl text-danger">🚧 Blockers</div>
          <div class="ws-team-val text-danger"><?= nl2br(html_escape($w->blockers)) ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.ws-team-header{display:flex;align-items:center;gap:12px;padding:14px 16px;border-bottom:1px solid var(--border-lt);}
.ws-team-section{padding:10px 16px;border-bottom:1px solid var(--border-lt);}
.ws-team-section:last-child{border-bottom:none;}
.ws-team-lbl{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--text-3);margin-bottom:4px;}
.ws-team-val{font-size:.83rem;line-height:1.5;color:var(--text-1);}
</style>

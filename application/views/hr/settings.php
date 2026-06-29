<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1"><a href="<?= site_url('hr') ?>">HR</a><i class="bi bi-chevron-right"></i><span>Work Settings</span></div>
    <h1 class="page-title">Work Hour Settings</h1>
    <p class="page-subtitle">Define standard working hours for attendance tracking</p>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-xl-6">
    <?php if($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?= $this->session->flashdata('success') ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <form action="<?= site_url('hr/settings') ?>" method="post">
      <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>

      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-clock me-2 text-primary"></i>Working Hours</h6></div>
        <div class="form-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Work Start Time</label>
              <input type="time" class="form-control" name="work_start" value="<?= $settings['work_start']??'09:00' ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Work End Time</label>
              <input type="time" class="form-control" name="work_end" value="<?= $settings['work_end']??'18:00' ?>">
            </div>
            <div class="col-12">
              <label class="form-label fw-600">Late Threshold (minutes after start)</label>
              <input type="number" class="form-control" name="late_threshold" min="0" max="60"
                value="<?= $settings['late_threshold_minutes']??15 ?>">
              <div class="form-text">If employee clocks in more than this many minutes after start time, marked as Late.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-card-header"><h6><i class="bi bi-calendar-week me-2 text-success"></i>Work Days</h6></div>
        <div class="form-card-body">
          <?php
          $work_days = explode(',', $settings['work_days']??'Mon,Tue,Wed,Thu,Fri');
          $all_days  = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
          $labels    = ['Mon'=>'Monday','Tue'=>'Tuesday','Wed'=>'Wednesday','Thu'=>'Thursday','Fri'=>'Friday','Sat'=>'Saturday','Sun'=>'Sunday'];
          ?>
          <div class="day-selector">
            <?php foreach($all_days as $d): ?>
            <label class="day-chip <?= in_array($d,$work_days)?'active':'' ?>">
              <input type="checkbox" name="work_days[]" value="<?= $d ?>" <?= in_array($d,$work_days)?'checked':'' ?>>
              <span><?= $d ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="<?= site_url('hr') ?>" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
      </div>
    </form>
  </div>
</div>

<style>
.day-selector{display:flex;flex-wrap:wrap;gap:8px;}
.day-chip{display:flex;align-items:center;justify-content:center;width:72px;height:44px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;font-weight:600;font-size:.82rem;color:var(--text-2);transition:all .15s;}
.day-chip input{display:none;}
.day-chip.active,.day-chip:has(input:checked){border-color:var(--primary);background:var(--primary);color:#fff;}
.day-chip:hover{border-color:var(--primary);}
</style>

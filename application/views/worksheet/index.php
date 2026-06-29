<div class="page-header">
  <div>
    <h1 class="page-title">Daily Worksheet</h1>
    <p class="page-subtitle">Track your daily work, progress and plans</p>
  </div>
  <div class="page-actions gap-2">
    <?php if (has_role(['admin','project_manager','hr'])): ?>
    <a href="<?= site_url('worksheet/admin') ?>" class="btn btn-ghost btn-sm">
      <i class="bi bi-people me-1"></i>Team Sheets
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Month stats bar -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="ws-stat-card">
      <div class="ws-stat-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-calendar-check"></i></div>
      <div class="ws-stat-val"><?= $stats['present'] ?? 0 ?></div>
      <div class="ws-stat-lbl">Present Days</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="ws-stat-card">
      <div class="ws-stat-icon" style="background:rgba(99,102,241,.1);color:var(--primary)"><i class="bi bi-clock-history"></i></div>
      <div class="ws-stat-val"><?= number_format($stats['total_hours'] ?? 0, 1) ?>h</div>
      <div class="ws-stat-lbl">Hours This Month</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="ws-stat-card">
      <div class="ws-stat-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-journal-check"></i></div>
      <div class="ws-stat-val"><?= count($history) ?></div>
      <div class="ws-stat-lbl">Sheets Filed</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="ws-stat-card">
      <?php $today_clocked = $today_att && !$today_att->clock_out; ?>
      <div class="ws-stat-icon" style="background:rgba(<?= $today_clocked ? '16,185,129' : '100,116,139' ?>,.1);color:<?= $today_clocked ? '#10b981' : '#94a3b8' ?>">
        <i class="bi bi-<?= $today_att ? ($today_clocked ? 'record-circle' : 'check-circle') : 'dash-circle' ?>"></i>
      </div>
      <div class="ws-stat-val" style="font-size:1rem;font-weight:700">
        <?php if ($today_att): ?>
        <?= $today_clocked ? '<span class="text-success">Active</span>' : '<span class="text-muted">Done</span>' ?>
        <?php else: ?>
        <span class="text-muted small">Not in</span>
        <?php endif; ?>
      </div>
      <div class="ws-stat-lbl">Today's Status</div>
    </div>
  </div>
</div>

<div class="row g-4">

  <!-- ═══ WORKSHEET FORM ═══ -->
  <div class="col-xl-7">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern">
          <i class="bi bi-journal-check me-2 text-success"></i>
          <?= $worksheet ? 'Edit Worksheet' : 'New Worksheet' ?>
        </h6>
        <div class="d-flex align-items-center gap-2">
          <input type="date" class="form-control form-control-sm" id="wsDate"
            value="<?= $selected_date ?>" style="width:150px"
            onchange="window.location='<?= site_url('worksheet') ?>?date='+this.value">
          <?php if ($worksheet): ?>
          <span class="badge bg-success-soft text-success"><i class="bi bi-check-circle me-1"></i>Saved</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-body">

        <div class="mb-3">
          <label class="form-label fw-600">
            ✅ Tasks Completed <span class="text-danger">*</span>
            <span class="text-muted fw-400 small">(what you actually finished today)</span>
          </label>
          <textarea class="form-control" id="wsTasks" rows="5"
            placeholder="• Completed user login module&#10;• Fixed bug #ISS-45 on dashboard&#10;• Reviewed John's PR for API module&#10;• Had sync meeting with client"><?= html_escape($worksheet->tasks_done ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-600">📅 Plan for Tomorrow</label>
          <textarea class="form-control" id="wsPlan" rows="3"
            placeholder="• Start payment integration&#10;• Sprint planning at 10am&#10;• Deploy staging build"><?= html_escape($worksheet->plan_tomorrow ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label fw-600">🚧 Blockers / Issues</label>
          <textarea class="form-control" id="wsBlockers" rows="2"
            placeholder="Any blockers, dependencies, or issues to report?"><?= html_escape($worksheet->blockers ?? '') ?></textarea>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label fw-600">⏱ Hours Worked</label>
            <input type="number" class="form-control" id="wsHours" step="0.5" min="0" max="24"
              value="<?= $worksheet->total_hours ?? '' ?>" placeholder="e.g. 8.5">
          </div>
          <div class="col-md-8">
            <label class="form-label fw-600">😊 How was your day?</label>
            <div class="mood-picker">
              <?php
              $moods = ['great'=>['😄','Great!'], 'good'=>['🙂','Good'], 'neutral'=>['😐','Neutral'],
                        'stressed'=>['😰','Stressed'], 'bad'=>['😞','Rough day']];
              $cur   = $worksheet->mood ?? 'good';
              foreach ($moods as $v=>[$em,$lb]):
              ?>
              <label class="mood-chip <?= $cur===$v?'active':'' ?>">
                <input type="radio" name="mood" value="<?= $v ?>" <?= $cur===$v?'checked':'' ?>>
                <span class="mood-emoji"><?= $em ?></span>
                <span class="mood-label"><?= $lb ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <button class="btn btn-success btn-lg w-100" id="btnSaveWs">
          <i class="bi bi-cloud-check me-2"></i>Save Worksheet
        </button>
        <div id="wsSaveMsg" class="mt-3" style="display:none"></div>

      </div>
    </div>
  </div>

  <!-- ═══ HISTORY ═══ -->
  <div class="col-xl-5">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-muted"></i>Worksheet History</h6>
        <div class="d-flex gap-1">
          <select class="form-select form-select-sm" id="histMonth" style="width:110px">
            <?php for ($m=1;$m<=12;$m++): ?>
            <option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option>
            <?php endfor; ?>
          </select>
          <button class="btn btn-ghost btn-sm" onclick="window.location='<?= site_url('worksheet') ?>?month='+document.getElementById('histMonth').value+'&year=<?= $year ?>'">Go</button>
        </div>
      </div>
      <div class="ws-history-list">
        <?php if (empty($history)): ?>
        <div class="empty-state py-5"><i class="bi bi-journal-x"></i><p>No worksheets yet</p></div>
        <?php else: foreach ($history as $w): ?>
        <a class="ws-hist-item <?= $w->work_date===$selected_date?'ws-hist-active':'' ?>"
           href="<?= site_url('worksheet?date='.$w->work_date) ?>">
          <div class="ws-hist-left">
            <div class="ws-hist-date"><?= date('D, M d', strtotime($w->work_date)) ?></div>
            <div class="ws-hist-preview">
              <?= html_escape(substr($w->tasks_done ?? 'No tasks logged', 0, 55)) ?>
              <?= strlen($w->tasks_done ?? '') > 55 ? '…' : '' ?>
            </div>
          </div>
          <div class="ws-hist-right">
            <span class="ws-hist-mood"><?= ['great'=>'😄','good'=>'🙂','neutral'=>'😐','stressed'=>'😰','bad'=>'😞'][$w->mood ?? 'good'] ?></span>
            <span class="ws-hist-hours"><?= $w->total_hours ?>h</span>
            <?php if ($w->blockers): ?>
            <i class="bi bi-exclamation-triangle text-warning" title="Has blockers"></i>
            <?php endif; ?>
          </div>
        </a>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
/* Stat cards */
.ws-stat-card{background:var(--card-bg);border:1px solid var(--border);border-radius:var(--radius);padding:16px;display:flex;align-items:center;gap:12px;}
.ws-stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ws-stat-val{font-size:1.4rem;font-weight:800;line-height:1;}
.ws-stat-lbl{font-size:.73rem;color:var(--text-3);margin-top:2px;}

/* Mood picker */
.mood-picker{display:flex;gap:6px;flex-wrap:wrap;margin-top:4px;}
.mood-chip{display:flex;align-items:center;gap:5px;padding:6px 10px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;transition:all .15s;}
.mood-chip input{display:none;}
.mood-chip:hover{border-color:var(--primary);}
.mood-chip.active{border-color:var(--primary);background:var(--primary-soft);}
.mood-emoji{font-size:1.1rem;}
.mood-label{font-size:.76rem;font-weight:600;color:var(--text-2);}
/* Make active work with :has() or JS */

/* History */
.ws-history-list{max-height:520px;overflow-y:auto;}
.ws-hist-item{display:flex;align-items:center;gap:10px;padding:11px 16px;border-bottom:1px solid var(--border-lt);text-decoration:none;transition:background .12s;}
.ws-hist-item:hover{background:var(--page-bg);}
.ws-hist-item:last-child{border-bottom:none;}
.ws-hist-active{background:var(--primary-soft);border-left:3px solid var(--primary);}
.ws-hist-left{flex:1;min-width:0;}
.ws-hist-date{font-weight:700;font-size:.83rem;color:var(--text-1);}
.ws-hist-preview{font-size:.76rem;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;}
.ws-hist-right{display:flex;align-items:center;gap:6px;flex-shrink:0;}
.ws-hist-mood{font-size:1.1rem;}
.ws-hist-hours{font-size:.74rem;font-weight:700;color:var(--primary);background:var(--primary-soft);padding:2px 6px;border-radius:4px;}
</style>

<script>
var BASE='<?= base_url() ?>';

// Mood chip toggle
document.querySelectorAll('.mood-chip').forEach(function(chip){
  chip.addEventListener('click',function(){
    document.querySelectorAll('.mood-chip').forEach(function(c){ c.classList.remove('active'); });
    this.classList.add('active');
    this.querySelector('input').checked=true;
  });
});

// Save worksheet
document.getElementById('btnSaveWs').addEventListener('click',function(){
  var tasks=document.getElementById('wsTasks').value.trim();
  if(!tasks){ showMsg('wsSaveMsg','Please fill in tasks done.','danger'); return; }
  var mood=document.querySelector('input[name="mood"]:checked');
  var self=this;
  self.disabled=true;
  self.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
  var fd=new FormData();
  fd.append('work_date', document.getElementById('wsDate').value);
  fd.append('tasks_done', tasks);
  fd.append('plan_tomorrow', document.getElementById('wsPlan').value);
  fd.append('blockers', document.getElementById('wsBlockers').value);
  fd.append('total_hours', document.getElementById('wsHours').value||0);
  fd.append('mood', mood?mood.value:'good');
  fetch(BASE+'worksheet/save',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
      self.disabled=false;
      self.innerHTML='<i class="bi bi-cloud-check me-2"></i>Save Worksheet';
      if(d.success){
        showMsg('wsSaveMsg',d.message,'success');
        setTimeout(function(){location.reload();},1000);
      } else {
        showMsg('wsSaveMsg',d.message,'danger');
      }
    })
    .catch(function(e){ self.disabled=false; showMsg('wsSaveMsg','Error. Try again.','danger'); });
});

function showMsg(id,msg,type){
  var el=document.getElementById(id);
  el.style.display='block';
  el.className='alert alert-'+type;
  el.textContent=msg;
}
</script>

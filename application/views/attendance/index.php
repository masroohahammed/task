<div class="page-header">
  <div>
    <h1 class="page-title">Attendance & Leave</h1>
    <p class="page-subtitle"><?= date('l, F j, Y') ?></p>
  </div>
  <?php if (has_role(['admin','project_manager'])): ?>
  <div class="page-actions gap-2">
    <a href="<?= site_url('attendance/admin') ?>" class="btn btn-ghost btn-sm">
      <i class="bi bi-people me-1"></i>Team View
    </a>
    <a href="<?= site_url('attendance/performance') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-graph-up me-1"></i>Performance
    </a>
  </div>
  <?php endif; ?>
</div>

<!-- ══ TABS ══════════════════════════════════════════════ -->
<ul class="att-tabs" id="attTabs">
  <li><a class="att-tab active" data-tab="today" href="#"><i class="bi bi-clock me-1"></i>Today</a></li>
  <li><a class="att-tab" data-tab="worksheet" href="#"><i class="bi bi-journal-check me-1"></i>Daily Sheet</a></li>
  <li><a class="att-tab" data-tab="leave" href="#"><i class="bi bi-calendar-x me-1"></i>Leave</a></li>
  <li><a class="att-tab" data-tab="history" href="#"><i class="bi bi-calendar3 me-1"></i>History</a></li>
  <?php if (has_role(['admin','project_manager']) && !empty($pending_leaves)): ?>
  <li>
    <a class="att-tab" data-tab="approvals" href="#">
      <i class="bi bi-check2-circle me-1"></i>Approvals
      <span class="tab-badge"><?= count($pending_leaves) ?></span>
    </a>
  </li>
  <?php endif; ?>
</ul>

<!-- ══ TAB: TODAY ═════════════════════════════════════════ -->
<div class="att-panel active" id="tab-today">
  <div class="row g-4">

    <!-- Clock Card -->
    <div class="col-lg-4">
      <div class="card card-modern">
        <div class="card-body att-clock-card">
          <?php if (!empty($shift)): ?>
          <div class="small text-muted mb-2">
            <i class="bi bi-clock-history me-1"></i>Your shift: <strong><?= html_escape($shift['work_start']) ?> – <?= html_escape($shift['work_end']) ?></strong>
          </div>
          <?php endif; ?>

          <?php if (!$today): ?>
          <!-- State A: Not clocked in -->
          <div class="clock-visual clock-idle">
            <div class="clock-ring"><i class="bi bi-power"></i></div>
            <div class="clock-label">Ready to start?</div>
            <div class="clock-time-display" id="currentTime"></div>
          </div>
          <div class="clock-actions">
            <button class="btn-clock btn-clock-in" id="btnClockIn">
              <i class="bi bi-play-circle-fill"></i> Clock In
            </button>
          </div>

          <?php elseif (!$today->clock_out): ?>
          <!-- State B: Clocked in, not out -->
          <div class="clock-visual clock-active">
            <div class="clock-ring clock-ring-pulse"><i class="bi bi-record-circle"></i></div>
            <div class="clock-label text-success fw-700">Working</div>
            <div class="clock-in-time">Since <?= date('h:i A', strtotime($today->clock_in)) ?></div>
            <div class="clock-elapsed fw-800" id="elapsedTime"></div>
          </div>
          <div class="clock-actions">
            <button class="btn-clock btn-clock-out" id="btnClockOut">
              <i class="bi bi-stop-circle-fill"></i> Clock Out
            </button>
          </div>
          <div class="d-grid gap-2 mt-3">
            <?php if (!empty($open_break)): ?>
            <button type="button" class="btn btn-outline-warning btn-sm" id="btnBreakOut">
              <i class="bi bi-cup-hot me-1"></i> Break out (end break)
            </button>
            <?php else: ?>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnBreakIn">
              <i class="bi bi-cup-hot me-1"></i> Break in (start break)
            </button>
            <?php endif; ?>
          </div>
          <script>
          var _cin = new Date('<?= $today->clock_in ?>');
          function _tick(){
            var s=Math.floor((Date.now()-_cin)/1000),h=Math.floor(s/3600),m=Math.floor((s%3600)/60),ss=s%60;
            var el=document.getElementById('elapsedTime');
            if(el) el.textContent=(h<10?'0':'')+h+'h '+(m<10?'0':'')+m+'m '+(ss<10?'0':'')+ss+'s';
          }
          setInterval(_tick,1000);_tick();
          </script>

          <?php else: ?>
          <!-- State C: Done for the day -->
          <div class="clock-visual clock-done">
            <div class="clock-ring clock-ring-done"><i class="bi bi-check-circle-fill"></i></div>
            <div class="clock-label text-primary fw-700">Day Complete!</div>
            <div class="clock-summary">
              <div class="cs-item"><span><?= date('h:i A', strtotime($today->clock_in)) ?></span><small>In</small></div>
              <div class="cs-sep">→</div>
              <div class="cs-item"><span><?= date('h:i A', strtotime($today->clock_out)) ?></span><small>Out</small></div>
              <div class="cs-sep">·</div>
              <div class="cs-item"><span class="text-primary"><?= $today->total_hours ?>h</span><small>Total</small></div>
            </div>
            <div class="mt-2"><?= status_badge($today->status) ?></div>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- Month Stats -->
    <div class="col-lg-8">
      <div class="row g-3">
        <div class="col-6 col-md-3">
          <div class="att-stat-card att-stat-green">
            <div class="att-stat-val"><?= $stats['present'] ?></div>
            <div class="att-stat-lbl">Present</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="att-stat-card att-stat-amber">
            <div class="att-stat-val"><?= $stats['late'] ?></div>
            <div class="att-stat-lbl">Late</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="att-stat-card att-stat-blue">
            <div class="att-stat-val"><?= $stats['half_day'] ?></div>
            <div class="att-stat-lbl">Half Day</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="att-stat-card att-stat-purple">
            <div class="att-stat-val"><?= number_format($stats['total_hours'],1) ?>h</div>
            <div class="att-stat-lbl">Total Hrs</div>
          </div>
        </div>
      </div>

      <!-- Today note -->
      <?php if (!$today || !$today->clock_out): ?>
      <div class="card card-modern mt-3">
        <div class="card-body">
          <label class="form-label fw-600 small">Note (optional)</label>
          <textarea class="form-control form-control-sm" id="clockNote" rows="2"
            placeholder="Working from home, visiting client..."></textarea>
        </div>
      </div>
      <?php endif; ?>

      <!-- Today's quick worksheet preview -->
      <?php if ($today): ?>
      <div class="card card-modern mt-3">
        <div class="card-header-modern">
          <h6 class="card-title-modern small"><i class="bi bi-journal-check me-1 text-success"></i>Today's Worksheet Status</h6>
          <button class="btn btn-sm btn-ghost" onclick="switchTab('worksheet')">
            <?= $worksheet ? 'Edit' : 'Fill Now' ?> <i class="bi bi-arrow-right"></i>
          </button>
        </div>
        <div class="card-body py-3">
          <?php if ($worksheet): ?>
          <div class="d-flex align-items-center gap-3">
            <span class="mood-big"><?= ['great'=>'😄','good'=>'🙂','neutral'=>'😐','stressed'=>'😰','bad'=>'😞'][$worksheet->mood] ?></span>
            <div>
              <div class="fw-600 small">Worksheet submitted</div>
              <div class="small text-muted"><?= $worksheet->total_hours ?>h logged · <?= ucfirst($worksheet->mood) ?></div>
            </div>
            <span class="badge bg-success-soft text-success ms-auto"><i class="bi bi-check-circle me-1"></i>Done</span>
          </div>
          <?php else: ?>
          <div class="d-flex align-items-center gap-3">
            <span class="mood-big">📝</span>
            <div class="small text-muted">No worksheet submitted for today yet.</div>
            <span class="badge bg-warning-soft text-warning ms-auto">Pending</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ══ TAB: WORKSHEET ═════════════════════════════════════ -->
<div class="att-panel" id="tab-worksheet">
  <div class="row g-4">
    <div class="col-xl-7">
      <div class="card card-modern">
        <div class="card-header-modern">
          <h6 class="card-title-modern"><i class="bi bi-journal-check me-2 text-success"></i>Daily Work Sheet</h6>
          <input type="date" class="form-control form-control-sm" id="wsDate" value="<?= date('Y-m-d') ?>" style="width:150px">
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-600">✅ Tasks Completed Today <span class="text-danger">*</span></label>
            <textarea class="form-control" id="wsTasks" rows="5"
              placeholder="• Completed the login module&#10;• Fixed bug #ISS-123 in dashboard&#10;• Code review for PR #45"><?= html_escape($worksheet->tasks_done ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">📅 Plan for Tomorrow</label>
            <textarea class="form-control" id="wsPlan" rows="3"
              placeholder="• Start API integration&#10;• Attend sprint planning meeting"><?= html_escape($worksheet->plan_tomorrow ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">🚧 Blockers / Issues</label>
            <textarea class="form-control" id="wsBlockers" rows="2"
              placeholder="Any blockers or dependencies?"><?= html_escape($worksheet->blockers ?? '') ?></textarea>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label fw-600">⏱ Hours Worked</label>
              <input type="number" class="form-control" id="wsHours" step="0.5" min="0" max="24"
                value="<?= $worksheet->total_hours ?? '' ?>" placeholder="e.g. 8">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">😊 Mood</label>
              <select class="form-select" id="wsMood">
                <?php foreach (['great'=>'😄 Great','good'=>'🙂 Good','neutral'=>'😐 Neutral','stressed'=>'😰 Stressed','bad'=>'😞 Not Great'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($worksheet->mood ?? 'good')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <button class="btn btn-success btn-lg w-100" id="btnSaveWorksheet">
            <i class="bi bi-cloud-check me-2"></i>Save Today's Worksheet
          </button>
          <div id="wsMsg" class="mt-3" style="display:none"></div>
        </div>
      </div>
    </div>

    <!-- Past worksheets -->
    <div class="col-xl-5">
      <div class="card card-modern">
        <div class="card-header-modern">
          <h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-muted"></i>Recent Worksheets</h6>
        </div>
        <div class="card-body p-0" style="max-height:600px;overflow-y:auto">
          <?php $worksheets = $this->Attendance_model->get_worksheets($current_user->id, 14); ?>
          <?php if (empty($worksheets)): ?>
          <div class="empty-state py-4"><i class="bi bi-journal-x"></i><p>No worksheets yet</p></div>
          <?php else: foreach ($worksheets as $w): ?>
          <div class="ws-item <?= $w->work_date === date('Y-m-d') ? 'ws-today' : '' ?>">
            <div class="ws-item-head">
              <span class="ws-item-date"><?= date('D, M d', strtotime($w->work_date)) ?></span>
              <div class="d-flex align-items-center gap-2">
                <span class="small text-muted"><?= $w->total_hours ?>h</span>
                <span class="ws-mood"><?= ['great'=>'😄','good'=>'🙂','neutral'=>'😐','stressed'=>'😰','bad'=>'😞'][$w->mood] ?></span>
              </div>
            </div>
            <?php if ($w->tasks_done): ?>
            <div class="ws-item-text"><?= nl2br(html_escape(substr($w->tasks_done, 0, 140))) ?><?= strlen($w->tasks_done)>140?'…':'' ?></div>
            <?php endif; ?>
            <?php if ($w->blockers): ?>
            <div class="ws-item-blocker"><i class="bi bi-exclamation-triangle me-1"></i><?= html_escape(substr($w->blockers,0,80)) ?></div>
            <?php endif; ?>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ TAB: LEAVE ══════════════════════════════════════════ -->
<div class="att-panel" id="tab-leave">
  <div class="row g-4">

    <!-- Request form -->
    <div class="col-lg-5">
      <div class="card card-modern">
        <div class="card-header-modern">
          <h6 class="card-title-modern"><i class="bi bi-calendar-plus me-2 text-primary"></i>Apply for Leave</h6>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Leave Type <span class="text-danger">*</span></label>
            <select class="form-select" id="leaveType">
              <option value="annual">🌴 Annual Leave</option>
              <option value="sick">🤒 Sick Leave</option>
              <option value="emergency">🚨 Emergency Leave</option>
              <option value="half_day">⏰ Half Day</option>
              <option value="unpaid">💸 Unpaid Leave</option>
              <option value="other">📝 Other</option>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-600">From Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="leaveFrom" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-6">
              <label class="form-label fw-600">To Date</label>
              <input type="date" class="form-control" id="leaveTo" value="<?= date('Y-m-d') ?>">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Reason <span class="text-danger">*</span></label>
            <textarea class="form-control" id="leaveReason" rows="3"
              placeholder="Explain why you need this leave..."></textarea>
          </div>

          <div class="leave-notify-info mb-3">
            <i class="bi bi-info-circle text-primary me-2"></i>
            <span class="small text-muted">Your request will be sent to <strong>Project Manager</strong> and <strong>Admin</strong> for approval.</span>
          </div>

          <button class="btn btn-primary w-100" id="btnSubmitLeave">
            <i class="bi bi-send me-2"></i>Submit Leave Request
          </button>
          <div id="leaveMsg" class="mt-3" style="display:none"></div>
        </div>
      </div>
    </div>

    <!-- My leave history -->
    <div class="col-lg-7">
      <div class="card card-modern">
        <div class="card-header-modern">
          <h6 class="card-title-modern"><i class="bi bi-list-check me-2 text-muted"></i>My Leave Requests</h6>
        </div>
        <div class="card-body p-0">
          <?php if (empty($my_leaves)): ?>
          <div class="empty-state py-5">
            <i class="bi bi-calendar-x"></i>
            <p>No leave requests yet</p>
          </div>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover table-modern">
              <thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>By</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($my_leaves as $lv): ?>
              <tr>
                <td>
                  <span class="leave-type-badge leave-<?= $lv->leave_type ?>">
                    <?= ['annual'=>'🌴 Annual','sick'=>'🤒 Sick','emergency'=>'🚨 Emergency',
                         'half_day'=>'⏰ Half Day','unpaid'=>'💸 Unpaid','other'=>'📝 Other'][$lv->leave_type] ?>
                  </span>
                </td>
                <td>
                  <div class="small fw-600"><?= date('M d', strtotime($lv->from_date)) ?><?= $lv->from_date !== $lv->to_date ? ' – '.date('M d', strtotime($lv->to_date)) : '' ?></div>
                  <div class="small text-muted"><?= date('Y', strtotime($lv->from_date)) ?></div>
                </td>
                <td><span class="fw-700"><?= $lv->total_days ?></span></td>
                <td><?= leave_status_badge($lv->status) ?></td>
                <td>
                  <span class="small text-muted">
                    <?= $lv->approver_first ? html_escape($lv->approver_first.' '.$lv->approver_last) : '—' ?>
                  </span>
                  <?php if ($lv->reject_reason): ?>
                  <div class="small text-danger"><?= html_escape($lv->reject_reason) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($lv->status === 'pending'): ?>
                  <button class="btn btn-sm btn-ghost text-danger" onclick="cancelLeave(<?= $lv->id ?>, this)">
                    <i class="bi bi-x"></i>
                  </button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══ TAB: HISTORY ════════════════════════════════════════ -->
<div class="att-panel" id="tab-history">
  <div class="filter-bar mb-3">
    <div class="filter-group">
      <label class="filter-label">Month</label>
      <select class="form-select form-select-sm" id="histMonth" style="width:130px">
        <?php for ($m=1;$m<=12;$m++): ?>
        <option value="<?= $m ?>" <?= $month==$m?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="filter-group">
      <label class="filter-label">Year</label>
      <select class="form-select form-select-sm" id="histYear" style="width:90px">
        <?php for ($y=date('Y');$y>=date('Y')-3;$y--): ?>
        <option value="<?= $y ?>" <?= $year==$y?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button class="btn btn-ghost btn-sm" onclick="window.location='<?= site_url('attendance') ?>?month='+$('#histMonth').val()+'&year='+$('#histYear').val()">
      <i class="bi bi-search me-1"></i>View
    </button>
  </div>

  <div class="card card-modern">
    <div class="card-body p-0">
      <?php if (empty($history)): ?>
      <div class="empty-state py-5"><i class="bi bi-calendar-x"></i><p>No records for this month</p></div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-modern">
          <thead><tr><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Hours</th><th>Status</th><th>Note</th></tr></thead>
          <tbody>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><span class="fw-600 small"><?= date('D, M d', strtotime($h->work_date)) ?></span></td>
            <td><span class="text-success fw-600 small"><?= date('h:i A', strtotime($h->clock_in)) ?></span></td>
            <td>
              <?php if ($h->clock_out): ?>
              <span class="text-danger fw-600 small"><?= date('h:i A', strtotime($h->clock_out)) ?></span>
              <?php else: ?>
              <span class="badge bg-warning-soft text-warning">No out</span>
              <?php endif; ?>
            </td>
            <td><span class="fw-700 text-primary"><?= $h->total_hours ? $h->total_hours.'h' : '—' ?></span></td>
            <td><?= status_badge($h->status) ?></td>
            <td><span class="small text-muted"><?= html_escape($h->note ?? '') ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ══ TAB: APPROVALS (PM/Admin) ══════════════════════════ -->
<?php if (has_role(['admin','project_manager'])): ?>
<div class="att-panel" id="tab-approvals">
  <div class="card card-modern">
    <div class="card-header-modern">
      <h6 class="card-title-modern"><i class="bi bi-hourglass-split me-2 text-warning"></i>Pending Leave Approvals</h6>
      <span class="badge bg-warning-soft text-warning"><?= count($pending_leaves) ?> pending</span>
    </div>
    <div class="card-body p-0">
      <?php if (empty($pending_leaves)): ?>
      <div class="empty-state py-5"><i class="bi bi-check-circle"></i><p>No pending requests!</p></div>
      <?php else: foreach ($pending_leaves as $lv): ?>
      <div class="leave-approval-row">
        <div class="d-flex align-items-start gap-3">
          <?= user_avatar($lv->first_name.' '.$lv->last_name, $lv->avatar ?? null, 40) ?>
          <div class="flex-1">
            <div class="fw-700"><?= html_escape($lv->first_name.' '.$lv->last_name) ?></div>
            <div class="small text-muted"><?= html_escape($lv->job_title ?? $lv->role_name) ?></div>
            <div class="d-flex flex-wrap gap-2 mt-1">
              <span class="leave-type-badge leave-<?= $lv->leave_type ?>">
                <?= ['annual'=>'🌴 Annual','sick'=>'🤒 Sick','emergency'=>'🚨 Emergency',
                     'half_day'=>'⏰ Half Day','unpaid'=>'💸 Unpaid','other'=>'📝 Other'][$lv->leave_type] ?>
              </span>
              <span class="small text-muted">
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('M d', strtotime($lv->from_date)) ?><?= $lv->from_date !== $lv->to_date ? ' – '.date('M d', strtotime($lv->to_date)) : '' ?>
                (<?= $lv->total_days ?> day<?= $lv->total_days>1?'s':'' ?>)
              </span>
              <span class="small text-muted"><i class="bi bi-clock me-1"></i><?= time_ago($lv->created_at) ?></span>
            </div>
            <div class="leave-reason"><?= html_escape($lv->reason) ?></div>
          </div>
          <div class="leave-approval-actions">
            <button class="btn btn-sm btn-success" onclick="approveLeave(<?= $lv->id ?>, this)">
              <i class="bi bi-check-lg me-1"></i>Approve
            </button>
            <button class="btn btn-sm btn-ghost text-danger" onclick="openReject(<?= $lv->id ?>)">
              <i class="bi bi-x-lg me-1"></i>Reject
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- All leaves this month -->
  <?php if (!empty($all_leaves)): ?>
  <div class="card card-modern mt-4">
    <div class="card-header-modern">
      <h6 class="card-title-modern"><i class="bi bi-list-ul me-2"></i>All Leave Requests — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></h6>
    </div>
    <div class="table-responsive">
      <table class="table table-hover table-modern">
        <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th>Approved By</th></tr></thead>
        <tbody>
        <?php foreach ($all_leaves as $lv): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center gap-2">
              <?= user_avatar($lv->first_name.' '.$lv->last_name, $lv->avatar ?? null, 30) ?>
              <span class="small fw-600"><?= html_escape($lv->first_name.' '.$lv->last_name) ?></span>
            </div>
          </td>
          <td><span class="small"><?= ucfirst($lv->leave_type) ?></span></td>
          <td><span class="small"><?= date('M d', strtotime($lv->from_date)) ?><?= $lv->from_date!==$lv->to_date?' – '.date('M d',strtotime($lv->to_date)):'' ?></span></td>
          <td><span class="fw-700 small"><?= $lv->total_days ?></span></td>
          <td><?= leave_status_badge($lv->status) ?></td>
          <td><span class="small text-muted"><?= $lv->approver_first ? html_escape($lv->approver_first.' '.$lv->approver_last) : '—' ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Reject Modal -->
<div class="modal fade" id="clockOutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title">Complete clock-out</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <p class="small text-muted" id="coModalHint"></p>
        <div class="mb-3" id="coEarlyWrap" style="display:none">
          <label class="form-label fw-600">Reason for early clock-out <span class="text-danger">*</span></label>
          <textarea class="form-control" id="coEarlyReason" rows="2"></textarea>
        </div>
        <div class="mb-0">
          <label class="form-label fw-600">Note <span class="text-muted fw-400">(optional)</span></label>
          <textarea class="form-control" id="coNote" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer-modern">
        <button class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary btn-sm" id="btnConfirmClockOut"><i class="bi bi-stop-circle me-1"></i>Clock out</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content modal-modern">
      <div class="modal-header-modern">
        <h5 class="modal-title">Reject Leave</h5>
        <button type="button" class="btn-close-modern" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="rejectLeaveId">
        <label class="form-label fw-600">Reason for rejection</label>
        <textarea class="form-control" id="rejectReason" rows="3" placeholder="Please provide a reason..."></textarea>
      </div>
      <div class="modal-footer-modern">
        <button class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger btn-sm" id="btnConfirmReject"><i class="bi bi-x-lg me-1"></i>Reject</button>
      </div>
    </div>
  </div>
</div>

<?php
// Helper function for leave status badge
function leave_status_badge($status) {
    $map = [
        'pending'   => ['bg-warning-soft text-warning',  'bi-hourglass-split', 'Pending'],
        'approved'  => ['bg-success-soft text-success',  'bi-check-circle',    'Approved'],
        'rejected'  => ['bg-danger-soft text-danger',    'bi-x-circle',        'Rejected'],
        'cancelled' => ['bg-secondary-soft text-muted',  'bi-dash-circle',     'Cancelled'],
    ];
    $m = $map[$status] ?? ['bg-secondary-soft text-muted','bi-circle','Unknown'];
    return '<span class="badge '.$m[0].'"><i class="bi '.$m[1].' me-1"></i>'.$m[2].'</span>';
}
?>

<style>
/* ── Tabs ── */
.att-tabs { display:flex; gap:4px; list-style:none; padding:0; margin:0 0 20px;
  border-bottom:1px solid var(--border); overflow-x:auto; }
.att-tabs li { flex-shrink:0; }
.att-tab { display:flex; align-items:center; padding:10px 16px; font-size:.84rem;
  font-weight:600; color:var(--text-2); text-decoration:none;
  border-bottom:2px solid transparent; margin-bottom:-1px;
  transition:all var(--tr); white-space:nowrap; }
.att-tab:hover { color:var(--primary); text-decoration:none; }
.att-tab.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-badge { background:var(--warning); color:#fff; font-size:.65rem;
  font-weight:700; border-radius:99px; padding:2px 6px; margin-left:5px; }

/* ── Panels ── */
.att-panel { display:none; }
.att-panel.active { display:block; }

/* ── Clock card ── */
.att-clock-card { padding:28px 20px; text-align:center; }
.clock-visual { margin-bottom:20px; }
.clock-ring {
  width:80px; height:80px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:2rem; margin:0 auto 10px;
}
.clock-idle .clock-ring { background:rgba(100,116,139,.1); color:#94a3b8; }
.clock-active .clock-ring { background:rgba(16,185,129,.12); color:#10b981; }
.clock-done .clock-ring { background:rgba(99,102,241,.1); color:var(--primary); }
.clock-ring-pulse {
  animation:clk-pulse 2s infinite;
  box-shadow:0 0 0 8px rgba(16,185,129,.12),0 0 0 16px rgba(16,185,129,.05);
}
@keyframes clk-pulse {
  0%,100%{box-shadow:0 0 0 8px rgba(16,185,129,.12),0 0 0 16px rgba(16,185,129,.05);}
  50%{box-shadow:0 0 0 14px rgba(16,185,129,.08),0 0 0 24px rgba(16,185,129,.02);}
}
.clock-label { font-weight:700; font-size:1rem; margin-bottom:4px; }
.clock-time-display,.clock-in-time { font-size:.82rem; color:var(--text-3); }
.clock-elapsed { font-size:1.5rem; letter-spacing:.02em; color:var(--primary); margin-top:6px; }
.clock-summary { display:flex; align-items:center; justify-content:center; gap:10px; margin-top:10px; }
.cs-item { text-align:center; }
.cs-item span { display:block; font-weight:800; font-size:.95rem; }
.cs-item small { color:var(--text-3); font-size:.7rem; }
.cs-sep { color:var(--text-3); font-size:.9rem; }

.clock-actions { margin-top:16px; }
.btn-clock {
  display:inline-flex; align-items:center; justify-content:center; gap:8px;
  width:100%; padding:13px 20px; border-radius:12px;
  font-size:.95rem; font-weight:700; border:none; cursor:pointer;
  font-family:var(--font); transition:all var(--tr);
}
.btn-clock-in  { background:linear-gradient(135deg,#10b981,#059669); color:#fff; }
.btn-clock-in:hover  { transform:translateY(-2px); box-shadow:0 6px 20px rgba(16,185,129,.35); }
.btn-clock-out { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; }
.btn-clock-out:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(239,68,68,.35); }

/* ── Stat cards ── */
.att-stat-card { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius); padding:20px; text-align:center; }
.att-stat-val { font-size:2rem; font-weight:800; line-height:1; }
.att-stat-lbl { font-size:.74rem; color:var(--text-3); margin-top:4px; font-weight:500; }
.att-stat-green .att-stat-val { color:#10b981; }
.att-stat-amber .att-stat-val { color:#f59e0b; }
.att-stat-blue  .att-stat-val { color:#06b6d4; }
.att-stat-purple .att-stat-val { color:var(--primary); }

/* ── Worksheet items ── */
.ws-item { padding:12px 18px; border-bottom:1px solid var(--border-lt); }
.ws-item:last-child { border-bottom:none; }
.ws-today { background:rgba(99,102,241,.04); border-left:3px solid var(--primary); }
.ws-item-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:4px; }
.ws-item-date { font-weight:700; font-size:.83rem; }
.ws-mood { font-size:1rem; }
.ws-item-text { font-size:.8rem; color:var(--text-2); line-height:1.4; }
.ws-item-blocker { font-size:.78rem; color:var(--danger); margin-top:3px; }
.mood-big { font-size:1.8rem; }

/* ── Leave ── */
.leave-notify-info { background:rgba(99,102,241,.06); border:1px solid rgba(99,102,241,.15);
  border-radius:8px; padding:10px 12px; display:flex; align-items:flex-start; gap:0; }
.leave-type-badge { display:inline-flex; align-items:center; gap:4px; font-size:.78rem;
  font-weight:600; padding:3px 9px; border-radius:5px; background:var(--primary-soft);
  color:var(--primary); }
.leave-approval-row { padding:16px 20px; border-bottom:1px solid var(--border-lt); }
.leave-approval-row:last-child { border-bottom:none; }
.leave-approval-actions { display:flex; gap:6px; flex-shrink:0; }
.leave-reason { font-size:.82rem; color:var(--text-2); margin-top:6px;
  background:var(--page-bg); border-radius:6px; padding:6px 10px; }
</style>

<script>
var BASE = '<?= base_url() ?>';
var CSRF = '<?= $this->security->get_csrf_hash() ?>';

/* ── Tab switching ── */
function switchTab(name) {
  document.querySelectorAll('.att-tab').forEach(function(t){ t.classList.remove('active'); });
  document.querySelectorAll('.att-panel').forEach(function(p){ p.classList.remove('active'); });
  var tab = document.querySelector('[data-tab="'+name+'"]');
  if (tab) tab.classList.add('active');
  var panel = document.getElementById('tab-'+name);
  if (panel) panel.classList.add('active');
}
document.querySelectorAll('.att-tab').forEach(function(tab) {
  tab.addEventListener('click', function(e) {
    e.preventDefault();
    switchTab(this.dataset.tab);
  });
});

/* ── Live clock ── */
function liveClock() {
  var el = document.getElementById('currentTime');
  if (!el) return;
  var d = new Date(), h=d.getHours(), m=d.getMinutes(), s=d.getSeconds(), a=h>=12?'PM':'AM';
  h=h%12||12;
  el.textContent=(h<10?'0':'')+h+':'+(m<10?'0':'')+m+':'+(s<10?'0':'')+s+' '+a;
}
setInterval(liveClock,1000); liveClock();

/* ── Clock In ── */
var $ci = document.getElementById('btnClockIn');
if ($ci) {
  $ci.addEventListener('click', function() {
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clocking in…';
    var self = this;
    $.ajax({
      url: BASE+'attendance/clock_in',
      type:'POST',
      data: { note: document.getElementById('clockNote').value },
      dataType:'json',
      success: function(r) {
        if (r.success) { showToast('Clocked in at '+r.time, 'success'); setTimeout(function(){ location.reload(); }, 800); }
        else { showToast(r.message, 'danger'); self.disabled=false; self.innerHTML='<i class="bi bi-play-circle-fill"></i> Clock In'; }
      },
      error: function(xhr) { showToast('Error: '+xhr.status, 'danger'); self.disabled=false; }
    });
  });
}

var $bi = document.getElementById('btnBreakIn');
if ($bi) {
  $bi.addEventListener('click', function() {
    var self = this; self.disabled = true;
    $.post(BASE+'attendance/break_start', { csrf_token: CSRF }, function(r) {
      self.disabled = false;
      if (r.success) { showToast('Break started', 'success'); setTimeout(function(){ location.reload(); }, 600); }
      else showToast(r.message, 'danger');
    }, 'json');
  });
}
var $bo = document.getElementById('btnBreakOut');
if ($bo) {
  $bo.addEventListener('click', function() {
    var self = this; self.disabled = true;
    $.post(BASE+'attendance/break_end', { csrf_token: CSRF }, function(r) {
      self.disabled = false;
      if (r.success) { showToast('Break ended', 'success'); setTimeout(function(){ location.reload(); }, 600); }
      else showToast(r.message, 'danger');
    }, 'json');
  });
}

/* ── Clock Out ── */
var $co = document.getElementById('btnClockOut');
if ($co) {
  $co.addEventListener('click', function() {
    var self = this;
    $.getJSON(BASE+'attendance/pre_clock_out', function(pre) {
      if (!pre.success) {
        if (pre.code === 'worksheet_required') {
          showToast('Daily status required: fill the worksheet first.', 'danger');
          switchTab('worksheet');
          return;
        }
        showToast(pre.message || 'Cannot clock out', 'danger');
        return;
      }
      document.getElementById('coModalHint').textContent = pre.early_candidate
        ? 'You are before duty end (' + (pre.duty_end || '') + '). A reason is required.'
        : 'Confirm clock-out.';
      document.getElementById('coEarlyWrap').style.display = pre.early_candidate ? 'block' : 'none';
      document.getElementById('coEarlyReason').value = '';
      document.getElementById('coNote').value = document.getElementById('clockNote') ? document.getElementById('clockNote').value : '';
      var modal = new bootstrap.Modal(document.getElementById('clockOutModal'));
      document.getElementById('btnConfirmClockOut').onclick = function() {
        var early = pre.early_candidate ? document.getElementById('coEarlyReason').value.trim() : '';
        if (pre.early_candidate && !early) { showToast('Enter reason for early clock-out.', 'danger'); return; }
        var note = document.getElementById('coNote').value;
        self.disabled = true;
        self.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Clocking out…';
        $.ajax({
          url: BASE+'attendance/clock_out',
          type:'POST',
          data: { note: note, early_clockout_reason: early, csrf_token: CSRF },
          dataType:'json',
          success: function(r) {
            if (r.success) {
              modal.hide();
              showToast('Clocked out at '+r.time+' · '+r.total_hours+'h', 'success');
              setTimeout(function(){ location.reload(); }, 800);
            } else {
              showToast(r.message, 'danger');
              self.disabled=false;
              self.innerHTML='<i class="bi bi-stop-circle-fill"></i> Clock Out';
            }
          },
          error: function(xhr) { showToast('Error: '+xhr.status, 'danger'); self.disabled=false; }
        });
      };
      modal.show();
    });
  });
}

/* ── Save Worksheet ── */
document.getElementById('btnSaveWorksheet').addEventListener('click', function() {
  var tasks = document.getElementById('wsTasks').value.trim();
  if (!tasks) { showMsg('wsMsg', 'Please fill in tasks done today.', 'danger'); return; }
  var self = this;
  self.disabled = true;
  self.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
  $.ajax({
    url: BASE+'attendance/save_worksheet',
    type:'POST',
    data: {
      work_date:     document.getElementById('wsDate').value,
      tasks_done:    tasks,
      plan_tomorrow: document.getElementById('wsPlan').value,
      blockers:      document.getElementById('wsBlockers').value,
      total_hours:   document.getElementById('wsHours').value,
      mood:          document.getElementById('wsMood').value
    },
    dataType:'json',
    success: function(r) {
      self.disabled=false;
      self.innerHTML='<i class="bi bi-cloud-check me-2"></i>Save Today\'s Worksheet';
      if (r.success) { showMsg('wsMsg', r.message, 'success'); showToast(r.message, 'success'); setTimeout(function(){ location.reload(); },1200); }
      else showMsg('wsMsg', r.message, 'danger');
    },
    error: function(xhr){ self.disabled=false; showMsg('wsMsg','Server error '+xhr.status,'danger'); }
  });
});

/* ── Submit Leave ── */
document.getElementById('btnSubmitLeave').addEventListener('click', function() {
  var reason = document.getElementById('leaveReason').value.trim();
  var from   = document.getElementById('leaveFrom').value;
  if (!from || !reason) { showMsg('leaveMsg','Date and reason are required.','danger'); return; }
  var self = this;
  self.disabled=true;
  self.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
  $.ajax({
    url: BASE+'attendance/leave_request',
    type:'POST',
    data: {
      leave_type: document.getElementById('leaveType').value,
      from_date:  from,
      to_date:    document.getElementById('leaveTo').value || from,
      reason:     reason
    },
    dataType:'json',
    success: function(r) {
      self.disabled=false;
      self.innerHTML='<i class="bi bi-send me-2"></i>Submit Leave Request';
      if (r.success) {
        showMsg('leaveMsg', r.message, 'success');
        showToast(r.message, 'success');
        document.getElementById('leaveReason').value='';
        setTimeout(function(){ location.reload(); }, 1200);
      } else {
        showMsg('leaveMsg', r.message, 'danger');
      }
    },
    error: function(xhr){ self.disabled=false; showMsg('leaveMsg','Server error '+xhr.status,'danger'); }
  });
});

/* ── Approve Leave ── */
function approveLeave(id, btn) {
  btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>';
  $.ajax({
    url: BASE+'attendance/leave_approve/'+id, type:'POST',
    data:{}, dataType:'json',
    success: function(r){
      if(r.success){ showToast('Leave approved!','success'); setTimeout(function(){ location.reload(); },800); }
      else{ showToast(r.message,'danger'); btn.disabled=false; btn.innerHTML='<i class="bi bi-check-lg me-1"></i>Approve'; }
    }
  });
}

/* ── Reject Leave ── */
var _rejectId = null;
function openReject(id) { _rejectId=id; document.getElementById('rejectReason').value=''; new bootstrap.Modal(document.getElementById('rejectModal')).show(); }
document.getElementById('btnConfirmReject').addEventListener('click', function() {
  if (!_rejectId) return;
  var self=this; self.disabled=true;
  $.ajax({
    url: BASE+'attendance/leave_reject/'+_rejectId, type:'POST',
    data:{reason: document.getElementById('rejectReason').value}, dataType:'json',
    success: function(r){
      self.disabled=false;
      if(r.success){ bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide(); showToast('Leave rejected.','warning'); setTimeout(function(){ location.reload(); },800); }
    }
  });
});

/* ── Cancel Leave ── */
function cancelLeave(id, btn) {
  if (!confirm('Cancel this leave request?')) return;
  btn.disabled=true;
  $.ajax({
    url: BASE+'attendance/leave_cancel/'+id, type:'POST', data:{}, dataType:'json',
    success: function(r){ if(r.success){ showToast('Request cancelled.','warning'); $(btn).closest('tr').fadeOut(300); } }
  });
}

/* ── Helpers ── */
function showMsg(id, msg, type) {
  var el=document.getElementById(id);
  el.style.display='block';
  el.className='alert alert-'+type;
  el.textContent=msg;
}
function showToast(msg, type) {
  var colors={success:'#10b981',danger:'#ef4444',warning:'#f59e0b',info:'#06b6d4'};
  var t=document.createElement('div');
  t.style.cssText='position:fixed;bottom:22px;right:22px;z-index:9999;background:'+colors[type||'success']+';color:#fff;padding:12px 18px;border-radius:10px;font-weight:600;font-size:.86rem;box-shadow:0 8px 28px rgba(0,0,0,.18);font-family:var(--font);max-width:300px;transition:all .28s;transform:translateY(10px);opacity:0;';
  t.textContent=msg;
  document.body.appendChild(t);
  requestAnimationFrame(function(){ t.style.transform='translateY(0)'; t.style.opacity='1'; });
  setTimeout(function(){ t.style.transform='translateY(10px)'; t.style.opacity='0'; setTimeout(function(){ t.remove(); },300); },3500);
}
</script>

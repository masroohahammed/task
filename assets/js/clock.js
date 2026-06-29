/**
 * Techfod — Topbar Clock + Clock In/Out Widget
 */
(function() {
  'use strict';

  var BASE = (document.querySelector('meta[name="base-url"]') || {}).content || '/';
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var CSRF_PARAM = (document.querySelector('meta[name="csrf-param"]') || {}).content || 'csrf_token';
  var timeEl  = document.getElementById('tbLiveTime');
  var btnEl   = document.getElementById('tbClockBtn');
  if (!timeEl || !btnEl) return;  // not an employee layout

  function postForm(url, bodyObj, cb) {
    var parts = [];
    for (var k in bodyObj) {
      if (bodyObj.hasOwnProperty(k)) {
        parts.push(encodeURIComponent(k) + '=' + encodeURIComponent(bodyObj[k] == null ? '' : String(bodyObj[k])));
      }
    }
    if (CSRF) {
      parts.push(encodeURIComponent(CSRF_PARAM) + '=' + encodeURIComponent(CSRF));
    }
    fetch(BASE + url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: parts.join('&')
    })
      .then(function(r) { return r.json(); })
      .then(function(data) { if (typeof cb === 'function') cb(data); })
      .catch(function() { if (typeof cb === 'function') cb({ success: false, message: 'Network error' }); });
  }

  // Live time
  function tick() {
    var d = new Date(), h = d.getHours(), m = d.getMinutes(), s = d.getSeconds();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    timeEl.textContent = (h < 10 ? '0' : '') + h + ':' +
                         (m < 10 ? '0' : '') + m + ':' +
                         (s < 10 ? '0' : '') + s + ' ' + ampm;
  }
  setInterval(tick, 1000);
  tick();

  // Fetch current clock status and render button
  function loadStatus() {
    fetch(BASE + 'attendance/get_status')
      .then(function(r) { return r.json(); })
      .then(function(data) { renderBtn(data); })
      .catch(function() { renderBtn({}); });
  }

  function renderBtn(data) {
    if (data.clocked_in) {
      var sub = '';
      if (data.on_break) sub = '<span class="small text-warning ms-1">On break</span>';
      btnEl.innerHTML =
        '<button class="tb-clock-btn tb-clock-btn-out" id="tbBtnOut">' +
        '<span class="tb-clock-dot tb-clock-dot-active"></span>' +
        '<span>Since ' + (data.clock_in_time || '') + '</span>' +
        sub +
        '<i class="bi bi-stop-circle-fill ms-1"></i>' +
        '</button>';
      document.getElementById('tbBtnOut').addEventListener('click', doClockOut);

    } else if (data.clocked_out) {
      btnEl.innerHTML =
        '<div class="tb-clock-done">' +
        '<i class="bi bi-check-circle-fill me-1"></i>' +
        (data.total_hours || 0) + 'h done' +
        '</div>';

    } else {
      btnEl.innerHTML =
        '<button class="tb-clock-btn tb-clock-btn-in" id="tbBtnIn">' +
        '<i class="bi bi-play-circle-fill me-1"></i>Clock In' +
        '</button>';
      document.getElementById('tbBtnIn').addEventListener('click', doClockIn);
    }
  }

  function doClockIn() {
    var btn = document.getElementById('tbBtnIn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    postForm('attendance/clock_in', { note: '' }, function(res) {
      if (res.success) {
        showTbToast('Clocked in at ' + res.time + ' ✓', '#10b981');
        loadStatus();
      } else {
        showTbToast(res.message, '#f59e0b');
        loadStatus();
      }
    });
  }

  function doClockOut() {
    fetch(BASE + 'attendance/pre_clock_out')
      .then(function(r) { return r.json(); })
      .then(function(pre) {
        if (!pre.success) {
          if (pre.code === 'worksheet_required') {
            showTbToast('Complete daily worksheet first (Attendance page).', '#f59e0b');
            return;
          }
          if (pre.code === 'break_open') {
            showTbToast(pre.message || 'End your break first.', '#f59e0b');
            return;
          }
          showTbToast(pre.message || 'Cannot clock out.', '#f59e0b');
          return;
        }
        var modalEl = document.getElementById('tbClockOutModal');
        if (!modalEl || typeof bootstrap === 'undefined') {
          if (!confirm('Clock out now?')) return;
          finishClockOut('', '');
          return;
        }
        var hint = document.getElementById('tbCoHint');
        var earlyWrap = document.getElementById('tbCoEarlyWrap');
        var earlyReason = document.getElementById('tbCoEarlyReason');
        var noteEl = document.getElementById('tbCoNote');
        if (hint) {
          hint.textContent = pre.early_candidate
            ? 'You are before your duty end (' + (pre.duty_end || '') + '). A reason is required.'
            : 'Confirm clock-out. Add an optional note if needed.';
        }
        if (earlyWrap) earlyWrap.style.display = pre.early_candidate ? 'block' : 'none';
        if (earlyReason) earlyReason.value = '';
        if (noteEl) noteEl.value = '';

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var confirmBtn = document.getElementById('tbCoConfirm');
        if (confirmBtn) {
          confirmBtn.onclick = function() {
            var early = pre.early_candidate ? (earlyReason && earlyReason.value.trim()) : '';
            if (pre.early_candidate && !early) {
              showTbToast('Please enter a reason for early clock-out.', '#ef4444');
              return;
            }
            var note = noteEl ? noteEl.value.trim() : '';
            confirmBtn.disabled = true;
            finishClockOut(note, early, function() {
              confirmBtn.disabled = false;
              modal.hide();
            });
          };
        }
        modal.show();
      })
      .catch(function() { showTbToast('Error. Try again.', '#ef4444'); });
  }

  function finishClockOut(note, earlyReason, done) {
    postForm('attendance/clock_out', {
      note: note || '',
      early_clockout_reason: earlyReason || ''
    }, function(res) {
      if (res.success) {
        showTbToast('Clocked out · ' + res.total_hours + 'h total ✓', '#6366f1');
        loadStatus();
        if (typeof done === 'function') done();
      } else if (res.code === 'early_reason_required') {
        showTbToast(res.message, '#f59e0b');
        var ew = document.getElementById('tbCoEarlyWrap');
        if (ew) ew.style.display = 'block';
      } else {
        showTbToast(res.message, '#f59e0b');
        loadStatus();
        if (typeof done === 'function') done();
      }
    });
  }

  function showTbToast(msg, color) {
    var t = document.createElement('div');
    t.style.cssText = 'position:fixed;top:70px;right:20px;z-index:9999;' +
      'background:' + color + ';color:#fff;padding:10px 16px;' +
      'border-radius:9px;font-weight:600;font-size:.84rem;' +
      'box-shadow:0 6px 20px rgba(0,0,0,.18);' +
      'font-family:\'Plus Jakarta Sans\',sans-serif;' +
      'transition:all .28s;transform:translateY(-8px);opacity:0;';
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(function() {
      t.style.transform = 'translateY(0)';
      t.style.opacity = '1';
    });
    setTimeout(function() {
      t.style.transform = 'translateY(-8px)';
      t.style.opacity = '0';
      setTimeout(function() { t.remove(); }, 300);
    }, 3500);
  }

  loadStatus();

})();

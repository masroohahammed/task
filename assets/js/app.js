/**
 * Techfod Task System — app.js
 */
'use strict';

/* ────────────────────────────────────────────
   CSRF + Base URL from meta tags
   ──────────────────────────────────────────── */
function getMeta(name) {
  var el = document.querySelector('meta[name="' + name + '"]');
  return el ? el.getAttribute('content') : '';
}
var CSRF  = getMeta('csrf-token');
var BASE  = getMeta('base-url') || '/';

/* ────────────────────────────────────────────
   AJAX helper
   ──────────────────────────────────────────── */
function ajax(url, data, cb) {
  data.csrf_token = CSRF;
  $.ajax({
    url: BASE + url,
    type: 'POST',
    data: data,
    success: function(res) { if (typeof cb === 'function') cb(res); },
    error: function(xhr) {
      console.error('AJAX error', xhr.status, url);
      toast('Something went wrong. Please try again.', 'danger');
    }
  });
}

/* ────────────────────────────────────────────
   TOAST
   ──────────────────────────────────────────── */
function toast(msg, type) {
  type = type || 'success';
  var colors = { success:'#10b981', danger:'#ef4444', warning:'#f59e0b', info:'#06b6d4' };
  var icons  = { success:'bi-check-circle-fill', danger:'bi-exclamation-circle-fill', warning:'bi-exclamation-triangle-fill', info:'bi-info-circle-fill' };
  var id = 'toast-' + Date.now();
  var $t = $('<div id="'+id+'" style="position:fixed;bottom:22px;right:22px;z-index:9999;'+
    'background:'+colors[type]+';color:#fff;'+
    'padding:11px 18px;border-radius:10px;'+
    'display:flex;align-items:center;gap:9px;'+
    'font-size:.86rem;font-weight:600;'+
    'box-shadow:0 8px 28px rgba(0,0,0,.18);'+
    'max-width:320px;font-family:\'Plus Jakarta Sans\',sans-serif;'+
    'transform:translateY(16px);opacity:0;'+
    'transition:all .28s cubic-bezier(.4,0,.2,1);">'+
    '<i class="bi '+icons[type]+'" style="font-size:1.05rem;flex-shrink:0;"></i>'+
    '<span>'+msg+'</span></div>');
  $('body').append($t);
  requestAnimationFrame(function() {
    $t.css({ transform:'translateY(0)', opacity:1 });
  });
  setTimeout(function() {
    $t.css({ transform:'translateY(16px)', opacity:0 });
    setTimeout(function(){ $t.remove(); }, 300);
  }, 3600);
}

/* ════════════════════════════════════════════
   DOM READY
   ════════════════════════════════════════════ */
$(function() {

  /* ── Theme toggle ── */
  function applyTheme(t) {
    if (t === 'dark') {
      document.body.classList.add('dark-mode');
    } else {
      document.body.classList.remove('dark-mode');
    }
    $('#btnLight').toggleClass('active', t !== 'dark');
    $('#btnDark').toggleClass('active',  t === 'dark');
    localStorage.setItem('techfod_theme', t);
  }

  // Apply on load
  applyTheme(localStorage.getItem('techfod_theme') || 'light');

  $('#btnLight').on('click', function() { applyTheme('light'); });
  $('#btnDark').on('click',  function() { applyTheme('dark');  });

  /* ── Mobile sidebar ── */
  $('#mobileToggle').on('click', function() {
    $('#sidebar').toggleClass('open');
    $('#sbOverlay').toggleClass('show');
  });
  $('#sbOverlay').on('click', function() {
    $('#sidebar').removeClass('open');
    $(this).removeClass('show');
  });

  /* ── Auto-dismiss alerts ── */
  setTimeout(function() {
    $('.alert.alert-success, .alert.alert-danger').fadeOut(600);
  }, 5000);

  /* ── Member selector ── */
  $(document).on('click', '.member-option', function() {
    $(this).toggleClass('selected');
    $(this).find('input[type="checkbox"]').prop('checked', $(this).hasClass('selected'));
  });

  /* ── Filter bar (projects list) ── */
  $('#filterStatus, #filterPriority').on('change', applyFilters);
  $('#searchProjects, #searchTickets').on('input', applyFilters);
  function applyFilters() {
    var status   = $('#filterStatus').val() || '';
    var priority = $('#filterPriority').val() || '';
    var search   = ($('#searchProjects').val() || $('#searchTickets').val() || '').toLowerCase();
    $('#projectsGrid .project-item, #ticketsTable tbody tr, #issuesTable tbody tr').each(function() {
      var s  = ($(this).data('status')   || '').toString();
      var p  = ($(this).data('priority') || '').toString();
      var tx = $(this).text().toLowerCase();
      var ok = (!status || s === status) && (!priority || p === priority) && (!search || tx.includes(search));
      $(this).toggle(ok);
    });
  }

  /* ── Grid/List view toggle ── */
  $('#viewGrid').on('click', function() {
    $(this).addClass('active'); $('#viewList').removeClass('active');
    $('#projectsGrid').removeClass('list-view');
  });
  $('#viewList').on('click', function() {
    $(this).addClass('active'); $('#viewGrid').removeClass('active');
    $('#projectsGrid').addClass('list-view');
  });

  /* ── Role tabs (users page) ── */
  $(document).on('click', '.role-tab', function() {
    $('.role-tab').removeClass('active');
    $(this).addClass('active');
    var role = $(this).data('role') || '';
    $('#usersTable tbody tr').each(function() {
      $(this).toggle(!role || $(this).data('role') === role);
    });
  });

  /* ── Issues filter (list page) ── */
  $('#searchIssues').on('input', applyFilters);
  $('#filterProject').on('change', function() {
    var pid = $(this).val() || '';
    $('#issuesTable tbody tr').each(function() {
      $(this).toggle(!pid || String($(this).data('project')) === pid);
    });
  });

  /* ════════════════════════════════
     KANBAN — TASK BOARD
     ════════════════════════════════ */
  if ($('#kanbanBoard').length) {
    initTaskKanban();

    function initTaskKanban() {
      $('.sortable-list').sortable({
        connectWith: '.sortable-list',
        placeholder: 'ui-sortable-placeholder',
        tolerance: 'pointer',
        revert: 160,
        start: function(e, ui) {
          ui.placeholder.height(ui.item.outerHeight());
        },
        update: function(e, ui) {
          if (this !== ui.item.parent()[0]) return;
          var taskId   = ui.item.data('task-id');
          var newSt    = $(this).data('status');
          var position = ui.item.index();
          ajax('tasks/update_status', { task_id: taskId, status: newSt, position: position }, function(res) {
            if (res.success) {
              updateColCounts();
              toast('Task moved to ' + newSt.replace('_', ' '), 'success');
            }
          });
        },
        over: function() { $(this).addClass('drag-over'); },
        out:  function() { $(this).removeClass('drag-over'); }
      }).disableSelection();
    }

    function updateColCounts() {
      ['todo','in_progress','testing','done'].forEach(function(s) {
        $('#count-' + s).text($('#col-' + s + ' .kanban-card').length);
      });
    }

    // Column add-card buttons preset status
    $('.btn-add-card').on('click', function() {
      $('#newTaskStatus').val($(this).data('status') || 'todo');
    });

    // Save new task
    $('#saveTask').on('click', function() {
      var $form = $('#createTaskForm');
      var data  = {};
      $form.serializeArray().forEach(function(f) { data[f.name] = f.value; });
      if (!data.title || !data.title.trim()) {
        toast('Task title is required', 'warning'); return;
      }
      var $btn = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating…');
      ajax('tasks/store', data, function(res) {
        $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Create Task');
        if (res.success) {
          $('#createTaskModal').modal('hide');
          $form[0].reset();
          toast('Task created!', 'success');
          setTimeout(function() { location.reload(); }, 800);
        } else {
          toast(res.errors || 'Failed to create task', 'danger');
        }
      });
    });

    // Kanban filters
    $('#filterAssignee, #filterPriority').on('change', filterKanban);
    $('#filterSearch').on('input', filterKanban);
    function filterKanban() {
      var a = $('#filterAssignee').val() || '';
      var p = $('#filterPriority').val() || '';
      var s = ($('#filterSearch').val() || '').toLowerCase();
      $('.kanban-card').each(function() {
        var ok = (!a || String($(this).data('assignee')) === a)
               && (!p || $(this).data('priority') === p)
               && (!s || $(this).text().toLowerCase().includes(s));
        $(this).toggle(ok);
      });
      updateColCounts();
    }
  }

  /* ════════════════════════════════
     KANBAN — ISSUE BOARD
     ════════════════════════════════ */
  if ($('#issueBoardKanban').length) {
    $('.issue-sortable').sortable({
      connectWith: '.issue-sortable',
      placeholder: 'ui-sortable-placeholder',
      tolerance: 'pointer',
      revert: 160,
      update: function(e, ui) {
        if (this !== ui.item.parent()[0]) return;
        ajax('issues/update_status', {
          issue_id: ui.item.data('issue-id'),
          status:   $(this).data('status'),
          position: ui.item.index()
        }, function(res) {
          if (res.success) toast('Issue moved', 'success');
        });
      },
      over: function() { $(this).addClass('drag-over'); },
      out:  function() { $(this).removeClass('drag-over'); }
    }).disableSelection();

    // Issue board filters
    $('#filterPriority, #filterSeverity, #filterAssignee').on('change', function() {
      var pr = $('#filterPriority').val() || '';
      var sv = $('#filterSeverity').val() || '';
      var as = $('#filterAssignee').val() || '';
      $('.issue-card').each(function() {
        var ok = (!pr || $(this).data('priority') === pr)
               && (!sv || $(this).data('severity') === sv)
               && (!as || String($(this).data('assignee')) === as);
        $(this).toggle(ok);
      });
    });
  }

  /* ════════════════════════════════
     TASK DETAIL PAGE
     ════════════════════════════════ */

  // Progress range display
  $('#progressRange').on('input', function() {
    $('#progressValue').text($(this).val() + '%');
  });

  // Daily update submit
  $('#saveDailyUpdate').on('click', function() {
    var work = $('#workDone').val().trim();
    if (!work) { toast('Please describe your work', 'warning'); return; }
    ajax('tasks/daily_update', {
      task_id:     $(this).data('task-id'),
      work_done:   work,
      progress:    $('#progressRange').val(),
      blockers:    $('#blockers').val(),
      hours_spent: $('#hoursSpent').val()
    }, function(res) {
      if (res.success) { toast('Update saved!', 'success'); setTimeout(function(){ location.reload(); }, 800); }
    });
  });

  // Task comment
  $('#submitTaskComment').on('click', function() {
    var comment = $('#taskComment').val().trim();
    if (!comment) return;
    ajax('tasks/add_comment', { task_id: $(this).data('task-id'), comment: comment }, function(res) {
      if (res.success) { toast('Comment added', 'success'); setTimeout(function(){ location.reload(); }, 600); }
    });
  });

  /* ════════════════════════════════
     ISSUE DETAIL PAGE
     ════════════════════════════════ */
  $('#submitIssueComment').on('click', function() {
    var comment = $('#issueComment').val().trim();
    if (!comment) return;
    ajax('issues/add_comment', { issue_id: $(this).data('issue-id'), comment: comment }, function(res) {
      if (res.success) { toast('Comment added', 'success'); setTimeout(function(){ location.reload(); }, 600); }
    });
  });

  $('#updateIssueStatus').on('click', function() {
    ajax('issues/update_status', {
      issue_id: $(this).data('issue-id'),
      status:   $('#issueStatusSelect').val(),
      position: 0
    }, function(res) {
      if (res.success) { toast('Status updated', 'success'); setTimeout(function(){ location.reload(); }, 600); }
    });
  });

  /* ════════════════════════════════
     TICKET DETAIL PAGE
     ════════════════════════════════ */
  $('#submitComment').on('click', function() {
    var comment = $('#newComment').val().trim();
    if (!comment) return;
    ajax('tickets/add_comment', { ticket_id: $(this).data('ticket-id'), comment: comment }, function(res) {
      if (res.success) { toast('Reply sent', 'success'); setTimeout(function(){ location.reload(); }, 600); }
    });
  });

  $('#updateTicketStatus').on('click', function() {
    ajax('tickets/update_status', {
      ticket_id:   $(this).data('ticket-id'),
      status:      $('#ticketStatus').val(),
      assigned_to: $('#ticketAssignee').val()
    }, function(res) {
      if (res.success) { toast('Ticket updated', 'success'); setTimeout(function(){ location.reload(); }, 600); }
    });
  });

  /* ════════════════════════════════
     ADMIN DASHBOARD — DONUT CHART
     ════════════════════════════════ */
  var $dn = $('#taskDonut');
  if ($dn.length) {
    var vals = [
      { v: parseInt($dn.data('todo'))     || 0, c: '#94a3b8' },
      { v: parseInt($dn.data('progress')) || 0, c: '#6366f1' },
      { v: parseInt($dn.data('testing'))  || 0, c: '#06b6d4' },
      { v: parseInt($dn.data('done'))     || 0, c: '#10b981' }
    ];
    var total = vals.reduce(function(s,d){ return s+d.v; }, 0) || 1;
    var angle = -Math.PI / 2;
    var cx = 80, cy = 80, r = 64, paths = '';
    vals.forEach(function(d) {
      if (!d.v) return;
      var sweep = d.v / total * 2 * Math.PI;
      var x1 = cx + r * Math.cos(angle);
      var y1 = cy + r * Math.sin(angle);
      var x2 = cx + r * Math.cos(angle + sweep);
      var y2 = cy + r * Math.sin(angle + sweep);
      paths += '<path d="M'+cx+','+cy+' L'+x1+','+y1+' A'+r+','+r+' 0 '+(sweep>Math.PI?1:0)+',1 '+x2+','+y2+' Z" fill="'+d.c+'" opacity=".9"/>';
      angle += sweep;
    });
    $dn.html('<svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg">'+paths+'<circle cx="'+cx+'" cy="'+cy+'" r="42" fill="var(--card-bg)"/></svg>');
  }

  /* ════════════════════════════════
     TOOLTIPS (Bootstrap)
     ════════════════════════════════ */
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    new bootstrap.Tooltip(el, { trigger:'hover' });
  });

}); // end DOM ready

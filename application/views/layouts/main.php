<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? html_escape($page_title).' — ' : '' ?>Techfod Task System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body id="appBody">

<!-- ╔══════════════════════════════════════╗
     ║  SIDEBAR — slim 68px icon rail       ║
     ╚══════════════════════════════════════╝ -->
<aside id="sidebar">

  <!-- Logo -->
  <div class="sb-logo-wrap">
    <div class="sb-logo">
      <i class="bi bi-grid-3x3-gap-fill"></i>
    </div>
  </div>

  <!-- Main nav icons -->
  <nav class="sb-nav">

    <a href="<?= site_url('dashboard') ?>"
       class="sb-link<?= uri_string()==='dashboard' ? ' active' : '' ?>"
       title="Dashboard">
      <i class="bi bi-house-door-fill"></i>
    </a>

    <a href="<?= site_url('projects') ?>"
       class="sb-link<?= strpos(uri_string(),'projects')!==false ? ' active' : '' ?>"
       title="Projects">
      <i class="bi bi-folder2-open"></i>
    </a>

    <?php if (!has_role('client')): ?>
    <a href="<?= site_url('issues') ?>"
       class="sb-link<?= strpos(uri_string(),'issues')!==false ? ' active' : '' ?>"
       title="Issues">
      <i class="bi bi-bug"></i>
      <?php $iss_count = $this->Issue_model->count_by_status('open'); if ($iss_count > 0): ?>
      <span class="sb-dot"></span>
      <?php endif; ?>
    </a>
    <?php endif; ?>

    <a href="<?= site_url('tickets') ?>"
       class="sb-link<?= strpos(uri_string(),'tickets')!==false ? ' active' : '' ?>"
       title="Tickets">
      <i class="bi bi-ticket-detailed"></i>
      <?php
        $tkt_cnt = has_role('client')
          ? $this->Ticket_model->count_by_client($current_user->client_id, 'open')
          : $this->Ticket_model->count_by_status('open');
        if ($tkt_cnt > 0):
      ?>
      <span class="sb-dot"></span>
      <?php endif; ?>
    </a>

    <?php if (is_client_admin()): ?>
    <a href="<?= site_url('client-portal/users') ?>"
       class="sb-link<?= strpos(uri_string(),'client-portal')!==false ? ' active' : '' ?>"
       title="Team Members">
      <i class="bi bi-people"></i>
    </a>
    <?php endif; ?>

    <?php if (has_permission('can_view_client_details')): ?>
    <a href="<?= site_url('clients') ?>"
       class="sb-link<?= strpos(uri_string(),'clients')!==false ? ' active' : '' ?>"
       title="Clients">
      <i class="bi bi-buildings"></i>
    </a>
    <?php endif; ?>

    <?php if (has_role('admin')): ?>
    <a href="<?= site_url('users') ?>"
       class="sb-link<?= strpos(uri_string(),'users')!==false ? ' active' : '' ?>"
       title="Users">
      <i class="bi bi-people"></i>
    </a>
    <?php endif; ?>

    <a href="<?= site_url('notifications') ?>"
       class="sb-link<?= strpos(uri_string(),'notifications')!==false ? ' active' : '' ?>"
       title="Notifications">
      <i class="bi bi-bell"></i>
      <?php if ($unread_notifications > 0): ?>
      <span class="sb-dot"></span>
      <?php endif; ?>
    </a>

    <?php if (!has_role('client')): ?>
    <a href="<?= site_url('attendance') ?>"
       class="sb-link<?= strpos(uri_string(),'attendance')!==false ? ' active' : '' ?>"
       title="Attendance">
      <i class="bi bi-person-check"></i>
    </a>
    <a href="<?= site_url('worksheet') ?>"
       class="sb-link<?= strpos(uri_string(),'worksheet')!==false ? ' active' : '' ?>"
       title="Daily Worksheet">
      <i class="bi bi-journal-check"></i>
    </a>
    <?php endif; ?>

    <?php if (has_role(['admin','hr'])): ?>
    <a href="<?= site_url('hr') ?>"
       class="sb-link<?= strpos(uri_string(),'hr')!==false ? ' active' : '' ?>"
       title="HR & Employees">
      <i class="bi bi-people-fill"></i>
    </a>
    <?php endif; ?>

    <?php if (has_role(['admin','project_manager'])): ?>
    <a href="<?= site_url('reports') ?>"
       class="sb-link<?= strpos(uri_string(),'reports')!==false ? ' active' : '' ?>"
       title="Reports">
      <i class="bi bi-bar-chart-line"></i>
    </a>
    <?php endif; ?>

  </nav>

  <!-- Bottom nav -->
  <div class="sb-bottom">
    <a href="<?= site_url('profile') ?>"
       class="sb-link<?= uri_string()==='profile' ? ' active' : '' ?>"
       title="My Profile">
      <i class="bi bi-person-circle"></i>
    </a>
    <a href="<?= site_url('logout') ?>" class="sb-link sb-link-danger" title="Logout">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>

</aside>

<!-- ╔══════════════════════════════════════╗
     ║  MAIN WRAPPER                        ║
     ╚══════════════════════════════════════╝ -->
<div id="mainWrap">

  <!-- ╔══════════════════════════════════════╗
       ║  TOPBAR                              ║
       ╚══════════════════════════════════════╝ -->
  <header id="topbar">

    <!-- LEFT: mobile burger + tab nav -->
    <div class="tb-left">
      <button class="tb-burger d-xl-none" id="mobileToggle">
        <i class="bi bi-list"></i>
      </button>

      <nav class="tb-tabs d-none d-md-flex">
        <a href="<?= site_url('dashboard') ?>"
           class="tb-tab<?= uri_string()==='dashboard' ? ' tb-tab-active' : '' ?>">
          <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="<?= site_url('projects') ?>"
           class="tb-tab<?= strpos(uri_string(),'projects')!==false ? ' tb-tab-active' : '' ?>">
          <i class="bi bi-diagram-3"></i> Workflows
        </a>
        <a href="<?= site_url('reports') ?>"
           class="tb-tab<?= strpos(uri_string(),'reports')!==false ? ' tb-tab-active' : '' ?>">
          <i class="bi bi-share"></i> Integrations
        </a>
      </nav>
    </div>

    <!-- CENTER: search -->
    <div class="tb-center">
      <div class="tb-search">
        <i class="bi bi-search tb-search-ico"></i>
        <input type="text" placeholder="Search or type command" id="globalSearch">
      </div>
    </div>

    <!-- RIGHT: controls -->
    <div class="tb-right">

      <!-- Light / Dark pill -->
      <div class="theme-pill">
        <button class="theme-opt" id="btnLight">
          <i class="bi bi-brightness-high-fill"></i>
          <span>Light</span>
        </button>
        <button class="theme-opt" id="btnDark">
          <i class="bi bi-moon-stars-fill"></i>
          <span>Dark</span>
        </button>
      </div>

      <!-- Bell -->
      <div class="dropdown">
        <button class="tb-icon-btn" data-bs-toggle="dropdown" data-bs-offset="0,8">
          <i class="bi bi-bell"></i>
          <?php if ($unread_notifications > 0): ?>
          <span class="tb-badge"><?= $unread_notifications ?></span>
          <?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end notif-menu">
          <div class="notif-menu-head">
            <span>Notifications</span>
            <?php if ($unread_notifications > 0): ?>
            <a href="<?= site_url('notifications/mark_all_read') ?>" class="notif-mark-all">Mark all read</a>
            <?php endif; ?>
          </div>
          <div class="notif-menu-body">
            <?php if (empty($notifications)): ?>
            <div class="notif-empty">
              <i class="bi bi-bell-slash"></i>
              <p>You're all caught up!</p>
            </div>
            <?php else: foreach ($notifications as $n): ?>
            <a class="notif-row<?= !$n->is_read ? ' notif-unread' : '' ?>"
               href="<?= notification_link_url($n) ?>">
              <span class="notif-ico notif-ico-<?= notification_icon_color($n->type) ?>">
                <i class="bi <?= notification_icon_bi($n->type) ?>"></i>
              </span>
              <div class="notif-row-text">
                <div class="notif-row-title"><?= html_escape($n->title) ?></div>
                <div class="notif-row-time"><?= time_ago($n->created_at) ?></div>
              </div>
            </a>
            <?php endforeach; endif; ?>
          </div>
          <div class="notif-menu-foot">
            <a href="<?= site_url('notifications') ?>">View all notifications</a>
          </div>
        </div>
      </div>

      <!-- Gear / settings -->
      <a href="<?= site_url('profile') ?>" class="tb-icon-btn" title="Settings">
        <i class="bi bi-gear"></i>
      </a>


      <!-- Clock widget -->
      <?php if (!has_role('client')): ?>
      <div class="tb-clock-wrap d-none d-xl-flex" id="tbClockWrap">
        <div class="tb-live-time" id="tbLiveTime"></div>
        <div id="tbClockBtn"></div>
      </div>
      <?php endif; ?>

      <!-- Add new board -->
      <?php if (has_permission('can_create_project')): ?>
      <a href="<?= site_url('projects/create') ?>" class="tb-add-btn">
        <i class="bi bi-plus-lg"></i>
        <span class="d-none d-sm-inline">Add new board</span>
      </a>
      <?php endif; ?>

      <!-- Avatar dropdown -->
      <div class="dropdown">
        <button class="tb-avatar-btn" data-bs-toggle="dropdown" data-bs-offset="0,8">
          <?= user_avatar($current_user->first_name.' '.$current_user->last_name, $current_user->avatar, 34) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end tb-user-menu">
          <li class="tb-user-info">
            <div class="tb-user-name"><?= html_escape($current_user->first_name.' '.$current_user->last_name) ?></div>
            <div class="tb-user-role"><?= html_escape($current_user->role_name) ?></div>
          </li>
          <li><a class="dropdown-item" href="<?= site_url('profile') ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
          <li><a class="dropdown-item" href="<?= site_url('notifications') ?>"><i class="bi bi-bell me-2"></i>Notifications</a></li>
          <li><hr class="dropdown-divider m-0"></li>
          <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>

    </div><!-- /tb-right -->
  </header>

  <!-- ╔══════════════════════════════════════╗
       ║  PAGE CONTENT                        ║
       ╚══════════════════════════════════════╝ -->
  <main id="pageContent">

    <!-- Flash alerts -->
    <?php if ($this->session->flashdata('success')): ?>
    <div class="flash-wrap">
      <div class="alert alert-success alert-dismissible fade show mb-0">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
    <div class="flash-wrap">
      <div class="alert alert-danger alert-dismissible fade show mb-0">
        <i class="bi bi-exclamation-circle-fill me-2"></i>
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Injected view -->
    <?= $content ?>

  </main>
</div><!-- /mainWrap -->

<!-- Mobile overlay -->
<div id="sbOverlay"></div>

<?php if (!has_role('client')): ?>
<div class="modal fade" id="tbClockOutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Clock out</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3" id="tbCoHint"></p>
        <div class="mb-3" id="tbCoEarlyWrap" style="display:none">
          <label class="form-label fw-600">Reason for early clock-out <span class="text-danger">*</span></label>
          <textarea class="form-control" id="tbCoEarlyReason" rows="2" placeholder="e.g. medical appointment, completed tasks early"></textarea>
        </div>
        <div class="mb-0">
          <label class="form-label fw-600">Note <span class="text-muted fw-400">(optional)</span></label>
          <textarea class="form-control" id="tbCoNote" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="tbCoConfirm">Complete clock-out</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ── Scripts ── -->
<meta name="csrf-token" content="<?= $this->security->get_csrf_hash() ?>">
<meta name="csrf-param" content="<?= $this->security->get_csrf_token_name() ?>">
<meta name="base-url"   content="<?= base_url() ?>">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<script src="<?= base_url('assets/js/clock.js') ?>"></script>
<?php $this->load->view('layouts/chat_widget'); ?>
</body>
</html>

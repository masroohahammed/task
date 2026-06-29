<div class="page-header">
  <div>
    <h1 class="page-title">Notifications</h1>
    <p class="page-subtitle">All your recent notifications</p>
  </div>
  <div class="page-actions">
    <a href="<?= site_url('notifications/mark_all_read') ?>" class="btn btn-ghost btn-sm">
      <i class="bi bi-check-all me-1"></i> Mark all read
    </a>
  </div>
</div>

<div class="card card-modern">
  <div class="card-body p-0">
    <?php if (empty($notifications)): ?>
    <div class="empty-state py-5">
      <i class="bi bi-bell-slash"></i>
      <h5>You're all caught up!</h5>
      <p>No notifications to show right now.</p>
    </div>
    <?php else: ?>
    <?php foreach ($notifications as $n): ?>
    <a class="notif-full-row<?= !$n->is_read ? ' notif-full-unread' : '' ?>"
       href="<?= notification_link_url($n) ?>">
      <span class="notif-ico notif-ico-<?= notification_icon_color($n->type) ?>">
        <i class="bi <?= notification_icon_bi($n->type) ?>"></i>
      </span>
      <div class="notif-full-body">
        <div class="notif-full-title"><?= html_escape($n->title) ?></div>
        <?php if ($n->message): ?>
        <div class="notif-full-msg"><?= html_escape($n->message) ?></div>
        <?php endif; ?>
      </div>
      <div class="notif-full-time">
        <?php if (!$n->is_read): ?>
        <span class="notif-unread-dot"></span>
        <?php endif; ?>
        <span class="small text-muted"><?= time_ago($n->created_at) ?></span>
      </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<style>
.notif-full-row {
  display:flex; align-items:center; gap:14px;
  padding:14px 20px; border-bottom:1px solid var(--border-lt);
  color:var(--text-1); text-decoration:none;
  transition:background var(--tr);
}
.notif-full-row:last-child { border-bottom:none; }
.notif-full-row:hover { background:var(--primary-soft); text-decoration:none; color:var(--text-1); }
.notif-full-unread { background:rgba(99,102,241,.04); }
.notif-full-body { flex:1; }
.notif-full-title { font-weight:600; font-size:.88rem; }
.notif-full-msg   { font-size:.8rem; color:var(--text-2); margin-top:2px; }
.notif-full-time  { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.notif-unread-dot { width:8px; height:8px; background:var(--primary); border-radius:50%; }
</style>

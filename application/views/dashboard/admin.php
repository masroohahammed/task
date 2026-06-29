<?php
$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
$greet_emoji = $hour < 12 ? '☀️' : ($hour < 17 ? '🌤️' : '🌙');
$tips = ['Have a nice day! 😊','Stay focused! 🚀','Make it count! ✨','Keep going! 💪','You got this! 🌟'];
$tip  = $tips[(int)date('N') % count($tips)];
?>
<div class="dash-hero">
  <div>
    <h1 class="dash-greeting"><?= $greeting ?>, <?= html_escape($current_user->first_name) ?>! <?= $greet_emoji ?></h1>
    <p class="dash-subtitle"><?= $tip ?> Today is <strong><?= date('l, F j, Y') ?></strong></p>
  </div>
  <div class="dash-hero-actions">
    <?php if(has_permission('can_create_project')): ?>
    <a href="<?= site_url('projects/create') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Project</a>
    <?php endif; ?>
    <a href="<?= site_url('attendance') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-clock me-1"></i>Attendance</a>
  </div>
</div>

<?php if(!empty($birthdays_today)): ?>
<div class="birthday-banner">
  <span class="bday-cake">🎂</span>
  <div>
    <div class="fw-700 small">Birthday Today!</div>
    <div class="small text-muted">
      <?php foreach($birthdays_today as $b): ?><?= html_escape($b->first_name.' '.$b->last_name) ?><?php endforeach; ?>
    </div>
  </div>
  <span class="ms-auto small fw-600 text-warning">🎉 Send a wish!</span>
</div>
<?php endif; ?>

<div class="stats-grid">
  <div class="stat-card stat-primary"><div class="stat-icon"><i class="bi bi-folder2-open"></i></div><div class="stat-body"><div class="stat-value"><?= $total_projects ?></div><div class="stat-label">Projects</div><div class="stat-sub text-success"><?= $active_projects ?> active</div></div></div>
  <div class="stat-card stat-indigo"><div class="stat-icon"><i class="bi bi-check2-square"></i></div><div class="stat-body"><div class="stat-value"><?= $total_tasks ?></div><div class="stat-label">Tasks</div><div class="stat-sub"><?= $task_stats['in_progress']??0 ?> in progress</div></div></div>
  <div class="stat-card stat-danger"><div class="stat-icon"><i class="bi bi-bug"></i></div><div class="stat-body"><div class="stat-value"><?= $open_issues ?></div><div class="stat-label">Open Issues</div></div></div>
  <div class="stat-card stat-warning"><div class="stat-icon"><i class="bi bi-ticket-detailed"></i></div><div class="stat-body"><div class="stat-value"><?= $open_tickets ?></div><div class="stat-label">Tickets</div></div></div>
  <div class="stat-card stat-teal"><div class="stat-icon"><i class="bi bi-buildings"></i></div><div class="stat-body"><div class="stat-value"><?= $total_clients ?></div><div class="stat-label">Clients</div></div></div>
  <div class="stat-card stat-success"><div class="stat-icon"><i class="bi bi-wifi"></i></div><div class="stat-body"><div class="stat-value"><?= count($present_today) ?></div><div class="stat-label">Present Today</div><div class="stat-sub"><?= $total_users ?> total</div></div></div>
</div>

<div class="row g-4 mt-1">
  <div class="col-xl-4">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-people-fill me-2 text-success"></i>Staff Present</h6>
        <span class="badge bg-success"><?= count($present_today) ?></span>
      </div>
      <div class="card-body p-0" style="max-height:340px;overflow-y:auto">
        <?php if(empty($present_today)): ?><div class="empty-state py-4"><i class="bi bi-person-slash"></i><p>No one in yet</p></div>
        <?php else: foreach($present_today as $p): ?>
        <div class="presence-item">
          <div style="position:relative;flex-shrink:0">
            <?= user_avatar($p->first_name.' '.$p->last_name,$p->avatar??null,36) ?>
            <span class="pdot <?= $p->clock_out?'pdot-out':($p->online_status==='online'?'pdot-on':'pdot-idle') ?>"></span>
          </div>
          <div style="flex:1;min-width:0">
            <div class="small fw-600 text-truncate"><?= html_escape($p->first_name.' '.$p->last_name) ?></div>
            <div class="small text-muted"><?= date('h:i A',strtotime($p->clock_in)) ?><?= $p->clock_out?' → '.date('h:i A',strtotime($p->clock_out)):'<span class="text-success ms-1">● Active</span>' ?></div>
          </div>
          <?= status_badge($p->att_status) ?>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-folder2-open me-2 text-primary"></i>Recent Projects</h6>
        <a href="<?= site_url('projects') ?>" class="btn btn-sm btn-ghost">View all</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($recent_projects)): ?><div class="empty-state py-4"><i class="bi bi-folder-x"></i><p>No projects</p></div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover table-modern">
            <thead><tr><th>Project</th><th>Status</th><th>Progress</th><th>Team</th></tr></thead>
            <tbody>
            <?php foreach($recent_projects as $p): $mems=$this->Project_model->get_members($p->id); ?>
            <tr>
              <td><a href="<?= site_url('projects/view/'.$p->id) ?>" class="fw-600 text-dark link-hover"><?= html_escape($p->name) ?></a><div class="small text-muted"><?= html_escape($p->company_name??'Internal') ?></div></td>
              <td><?= status_badge($p->status) ?></td>
              <td><div class="d-flex align-items-center gap-2"><div class="progress flex-1" style="height:5px;min-width:60px"><div class="progress-bar bg-primary" style="width:<?= $p->progress ?>%"></div></div><span class="small text-muted"><?= $p->progress ?>%</span></div></td>
              <td><div class="avatar-group"><?php foreach(array_slice($mems,0,3) as $m): ?><?= user_avatar($m->first_name.' '.$m->last_name,$m->avatar??null,26) ?><?php endforeach; ?><?php if(count($mems)>3): ?><div class="avatar-more">+<?= count($mems)-3 ?></div><?php endif; ?></div></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern"><h6 class="card-title-modern"><i class="bi bi-bug me-2 text-danger"></i>Recent Issues</h6><a href="<?= site_url('issues') ?>" class="btn btn-sm btn-ghost">View all</a></div>
      <div class="card-body p-0">
        <?php if(empty($recent_issues)): ?><div class="empty-state py-3"><i class="bi bi-check-circle"></i><p>All clear!</p></div>
        <?php else: foreach($recent_issues as $i): ?>
        <div class="list-item"><div class="list-item-icon bg-danger-soft text-danger"><i class="bi bi-bug"></i></div><div class="list-item-body"><a href="<?= site_url('issues/view/'.$i->id) ?>" class="list-item-title"><?= html_escape($i->title) ?></a><div class="list-item-meta"><?= status_badge($i->status) ?><?= priority_badge($i->priority) ?></div></div><div class="list-item-time"><?= time_ago($i->created_at) ?></div></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-6">
    <div class="card card-modern">
      <div class="card-header-modern"><h6 class="card-title-modern"><i class="bi bi-clock-history me-2 text-teal"></i>Recent Activity</h6></div>
      <div class="activity-timeline">
        <?php if(empty($recent_activities)): ?><div class="empty-state py-3"><i class="bi bi-clock"></i><p>No activity</p></div>
        <?php else: foreach($recent_activities as $a): ?>
        <div class="activity-item"><div class="activity-dot"></div><div class="activity-content"><span class="activity-user fw-600"><?= html_escape($a->first_name.' '.$a->last_name) ?></span><span class="activity-action"> <?= html_escape($a->action) ?></span></div><div class="activity-time"><?= time_ago($a->created_at) ?></div></div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.dash-hero{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;gap:16px;flex-wrap:wrap;}
.dash-greeting{font-size:1.55rem;font-weight:800;color:var(--text-1);margin:0;letter-spacing:-.4px;}
.dash-subtitle{color:var(--text-2);margin:4px 0 0;font-size:.87rem;}
.dash-hero-actions{display:flex;gap:8px;flex-shrink:0;}
.birthday-banner{display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,rgba(245,158,11,.1),rgba(239,68,68,.07));border:1px solid rgba(245,158,11,.2);border-radius:var(--radius);padding:12px 16px;margin-bottom:18px;flex-wrap:wrap;}
.bday-cake{font-size:1.6rem;flex-shrink:0;}
.presence-item{display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid var(--border-lt);}
.presence-item:last-child{border-bottom:none;}
.pdot{position:absolute;bottom:0;right:0;width:10px;height:10px;border-radius:50%;border:2px solid var(--card-bg);}
.pdot-on{background:#10b981;} .pdot-idle{background:#f59e0b;} .pdot-out{background:#94a3b8;}
</style>

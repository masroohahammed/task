<!-- ═══ CHAT WIDGET ═══ -->
<div id="cw-btn" onclick="CW.toggle()">
  <i class="bi bi-chat-dots-fill"></i>
  <span id="cw-badge" style="display:none">0</span>
</div>

<div id="cw-panel" style="display:none">

  <!-- Header -->
  <div id="cw-header">
    <div id="cw-header-left">
      <button id="cw-back" onclick="CW.back()" style="display:none">
        <i class="bi bi-arrow-left"></i>
      </button>
      <span id="cw-title">Messages</span>
    </div>
    <div id="cw-header-right">
      <button class="cw-hbtn" onclick="CW.newGroup()" title="New group"><i class="bi bi-people-fill"></i></button>
      <button class="cw-hbtn" onclick="CW.hide()" title="Close"><i class="bi bi-x-lg"></i></button>
    </div>
  </div>

  <!-- View: People list -->
  <div id="cw-view-list">
    <div id="cw-search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" id="cw-search" placeholder="Search people or chats...">
    </div>
    <div id="cw-subtitle">Team</div>
    <div id="cw-people"></div>
  </div>

  <!-- View: New group (name + pick members) -->
  <div id="cw-view-new-group" style="display:none">
    <div class="cw-ng-head">
      <span class="cw-ng-title">New group chat</span>
      <span class="cw-ng-hint">Choose a name and who to include.</span>
    </div>
    <input type="text" id="cw-ng-name" class="cw-ng-input" placeholder="Group name" maxlength="120" autocomplete="off">
    <div class="cw-ng-search-wrap">
      <i class="bi bi-search"></i>
      <input type="text" id="cw-ng-search" class="cw-ng-search" placeholder="Filter people...">
    </div>
    <div id="cw-ng-members" class="cw-ng-members"></div>
    <div class="cw-ng-footer">
      <button type="button" class="cw-ng-btn cw-ng-btn-ghost" id="cw-ng-cancel">Cancel</button>
      <button type="button" class="cw-ng-btn cw-ng-btn-primary" id="cw-ng-create">Create group</button>
    </div>
  </div>

  <!-- View: Conversation -->
  <div id="cw-view-conv" style="display:none">
    <div id="cw-group-bar" style="display:none">
      <div class="cw-group-bar-inner">
        <span id="cw-group-meta" class="cw-group-meta"></span>
        <button type="button" class="cw-group-add-btn" id="cw-btn-add-members" title="Add people to this group">
          <i class="bi bi-person-plus"></i> Add members
        </button>
      </div>
    </div>

    <!-- Date sessions bar (scroll-to-top history) -->
    <div id="cw-sessions-bar" style="display:none">
      <div id="cw-sessions-label"><i class="bi bi-calendar3 me-1"></i>Load older chats:</div>
      <div id="cw-session-dates"></div>
    </div>

    <!-- Loading indicator -->
    <div id="cw-loading" style="display:none">
      <span class="cw-dots"><span></span><span></span><span></span></span>
      <span class="cw-loading-text">Loading...</span>
    </div>

    <!-- Messages -->
    <div id="cw-messages"></div>

    <!-- Typing indicator -->
    <div id="cw-typing" style="display:none">
      <span class="cw-dots"><span></span><span></span><span></span></span>
      <span id="cw-typing-name"></span> is typing
    </div>

    <!-- Input -->
    <div id="cw-input-row">
      <input type="text" id="cw-input" placeholder="Type a message..." maxlength="2000">
      <button id="cw-send"><i class="bi bi-send-fill"></i></button>
    </div>
  </div>

  <!-- Modal: add members to existing group -->
  <div id="cw-add-overlay" class="cw-modal-overlay" style="display:none" aria-hidden="true">
    <div class="cw-modal" role="dialog" aria-labelledby="cw-add-title">
      <div class="cw-modal-head">
        <span id="cw-add-title" class="cw-modal-title">Add members</span>
        <button type="button" class="cw-modal-close" id="cw-add-close" aria-label="Close">&times;</button>
      </div>
      <div class="cw-modal-body">
        <div class="cw-ng-search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" id="cw-add-search" class="cw-ng-search" placeholder="Filter people...">
        </div>
        <div id="cw-add-members" class="cw-ng-members cw-ng-members-tall"></div>
      </div>
      <div class="cw-modal-foot">
        <button type="button" class="cw-ng-btn cw-ng-btn-ghost" id="cw-add-cancel">Cancel</button>
        <button type="button" class="cw-ng-btn cw-ng-btn-primary" id="cw-add-confirm">Add selected</button>
      </div>
    </div>
  </div>

</div>

<style>
/* ── Toggle Button ── */
#cw-btn {
  position:fixed; bottom:24px; right:24px; z-index:9980;
  width:54px; height:54px;
  background:var(--primary);
  color:#fff; border-radius:50%; border:none;
  display:flex; align-items:center; justify-content:center;
  font-size:1.35rem; cursor:pointer;
  box-shadow:0 4px 20px rgba(99,102,241,.5);
  transition:all .2s; user-select:none;
}
#cw-btn:hover { transform:scale(1.08); }

#cw-badge {
  position:absolute; top:-4px; right:-4px;
  min-width:19px; height:19px;
  background:#ef4444; color:#fff;
  border-radius:99px; border:2px solid #fff;
  font-size:.62rem; font-weight:700;
  display:flex; align-items:center; justify-content:center; padding:0 5px;
}

/* ── Panel ── */
#cw-panel {
  position:fixed; bottom:88px; right:24px; z-index:9979;
  width:320px; max-height:500px;
  background:var(--card-bg);
  border:1px solid var(--border);
  border-radius:16px;
  box-shadow:0 16px 48px rgba(0,0,0,.2);
  display:flex; flex-direction:column;
  overflow:hidden;
}

/* ── Header ── */
#cw-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:13px 14px;
  background:var(--primary); color:#fff;
  font-weight:700; font-size:.9rem; flex-shrink:0;
}
#cw-header-left { display:flex; align-items:center; gap:8px; }
#cw-header-right { display:flex; gap:4px; }
#cw-back {
  background:rgba(255,255,255,.2); border:none; color:#fff;
  width:28px; height:28px; border-radius:7px;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:.9rem;
}
.cw-hbtn {
  background:rgba(255,255,255,.15); border:none; color:#fff;
  width:28px; height:28px; border-radius:7px;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:.82rem; transition:background .15s;
}
.cw-hbtn:hover { background:rgba(255,255,255,.3); }

/* ── People list ── */
#cw-view-list { display:flex; flex-direction:column; flex:1; overflow:hidden; }
#cw-search-wrap {
  display:flex; align-items:center; gap:8px;
  padding:9px 14px; border-bottom:1px solid var(--border);
  background:var(--page-bg); flex-shrink:0;
}
#cw-search-wrap i { color:var(--text-3); font-size:.85rem; }
#cw-search {
  background:none; border:none; outline:none; flex:1;
  font-size:.83rem; color:var(--text-1); font-family:var(--font);
}
#cw-subtitle {
  font-size:.7rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.05em; color:var(--text-3);
  padding:8px 14px 4px; flex-shrink:0;
}
#cw-people { flex:1; overflow-y:auto; }

.cw-person {
  display:flex; align-items:center; gap:10px;
  padding:9px 14px; cursor:pointer; transition:background .12s;
}
.cw-person:hover { background:var(--page-bg); }
.cw-person-info { flex:1; min-width:0; }
.cw-person-name  { font-weight:600; font-size:.83rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cw-person-sub   { font-size:.71rem; color:var(--text-3); }
.cw-person-badge {
  background:var(--primary); color:#fff;
  border-radius:99px; font-size:.62rem; font-weight:700;
  min-width:18px; height:18px;
  display:flex; align-items:center; justify-content:center; padding:0 5px;
}
.cw-status-dot {
  width:9px; height:9px; border-radius:50%; flex-shrink:0;
  border:2px solid var(--card-bg);
}
.cw-online  { background:#10b981; }
.cw-offline { background:#94a3b8; }
.cw-idle    { background:#f59e0b; }

/* ── Conversation ── */
#cw-view-conv { display:flex; flex-direction:column; flex:1; overflow:hidden; }

/* Session bar */
#cw-sessions-bar {
  background:var(--page-bg); border-bottom:1px solid var(--border);
  padding:8px 12px; flex-shrink:0;
}
#cw-sessions-label { font-size:.7rem; color:var(--text-3); font-weight:600; margin-bottom:5px; }
#cw-session-dates { display:flex; flex-wrap:wrap; gap:4px; }
.cw-date-chip {
  background:var(--border); color:var(--text-2);
  border-radius:6px; padding:3px 8px; font-size:.72rem; font-weight:600;
  cursor:pointer; transition:all .12s;
}
.cw-date-chip:hover, .cw-date-chip.active { background:var(--primary); color:#fff; }

/* Loading */
#cw-loading {
  display:flex; align-items:center; justify-content:center;
  gap:6px; padding:8px; font-size:.78rem; color:var(--text-3); flex-shrink:0;
}
.cw-loading-text { font-size:.75rem; }

/* Messages */
#cw-messages {
  flex:1; overflow-y:auto; padding:10px 12px;
  display:flex; flex-direction:column; gap:8px;
  scroll-behavior:smooth;
}

.cw-date-divider {
  text-align:center; font-size:.68rem; color:var(--text-3);
  margin:4px 0; position:relative;
}
.cw-date-divider span {
  background:var(--card-bg); padding:0 8px; position:relative; z-index:1;
}
.cw-date-divider::before {
  content:''; position:absolute; top:50%; left:0; right:0;
  height:1px; background:var(--border);
}

.cw-msg { display:flex; align-items:flex-end; gap:6px; max-width:88%; }
.cw-msg-mine  { align-self:flex-end; flex-direction:row-reverse; }
.cw-msg-other { align-self:flex-start; }

.cw-bubble {
  padding:8px 11px; border-radius:12px;
  font-size:.83rem; line-height:1.45; word-break:break-word;
}
.cw-msg-mine  .cw-bubble { background:var(--primary); color:#fff; border-bottom-right-radius:3px; }
.cw-msg-other .cw-bubble { background:var(--page-bg); color:var(--text-1); border:1px solid var(--border); border-bottom-left-radius:3px; }

.cw-msg-time { font-size:.62rem; color:var(--text-3); white-space:nowrap; padding-bottom:2px; flex-shrink:0; }
.cw-msg-name { font-size:.68rem; color:var(--text-3); margin-bottom:2px; font-weight:600; }

/* Typing */
#cw-typing {
  display:flex; align-items:center; gap:5px;
  padding:5px 14px; font-size:.74rem; color:var(--text-3); flex-shrink:0;
}
.cw-dots { display:flex; gap:3px; align-items:center; }
.cw-dots span {
  width:5px; height:5px; background:var(--text-3); border-radius:50%;
  animation:cwbounce .9s infinite;
}
.cw-dots span:nth-child(2) { animation-delay:.2s; }
.cw-dots span:nth-child(3) { animation-delay:.4s; }
@keyframes cwbounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-5px)} }

/* Input */
#cw-input-row {
  display:flex; align-items:center; gap:8px;
  padding:10px 12px; border-top:1px solid var(--border); flex-shrink:0;
}
#cw-input {
  flex:1; border:1px solid var(--border); border-radius:20px;
  padding:7px 13px; font-size:.83rem; outline:none;
  background:var(--page-bg); color:var(--text-1); font-family:var(--font);
  transition:border .15s;
}
#cw-input:focus { border-color:var(--primary); }
#cw-send {
  background:var(--primary); color:#fff; border:none;
  border-radius:50%; width:34px; height:34px;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:.85rem; flex-shrink:0;
  transition:background .15s;
}
#cw-send:hover { background:var(--primary-d); }

/* New group + group bar + modal */
#cw-view-new-group {
  display:none; flex-direction:column; flex:1; overflow:hidden;
  padding:12px 14px 10px; border-top:1px solid var(--border);
  background:var(--card-bg);
}
.cw-ng-head { margin-bottom:10px; }
.cw-ng-title { display:block; font-weight:700; font-size:.88rem; color:var(--text-1); }
.cw-ng-hint { font-size:.72rem; color:var(--text-3); }
.cw-ng-input {
  width:100%; border:1px solid var(--border); border-radius:10px;
  padding:8px 11px; font-size:.83rem; margin-bottom:8px;
  background:var(--page-bg); color:var(--text-1); font-family:var(--font);
}
.cw-ng-input:focus { outline:none; border-color:var(--primary); }
.cw-ng-search-wrap {
  display:flex; align-items:center; gap:8px;
  padding:6px 10px; border:1px solid var(--border); border-radius:10px;
  margin-bottom:8px; background:var(--page-bg);
}
.cw-ng-search-wrap i { color:var(--text-3); font-size:.8rem; }
.cw-ng-search {
  flex:1; border:none; background:transparent; outline:none;
  font-size:.8rem; color:var(--text-1); font-family:var(--font);
}
.cw-ng-members {
  flex:1; overflow-y:auto; min-height:120px; max-height:220px;
  border:1px solid var(--border); border-radius:10px; padding:4px 0;
  background:var(--page-bg);
}
.cw-ng-members-tall { max-height:260px; min-height:160px; }
.cw-ng-row {
  display:flex; align-items:center; gap:10px; padding:8px 12px;
  cursor:pointer; transition:background .12s;
}
.cw-ng-row:hover { background:rgba(99,102,241,.06); }
.cw-ng-row input { width:16px; height:16px; flex-shrink:0; cursor:pointer; }
.cw-ng-row-info { flex:1; min-width:0; }
.cw-ng-row-name { font-weight:600; font-size:.8rem; color:var(--text-1); }
.cw-ng-row-sub { font-size:.7rem; color:var(--text-3); }
.cw-ng-footer {
  display:flex; gap:8px; justify-content:flex-end; margin-top:10px; flex-shrink:0;
}
.cw-ng-btn {
  border-radius:8px; padding:7px 14px; font-size:.78rem; font-weight:600;
  border:none; cursor:pointer; font-family:var(--font);
}
.cw-ng-btn-ghost { background:var(--border); color:var(--text-2); }
.cw-ng-btn-primary { background:var(--primary); color:#fff; }
.cw-ng-btn-primary:hover { filter:brightness(0.95); }

#cw-group-bar {
  flex-shrink:0; border-bottom:1px solid var(--border);
  background:var(--page-bg); padding:8px 12px;
}
.cw-group-bar-inner {
  display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;
}
.cw-group-meta { font-size:.72rem; color:var(--text-3); flex:1; min-width:0; }
.cw-group-add-btn {
  font-size:.72rem; font-weight:600; white-space:nowrap;
  border:1px solid var(--border); background:var(--card-bg); color:var(--primary);
  border-radius:8px; padding:5px 10px; cursor:pointer;
  display:inline-flex; align-items:center; gap:4px;
}
.cw-group-add-btn:hover { background:var(--primary-soft); }

.cw-modal-overlay {
  position:absolute; inset:0; z-index:20;
  background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center;
  padding:12px;
}
.cw-modal {
  width:100%; max-width:288px; background:var(--card-bg);
  border-radius:14px; box-shadow:0 12px 40px rgba(0,0,0,.2);
  border:1px solid var(--border); overflow:hidden;
  max-height:92%;
  display:flex; flex-direction:column;
}
.cw-modal-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 12px; border-bottom:1px solid var(--border);
}
.cw-modal-title { font-weight:700; font-size:.86rem; }
.cw-modal-close {
  border:none; background:none; font-size:1.35rem; line-height:1; color:var(--text-3);
  cursor:pointer; padding:0 4px;
}
.cw-modal-body { padding:10px 12px; flex:1; overflow:hidden; display:flex; flex-direction:column; min-height:0; }
.cw-modal-foot {
  display:flex; gap:8px; justify-content:flex-end; padding:10px 12px;
  border-top:1px solid var(--border); flex-shrink:0;
}

@media(max-width:400px) {
  #cw-panel { width:calc(100vw - 20px); right:10px; }
}
</style>

<script>
var CW = (function(){
'use strict';
var BASE     = (document.querySelector('meta[name="base-url"]')||{content:'/'}).content;
var CSRF     = (document.querySelector('meta[name="csrf-token"]')||{content:''}).content;
var CSRFNAME = (document.querySelector('meta[name="csrf-param"]')||{content:'csrf_token'}).content;
var ME       = <?= isset($current_user) ? (int)$current_user->id : 0 ?>;
var ME_NAME  = <?= json_encode($current_user->first_name.' '.$current_user->last_name) ?>;
var ME_AVT   = <?= json_encode($current_user->avatar ?? null) ?>;

var state = {
  open:       false,
  roomId:     null,
  roomName:   '',
  roomAvatar: null,
  lastId:     0,
  pollTimer:  null,
  hbTimer:    null,
  currentDate: null,
  allDates:   [],
  typing:     false,
  typingTimer: null,
  roomType:   null,
  newGroupUsers: [],
  addMemberExistingIds: [],
};

// ── DOM refs ──
var $ = function(id){ return document.getElementById(id); };

// ── Helpers ──
function esc(s){
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmt_time(dt){
  var d=new Date(dt.replace(' ','T'));
  var h=d.getHours()%12||12,m=d.getMinutes(),ap=d.getHours()>=12?'PM':'AM';
  return (h<10?'0':'')+h+':'+(m<10?'0':'')+m+' '+ap;
}
function fmt_date(s){
  var d=new Date(s+'T00:00:00');
  var today=new Date(); today.setHours(0,0,0,0);
  var diff=(today-d)/86400000;
  if(diff===0) return 'Today';
  if(diff===1) return 'Yesterday';
  return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
}
function avatar(name,src,size){
  size=size||30;
  if(src) return '<img src="'+BASE+'uploads/'+esc(src)+'" style="width:'+size+'px;height:'+size+'px;border-radius:50%;object-fit:cover;flex-shrink:0">';
  var colors=['#6366f1','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'];
  var c=colors[(name.charCodeAt(0)||0)%colors.length];
  var i=(name.split(' ').map(function(w){return w[0]||'';}).join('').substring(0,2)||'?').toUpperCase();
  return '<div style="width:'+size+'px;height:'+size+'px;background:'+c+';border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:'+(size*0.38)+'px;flex-shrink:0">'+i+'</div>';
}
function post(url, data, cb){
  var fd=new FormData();
  for(var k in data) fd.append(k, data[k]);
  if(CSRF && CSRFNAME) fd.append(CSRFNAME, CSRF);
  fetch(BASE+url,{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(r){ if(typeof cb==='function') cb(r); })
    .catch(function(e){ console.error('[CW] POST error',url,e); });
}
function get(url, cb){
  fetch(BASE+url)
    .then(function(r){return r.json();})
    .then(function(r){ if(typeof cb==='function') cb(r); })
    .catch(function(e){ console.error('[CW] GET error',url,e); });
}

// ── Show / Hide ──
function toggle(){
  if(state.open) hide(); else show();
}
function show(){
  state.open=true;
  $('cw-panel').style.display='flex';
  hideNewGroupView();
  hideAddMembersModal();
  if(!state.roomId) loadPeople();
  startHb();
}
function hide(){
  state.open=false;
  $('cw-panel').style.display='none';
  hideNewGroupView();
  hideAddMembersModal();
  stopPoll();
}
function back(){
  if($('cw-view-new-group') && $('cw-view-new-group').style.display==='flex'){
    hideNewGroupView();
    $('cw-view-list').style.display='flex';
    $('cw-back').style.display='none';
    $('cw-title').textContent='Messages';
    return;
  }
  state.roomId=null; state.lastId=0; state.currentDate=null; state.roomType=null;
  stopPoll();
  $('cw-view-conv').style.display='none';
  $('cw-view-new-group').style.display='none';
  $('cw-view-list').style.display='flex';
  $('cw-back').style.display='none';
  $('cw-title').textContent='Messages';
  $('cw-group-bar').style.display='none';
  loadPeople();
}

// ── People list ──
function loadPeople(){
  get('chat/user_list', function(d){
    if(!d.success) return;
    get('chat/rooms', function(r){
      renderPeople(d.users, (r.success && r.rooms) ? r.rooms : []);
    });
  });
}
function renderPeople(users, rooms){
  var el=$('cw-people'); el.innerHTML='';
  rooms = rooms || [];
  if(rooms.length){
    var sub=document.createElement('div');
    sub.id='cw-sub-rooms';
    sub.className='small fw-700 text-uppercase';
    sub.style.cssText='padding:8px 14px 4px;font-size:.68rem;letter-spacing:.05em;color:var(--text-3)';
    sub.textContent='Chats';
    el.appendChild(sub);
    rooms.forEach(function(room){
      var title = room.name || ('Room #'+room.id);
      var avt = room.type==='direct' ? room.other_avatar : null;
      var d=document.createElement('div');
      d.className='cw-person';
      d.innerHTML=
        '<div style="position:relative;flex-shrink:0">'+avatar(title, avt, 34)+'</div>'+
        '<div class="cw-person-info">'+
          '<div class="cw-person-name">'+esc(title)+'</div>'+
          '<div class="cw-person-sub">'+(room.type==='group'?'Group chat':'Direct')+'</div>'+
        '</div>'+
        (parseInt(room.unread_count,10)>0?'<div class="cw-person-badge">'+(room.unread_count>99?'99+':room.unread_count)+'</div>':'');
      d.onclick=function(){ openRoom(room.id, title, avt, room.type); };
      d.setAttribute('data-cw-kind','room');
      el.appendChild(d);
    });
    var div=document.createElement('div');
    div.id='cw-sub-team';
    div.className='small fw-700 text-uppercase';
    div.style.cssText='padding:10px 14px 4px;font-size:.68rem;letter-spacing:.05em;color:var(--text-3)';
    div.textContent='Start direct message';
    el.appendChild(div);
  }
  if(!users||!users.length){
    if(!rooms.length) el.innerHTML='<div style="padding:20px;text-align:center;color:var(--text-3);font-size:.82rem">No team members</div>';
    return;
  }
  users.forEach(function(u){
    if(u.id==ME) return;
    var dot='<span class="cw-status-dot '+(u.is_online?'cw-online':'cw-offline')+'"></span>';
    var d=document.createElement('div');
    d.className='cw-person';
    d.innerHTML=
      '<div style="position:relative;flex-shrink:0">'+avatar(u.first_name+' '+u.last_name,u.avatar,34)+
      '<span style="position:absolute;bottom:0;right:0" class="cw-status-dot '+(u.is_online?'cw-online':'cw-offline')+'"></span></div>'+
      '<div class="cw-person-info">'+
        '<div class="cw-person-name">'+esc(u.first_name+' '+u.last_name)+'</div>'+
        '<div class="cw-person-sub">'+(u.job_title||'')+'</div>'+
      '</div>'+
      (u.unread>0?'<div class="cw-person-badge">'+u.unread+'</div>':'');
    d.onclick=function(){ openDirect(u.id,u.first_name+' '+u.last_name,u.avatar); };
    d.setAttribute('data-cw-kind','person');
    el.appendChild(d);
  });
  reflowCwSearch();
}

// Search filter: filter rows, then show section headers if any child visible
function reflowCwSearch(){
  var q=($('cw-search').value||'').toLowerCase();
  var wrap=$('cw-people');
  if(!wrap) return;
  wrap.querySelectorAll('.cw-person').forEach(function(el){
    el.style.display=(!q||el.textContent.toLowerCase().includes(q))?'':'none';
  });
  var children=Array.prototype.slice.call(wrap.children);
  var roomsH=wrap.querySelector('#cw-sub-rooms');
  var teamH=wrap.querySelector('#cw-sub-team');
  if(roomsH){
    var i=children.indexOf(roomsH), anyR=false;
    for(var j=i+1;j<children.length;j++){
      var n=children[j];
      if(n===teamH) break;
      if(n.classList && n.classList.contains('cw-person') && n.getAttribute('data-cw-kind')==='room' && n.style.display!=='none'){ anyR=true; break; }
    }
    roomsH.style.display=(!q||anyR)?'':'none';
  }
  if(teamH){
    var k=children.indexOf(teamH), anyP=false;
    for(var m=k+1;m<children.length;m++){
      var p=children[m];
      if(p.classList && p.classList.contains('cw-person') && p.getAttribute('data-cw-kind')==='person' && p.style.display!=='none'){ anyP=true; break; }
    }
    teamH.style.display=(!q||anyP)?'':'none';
  }
}
$('cw-search').addEventListener('input', reflowCwSearch);

// ── Open direct chat ──
function openDirect(userId, name, avt){
  post('chat/direct/'+userId,{},function(d){
    if(!d.success){ alert('Could not open chat: '+(d.message||'error')); return; }
    openRoom(d.room.id, name, avt, d.room.type || 'direct');
  });
}

function openRoom(roomId, name, avt, roomType){
  hideNewGroupView();
  state.roomId   = roomId;
  state.roomName = name;
  state.roomAvatar = avt;
  state.roomType = roomType || null;
  state.lastId   = 0;
  state.currentDate = null;

  $('cw-title').textContent=name;
  $('cw-back').style.display='flex';
  $('cw-view-list').style.display='none';
  $('cw-view-new-group').style.display='none';
  $('cw-view-conv').style.display='flex';
  $('cw-messages').innerHTML='';
  $('cw-sessions-bar').style.display='none';
  $('cw-loading').style.display='flex';
  $('cw-group-bar').style.display='none';

  if(state.roomType==='group'){
    get('chat/room_info/'+roomId, function(info){
      if(info.success && info.members){
        var n=info.members.length;
        $('cw-group-meta').textContent=n+' member'+(n!==1?'s':'');
        $('cw-group-bar').style.display='block';
      } else {
        $('cw-group-bar').style.display='block';
        $('cw-group-meta').textContent='Group chat';
      }
    });
  }

  // Load today's session
  get('chat/session/'+roomId, function(d){
    $('cw-loading').style.display='none';
    if(!d.success){ return; }

    state.currentDate = d.today;

    // Show date session history bar
    if(d.dates && d.dates.length>1){
      renderSessionDates(d.dates, d.today);
      $('cw-sessions-bar').style.display='block';
    }

    // Render messages
    renderMessages(d.messages, true);
    scrollBottom();
    startPoll();
  });
}

// ── Render messages ──
function renderMessages(msgs, replace){
  var area=$('cw-messages');
  if(replace) area.innerHTML='';
  if(!msgs||!msgs.length) return;

  var lastDate='';
  msgs.forEach(function(m){
    var d=m.created_at?m.created_at.substring(0,10):'';
    if(d && d!==lastDate){
      var div=document.createElement('div');
      div.className='cw-date-divider';
      div.innerHTML='<span>'+fmt_date(d)+'</span>';
      area.appendChild(div);
      lastDate=d;
    }
    var isMine=parseInt(m.user_id)===ME;
    var wrap=document.createElement('div');
    wrap.className='cw-msg '+(isMine?'cw-msg-mine':'cw-msg-other');
    wrap.setAttribute('data-id', m.id);
    var ts=m.created_at?fmt_time(m.created_at):'';
    if(isMine){
      wrap.innerHTML=
        '<span class="cw-msg-time">'+ts+'</span>'+
        '<div class="cw-bubble">'+esc(m.message)+'</div>';
    } else {
      wrap.innerHTML=
        '<div style="flex-shrink:0">'+avatar((m.first_name||'?')+' '+(m.last_name||''),m.avatar,26)+'</div>'+
        '<div style="max-width:80%">'+
          (state.roomType==='group'?('<div class="cw-msg-name">'+esc((m.first_name||'')+' '+(m.last_name||''))+'</div>'):'')+
          '<div class="cw-bubble">'+esc(m.message)+'</div>'+
          '<span class="cw-msg-time">'+ts+'</span>'+
        '</div>';
    }
    area.appendChild(wrap);
    if(m.id) state.lastId=Math.max(state.lastId, parseInt(m.id)||0);
  });
}

// ── Session date chips ──
function renderSessionDates(dates, today){
  var bar=$('cw-session-dates'); bar.innerHTML='';
  dates.forEach(function(d){
    if(d.session_date===today) return; // skip today
    var chip=document.createElement('div');
    chip.className='cw-date-chip';
    chip.textContent=fmt_date(d.session_date)+' ('+d.msg_count+')';
    chip.setAttribute('data-date', d.session_date);
    chip.onclick=function(){
      document.querySelectorAll('.cw-date-chip').forEach(function(c){ c.classList.remove('active'); });
      chip.classList.add('active');
      loadDaySession(state.roomId, d.session_date);
    };
    bar.appendChild(chip);
  });
  // Add "Today" chip
  var todayChip=document.createElement('div');
  todayChip.className='cw-date-chip active';
  todayChip.textContent='Today';
  todayChip.onclick=function(){
    document.querySelectorAll('.cw-date-chip').forEach(function(c){ c.classList.remove('active'); });
    todayChip.classList.add('active');
    loadTodaySession(state.roomId);
  };
  bar.insertBefore(todayChip, bar.firstChild);
}

// ── Load a specific day's session ──
function loadDaySession(roomId, date){
  $('cw-loading').style.display='flex';
  $('cw-messages').innerHTML='';
  stopPoll();
  get('chat/day_session/'+roomId+'?date='+date, function(d){
    $('cw-loading').style.display='none';
    if(!d.success) return;
    state.currentDate = date;
    state.lastId = 0;
    renderMessages(d.messages, true);
    scrollBottom();
    // Don't poll for past dates
  });
}

function loadTodaySession(roomId){
  $('cw-loading').style.display='flex';
  $('cw-messages').innerHTML='';
  get('chat/session/'+roomId, function(d){
    $('cw-loading').style.display='none';
    if(!d.success) return;
    state.currentDate = d.today;
    state.lastId = 0;
    renderMessages(d.messages, true);
    scrollBottom();
    startPoll();
  });
}

// Scroll to top → show session selector
$('cw-messages').addEventListener('scroll', function(){
  if(this.scrollTop === 0 && state.roomId){
    $('cw-sessions-bar').style.display='block';
  }
});

// ── Polling ──
function startPoll(){
  stopPoll();
  state.pollTimer=setInterval(function(){
    if(!state.roomId) return;
    // Only poll today's session
    if(state.currentDate && state.currentDate!==new Date().toISOString().substring(0,10)) return;
    get('chat/poll/'+state.roomId+'?after='+state.lastId, function(d){
      if(!d.success||!d.messages||!d.messages.length) return;
      var area=$('cw-messages');
      var atBottom = area.scrollHeight - area.scrollTop <= area.clientHeight + 80;
      renderMessages(d.messages, false);
      if(atBottom) scrollBottom();
      updateBadge();
    });
  }, 2500);
}
function stopPoll(){ if(state.pollTimer){ clearInterval(state.pollTimer); state.pollTimer=null; } }

// ── Send ──
function sendMsg(){
  var inp=$('cw-input');
  var msg=inp.value.trim();
  if(!msg||!state.roomId) return;
  inp.value='';
  post('chat/send',{room_id:state.roomId,message:msg},function(d){
    if(d.success && d.message){
      renderMessages([d.message], false);
      scrollBottom();
    }
  });
}
$('cw-send').onclick=sendMsg;
$('cw-input').addEventListener('keydown',function(e){
  if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); sendMsg(); }
});

// ── Heartbeat + badge ──
function startHb(){
  updateBadge();
  state.hbTimer=setInterval(updateBadge, 12000);
}
function updateBadge(){
  post('chat/heartbeat',{status:'online'},function(d){
    if(!d.success) return;
    var badge=$('cw-badge');
    if(d.unread>0){ badge.textContent=d.unread>99?'99+':d.unread; badge.style.display='flex'; }
    else badge.style.display='none';
  });
}

// ── New group (name + members) ──
function hideNewGroupView(){
  var v=$('cw-view-new-group');
  if(v) v.style.display='none';
}
function showNewGroupView(){
  $('cw-view-list').style.display='none';
  $('cw-view-conv').style.display='none';
  $('cw-view-new-group').style.display='flex';
  $('cw-back').style.display='flex';
  $('cw-title').textContent='New group';
  $('cw-ng-name').value='';
  $('cw-ng-search').value='';
  renderNewGroupPicker();
}
function renderNewGroupPicker(){
  var box=$('cw-ng-members');
  if(!box) return;
  box.innerHTML='';
  var q=($('cw-ng-search').value||'').toLowerCase();
  state.newGroupUsers.forEach(function(u){
    if(u.id===ME) return;
    var nm=(u.first_name+' '+u.last_name).toLowerCase();
    if(q && nm.indexOf(q)<0) return;
    var row=document.createElement('label');
    row.className='cw-ng-row';
    row.innerHTML='<input type="checkbox" name="cw_ng_m" value="'+u.id+'">'+
      avatar(u.first_name+' '+u.last_name,u.avatar,32)+
      '<div class="cw-ng-row-info"><div class="cw-ng-row-name">'+esc(u.first_name+' '+u.last_name)+'</div>'+
      '<div class="cw-ng-row-sub">'+(u.job_title||'')+'</div></div>';
    box.appendChild(row);
  });
  if(!box.children.length){
    box.innerHTML='<div style="padding:16px;text-align:center;color:var(--text-3);font-size:.78rem">No people to add</div>';
  }
}
if($('cw-ng-search')) $('cw-ng-search').addEventListener('input', renderNewGroupPicker);
if($('cw-ng-cancel')) $('cw-ng-cancel').addEventListener('click', function(){
  hideNewGroupView();
  $('cw-view-list').style.display='flex';
  $('cw-back').style.display='none';
  $('cw-title').textContent='Messages';
});
if($('cw-ng-create')) $('cw-ng-create').addEventListener('click', function(){
  var name=($('cw-ng-name').value||'').trim();
  if(!name){ alert('Please enter a group name.'); return; }
  var fd=new FormData();
  fd.append('name', name);
  var checks=document.querySelectorAll('#cw-ng-members input[type="checkbox"]:checked');
  checks.forEach(function(c){ fd.append('members[]', c.value); });
  if(CSRF && CSRFNAME) fd.append(CSRFNAME, CSRF);
  fetch(BASE+'chat/create_group',{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
      if(d.success){
        hideNewGroupView();
        $('cw-back').style.display='flex';
        openRoom(d.room_id, d.name, null, 'group');
      } else alert(d.message||'Could not create group');
    })
    .catch(function(){ alert('Network error'); });
});

function newGroup(){
  get('chat/user_list', function(d){
    if(!d.success||!d.users){ alert('Could not load team list'); return; }
    state.newGroupUsers=d.users;
    showNewGroupView();
  });
}

// ── Add members to existing group ──
function hideAddMembersModal(){
  var o=$('cw-add-overlay');
  if(o){ o.style.display='none'; o.setAttribute('aria-hidden','true'); }
}
function showAddMembersModal(){
  var o=$('cw-add-overlay');
  if(!o) return;
  $('cw-add-search').value='';
  state.addMemberExistingIds=[];
  get('chat/room_info/'+state.roomId, function(info){
    if(!info.success||!info.members){ hideAddMembersModal(); return; }
    info.members.forEach(function(m){ state.addMemberExistingIds.push(parseInt(m.id,10)); });
    renderAddMemberPicker();
    o.style.display='flex';
    o.setAttribute('aria-hidden','false');
  });
}
function renderAddMemberPicker(){
  var box=$('cw-add-members');
  if(!box) return;
  box.innerHTML='';
  var q=($('cw-add-search').value||'').toLowerCase();
  var existing={};
  state.addMemberExistingIds.forEach(function(id){ existing[id]=true; });
  state.newGroupUsers.forEach(function(u){
    if(u.id===ME||existing[u.id]) return;
    var nm=(u.first_name+' '+u.last_name).toLowerCase();
    if(q && nm.indexOf(q)<0) return;
    var row=document.createElement('label');
    row.className='cw-ng-row';
    row.innerHTML='<input type="checkbox" name="cw_add_m" value="'+u.id+'">'+
      avatar(u.first_name+' '+u.last_name,u.avatar,32)+
      '<div class="cw-ng-row-info"><div class="cw-ng-row-name">'+esc(u.first_name+' '+u.last_name)+'</div>'+
      '<div class="cw-ng-row-sub">'+(u.job_title||'')+'</div></div>';
    box.appendChild(row);
  });
  if(!box.children.length){
    box.innerHTML='<div style="padding:16px;text-align:center;color:var(--text-3);font-size:.78rem">Everyone is already in this group</div>';
  }
}
if($('cw-add-search')) $('cw-add-search').addEventListener('input', renderAddMemberPicker);
if($('cw-add-close')) $('cw-add-close').addEventListener('click', hideAddMembersModal);
if($('cw-add-cancel')) $('cw-add-cancel').addEventListener('click', hideAddMembersModal);
if($('cw-add-overlay')) $('cw-add-overlay').addEventListener('click', function(e){
  if(e.target===this) hideAddMembersModal();
});
if($('cw-add-confirm')) $('cw-add-confirm').addEventListener('click', function(){
  var fd=new FormData();
  var checks=document.querySelectorAll('#cw-add-members input[type="checkbox"]:checked');
  if(!checks.length){ hideAddMembersModal(); return; }
  checks.forEach(function(c){ fd.append('members[]', c.value); });
  if(CSRF && CSRFNAME) fd.append(CSRFNAME, CSRF);
  fetch(BASE+'chat/add_members/'+state.roomId,{method:'POST',body:fd})
    .then(function(r){return r.json();})
    .then(function(d){
      hideAddMembersModal();
      if(d.success){
        get('chat/room_info/'+state.roomId, function(info){
          if(info.success && info.members){
            var n=info.members.length;
            $('cw-group-meta').textContent=n+' member'+(n!==1?'s':'');
          }
        });
        updateBadge();
      } else alert(d.message||'Could not add members');
    })
    .catch(function(){ alert('Network error'); });
});
if($('cw-btn-add-members')) $('cw-btn-add-members').addEventListener('click', function(){
  if(state.roomType!=='group'||!state.roomId) return;
  get('chat/user_list', function(d){
    if(!d.success||!d.users){ alert('Could not load team'); return; }
    state.newGroupUsers=d.users;
    showAddMembersModal();
  });
});

// ── Utils ──
function scrollBottom(){
  var a=$('cw-messages');
  a.scrollTop=a.scrollHeight;
}

// Init
setTimeout(updateBadge, 1500);
if(location.hash && location.hash.indexOf('open-chat-')===0){
  var rid=parseInt(location.hash.replace('#open-chat-',''),10);
  if(rid){ setTimeout(function(){ show(); openRoom(rid,'Messages',null,null); location.hash=''; }, 600); }
}

return { toggle:toggle, show:show, hide:hide, back:back, newGroup:newGroup };
})();
</script>

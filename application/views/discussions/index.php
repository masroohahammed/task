<div class="page-header">
  <div>
    <div class="breadcrumb-custom mb-1">
      <a href="<?= site_url('projects') ?>">Projects</a><i class="bi bi-chevron-right"></i>
      <a href="<?= site_url('projects/view/'.$project->id) ?>"><?= html_escape($project->name) ?></a>
      <i class="bi bi-chevron-right"></i><span>Discussion</span>
    </div>
    <h1 class="page-title">Project Discussion</h1>
  </div>
  <div class="page-actions gap-2">
    <a href="<?= site_url('projects/kanban/'.$project->id) ?>" class="btn btn-ghost btn-sm"><i class="bi bi-kanban me-1"></i>Kanban</a>
  </div>
</div>

<div class="row g-4">
  <!-- Discussion Thread -->
  <div class="col-xl-8">
    <div class="card card-modern">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-chat-dots me-2 text-primary"></i>Discussion Thread</h6>
        <span class="small text-muted"><?= count($discussions) ?> posts</span>
      </div>

      <!-- Messages area -->
      <div class="disc-thread" id="discThread">
        <?php if(empty($discussions)): ?>
        <div class="empty-state py-5"><i class="bi bi-chat-dots"></i><h5>No discussions yet</h5><p>Start the conversation below.</p></div>
        <?php else: foreach($discussions as $d):
          $replies = $this->Discussion_model->get_replies($d->id);
          $tagged  = $d->tagged_users ? json_decode($d->tagged_users, true) : [];
        ?>
        <div class="disc-post <?= $d->is_pinned?'disc-pinned':'' ?>" data-id="<?= $d->id ?>">
          <div class="disc-post-avatar">
            <?= user_avatar($d->first_name.' '.$d->last_name, $d->avatar??null, 38) ?>
          </div>
          <div class="disc-post-body">
            <div class="disc-post-header">
              <span class="disc-author"><?= html_escape($d->first_name.' '.$d->last_name) ?></span>
              <?php if($d->job_title): ?><span class="disc-role"><?= html_escape($d->job_title) ?></span><?php endif; ?>
              <?php if($d->is_pinned): ?><span class="disc-pinned-badge"><i class="bi bi-pin-fill"></i> Pinned</span><?php endif; ?>
              <span class="disc-time ms-auto"><?= time_ago($d->created_at) ?></span>
              <?php if($d->user_id==$current_user->id||has_role(['admin','project_manager','hr'])): ?>
              <button class="btn-disc-delete" onclick="deleteDisc(<?= $d->id ?>,this)" title="Delete"><i class="bi bi-trash"></i></button>
              <?php endif; ?>
            </div>
            <div class="disc-post-text"><?= nl2br(html_escape($d->message)) ?></div>
            <?php if(!empty($tagged)): ?>
            <div class="disc-tags">
              <?php foreach($tagged as $tid):
                $tu = $this->User_model->get_user($tid);
                if($tu): ?>
              <span class="disc-tag">@<?= html_escape($tu->first_name.' '.$tu->last_name) ?></span>
              <?php endif; endforeach; ?>
            </div>
            <?php endif; ?>
            <?php if($d->attachments): ?>
            <div class="disc-attachment">
              <a href="<?= base_url('uploads/'.$d->attachments) ?>" target="_blank">
                <i class="bi bi-paperclip me-1"></i><?= basename($d->attachments) ?>
              </a>
            </div>
            <?php endif; ?>

            <!-- Replies -->
            <?php if(!empty($replies)): ?>
            <div class="disc-replies">
              <?php foreach($replies as $r): ?>
              <div class="disc-reply">
                <?= user_avatar($r->first_name.' '.$r->last_name, $r->avatar??null, 28) ?>
                <div class="disc-reply-bubble">
                  <span class="disc-reply-author"><?= html_escape($r->first_name.' '.$r->last_name) ?></span>
                  <span class="disc-reply-text"><?= nl2br(html_escape($r->message)) ?></span>
                  <span class="disc-reply-time"><?= time_ago($r->created_at) ?></span>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <button class="btn-reply-toggle" onclick="toggleReply(<?= $d->id ?>)">
              <i class="bi bi-reply me-1"></i>Reply
            </button>
            <div class="disc-reply-form" id="replyForm-<?= $d->id ?>" style="display:none">
              <input type="text" class="form-control form-control-sm" placeholder="Write a reply..." id="replyText-<?= $d->id ?>">
              <button class="btn btn-sm btn-primary mt-1" onclick="sendReply(<?= $d->id ?>,<?= $project->id ?>)">Reply</button>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- Post new message -->
      <div class="disc-composer">
        <div class="disc-composer-header">
          <?= user_avatar($current_user->first_name.' '.$current_user->last_name, $current_user->avatar, 36) ?>
          <div class="disc-composer-input-wrap">
            <textarea class="form-control" id="discMessage" rows="3"
              placeholder="Write a message... Use @name to tag someone"></textarea>
          </div>
        </div>
        <!-- Tag users -->
        <div class="disc-composer-actions">
          <div class="tag-wrap">
            <label class="form-label fw-600 small mb-1">Tag Team Members</label>
            <div class="tag-chips" id="tagChips">
              <?php foreach($members as $m): ?>
              <label class="tag-chip">
                <input type="checkbox" class="tag-check" value="<?= $m->user_id ?>">
                <?= user_avatar($m->first_name.' '.$m->last_name, $m->avatar??null, 22) ?>
                <span><?= html_escape($m->first_name) ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="d-flex gap-2 align-items-center mt-3">
            <label class="disc-attach-btn" for="discFile"><i class="bi bi-paperclip me-1"></i>Attach</label>
            <input type="file" id="discFile" class="d-none">
            <span id="discFileName" class="small text-muted"></span>
            <button class="btn btn-primary ms-auto" id="btnPostDisc">
              <i class="bi bi-send me-1"></i>Post
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar: Members -->
  <div class="col-xl-4">
    <div class="card card-modern mb-4">
      <div class="card-header-modern">
        <h6 class="card-title-modern"><i class="bi bi-people me-2 text-success"></i>Team Members</h6>
      </div>
      <div class="card-body p-0">
        <?php foreach($members as $m): ?>
        <div class="list-item">
          <?= user_avatar($m->first_name.' '.$m->last_name, $m->avatar??null, 36) ?>
          <div class="list-item-body">
            <div class="list-item-title small"><?= html_escape($m->first_name.' '.$m->last_name) ?></div>
            <div class="small text-muted"><?= html_escape($m->job_title??$m->role) ?></div>
          </div>
          <?php if($m->is_pm??false): ?>
          <span class="badge bg-primary-soft text-primary small">PM</span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<style>
.disc-thread{padding:20px;display:flex;flex-direction:column;gap:20px;max-height:600px;overflow-y:auto;}
.disc-post{display:flex;gap:12px;}
.disc-pinned{background:rgba(99,102,241,.04);border-radius:12px;padding:12px;}
.disc-post-avatar{flex-shrink:0;}
.disc-post-body{flex:1;}
.disc-post-header{display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;}
.disc-author{font-weight:700;font-size:.87rem;}
.disc-role{font-size:.72rem;color:var(--text-3);background:var(--border);padding:2px 7px;border-radius:4px;}
.disc-pinned-badge{font-size:.7rem;background:rgba(245,158,11,.12);color:#d97706;padding:2px 7px;border-radius:4px;}
.disc-time{font-size:.72rem;color:var(--text-3);}
.btn-disc-delete{background:none;border:none;color:var(--text-3);cursor:pointer;padding:2px 5px;font-size:.8rem;transition:color .15s;}
.btn-disc-delete:hover{color:var(--danger);}
.disc-post-text{font-size:.86rem;line-height:1.6;color:var(--text-1);}
.disc-tags{display:flex;gap:5px;flex-wrap:wrap;margin-top:6px;}
.disc-tag{font-size:.74rem;background:var(--primary-soft);color:var(--primary);padding:2px 8px;border-radius:4px;font-weight:600;}
.disc-attachment{margin-top:6px;font-size:.78rem;}
.disc-attachment a{color:var(--primary);}
.disc-replies{margin-top:10px;padding-left:10px;border-left:2px solid var(--border);display:flex;flex-direction:column;gap:8px;}
.disc-reply{display:flex;gap:8px;align-items:flex-start;}
.disc-reply-bubble{background:var(--page-bg);border-radius:8px;padding:7px 10px;flex:1;}
.disc-reply-author{font-weight:700;font-size:.78rem;display:block;}
.disc-reply-text{font-size:.82rem;display:block;margin-top:2px;}
.disc-reply-time{font-size:.7rem;color:var(--text-3);}
.btn-reply-toggle{background:none;border:none;font-size:.76rem;color:var(--text-3);cursor:pointer;padding:5px 0 0;transition:color .15s;}
.btn-reply-toggle:hover{color:var(--primary);}
.disc-reply-form{margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;}

.disc-composer{border-top:1px solid var(--border);padding:16px 20px;}
.disc-composer-header{display:flex;gap:12px;margin-bottom:12px;}
.disc-composer-input-wrap{flex:1;}
.disc-composer-actions{border-top:1px solid var(--border-lt);padding-top:12px;}
.tag-wrap{}
.tag-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;}
.tag-chip{display:flex;align-items:center;gap:5px;padding:4px 8px;border:1px solid var(--border);border-radius:6px;cursor:pointer;font-size:.78rem;transition:all .15s;}
.tag-chip:hover{border-color:var(--primary);background:var(--primary-soft);}
.tag-chip input{display:none;}
.tag-chip:has(input:checked){border-color:var(--primary);background:var(--primary-soft);color:var(--primary);}
.disc-attach-btn{font-size:.8rem;color:var(--text-2);cursor:pointer;display:flex;align-items:center;padding:5px 10px;border:1px solid var(--border);border-radius:7px;transition:all .15s;}
.disc-attach-btn:hover{border-color:var(--primary);color:var(--primary);}
</style>

<script>
var BASE='<?= base_url() ?>';
var PROJECT_ID=<?= $project->id ?>;
var LAST_ID=<?= !empty($discussions)?end($discussions)->id:0 ?>;

$('#discFile').change(function(){$('#discFileName').text(this.files[0]?this.files[0].name:'');});

// Post new discussion
$('#btnPostDisc').click(function(){
  var msg=$('#discMessage').val().trim();
  if(!msg){alert('Please write a message.');return;}
  var tagged=[];$('.tag-check:checked').each(function(){tagged.push($(this).val());});
  var fd=new FormData();
  fd.append('project_id',PROJECT_ID);
  fd.append('message',msg);
  fd.append('tagged_users',tagged.join(','));
  if($('#discFile')[0].files[0]) fd.append('attachment',$('#discFile')[0].files[0]);
  $(this).prop('disabled',true).html('<span class="spinner-border spinner-border-sm"></span>');
  var self=this;
  $.ajax({url:BASE+'discussions/post',type:'POST',data:fd,processData:false,contentType:false,dataType:'json',
    success:function(r){
      $(self).prop('disabled',false).html('<i class="bi bi-send me-1"></i>Post');
      if(r.success){$('#discMessage').val('');$('.tag-check').prop('checked',false);$('#discFile').val('');$('#discFileName').text('');location.reload();}
    }
  });
});

function toggleReply(id){$('#replyForm-'+id).toggle();}

function sendReply(parentId,projectId){
  var msg=$('#replyText-'+parentId).val().trim();
  if(!msg)return;
  $.post(BASE+'discussions/post',{project_id:projectId,message:msg,parent_id:parentId},function(r){
    if(r.success){location.reload();}
  },'json');
}

function deleteDisc(id,btn){
  if(!confirm('Delete this post?'))return;
  $.post(BASE+'discussions/delete/'+id,{},function(r){
    if(r.success)$(btn).closest('.disc-post').fadeOut(300,function(){$(this).remove();});
  },'json');
}

// Auto-poll for new messages every 15 seconds
setInterval(function(){
  $.get(BASE+'discussions/get_new/'+PROJECT_ID,{after:LAST_ID},function(r){
    if(r.success&&r.discussions&&r.discussions.length){location.reload();}
  },'json');
},15000);
</script>

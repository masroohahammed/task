<script>
(function($) {
  var BASE_URL = '<?= base_url() ?>';
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var CSRF_NAME = (document.querySelector('meta[name="csrf-param"]') || {}).content || 'csrf_token';

  function withCsrf(data) {
    var payload = {};
    $.each(data || {}, function(key, val) {
      if (key === 'projects' && val && typeof val === 'object') {
        $.each(val, function(pid, perm) {
          payload['projects[' + pid + ']'] = perm;
        });
      } else {
        payload[key] = val;
      }
    });
    payload[CSRF_NAME] = CSRF;
    return payload;
  }

  function showModal(id) {
    var el = document.getElementById(id);
    if (el && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    }
  }

  function hideModal(id) {
    var el = document.getElementById(id);
    if (el && window.bootstrap) {
      var instance = bootstrap.Modal.getInstance(el);
      if (instance) instance.hide();
    }
  }

  function togglePermSelect(checkbox) {
    var pid = $(checkbox).data('project');
    var isAdd = $(checkbox).hasClass('add-proj-check');
    var sel = (isAdd ? '.add-proj-perm' : '.edit-proj-perm') + '[data-project="' + pid + '"]';
    $(sel).prop('disabled', !checkbox.checked);
  }

  $(document).on('change', '.add-proj-check, .edit-proj-check', function() {
    togglePermSelect(this);
  });

  function collectCheckedProjects(checkClass, permClass) {
    var projects = {};
    $(checkClass + ':checked').each(function() {
      var pid = $(this).data('project');
      var perm = $(permClass + '[data-project="' + pid + '"]').val();
      if (perm) projects[pid] = perm;
    });
    return projects;
  }

  function resetAddProjectForm() {
    $('.add-proj-check').prop('checked', false);
    $('.add-proj-perm').prop('disabled', true).val('tickets');
  }

  $('#addUserModal').on('hidden.bs.modal', function() {
    resetAddProjectForm();
    $('#addUserMsg').addClass('d-none').removeClass('alert alert-danger alert-success').text('');
  });

  $('#saveNewUser').on('click', function() {
    var $btn = $(this).prop('disabled', true).html('<i class="bi bi-check-lg me-1"></i> Creating...');
    $.ajax({
      url: BASE_URL + 'client-portal/add_user',
      method: 'POST',
      dataType: 'json',
      data: withCsrf({
        first_name: $('#newUserFirst').val(),
        last_name:  $('#newUserLast').val(),
        email:      $('#newUserEmail').val(),
        job_title:  $('#newUserTitle').val(),
        password:   $('#newUserPass').val(),
        projects:   collectCheckedProjects('.add-proj-check', '.add-proj-perm')
      }),
      success: function(res) {
        $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Create User');
        if (res.success) {
          hideModal('addUserModal');
          location.reload();
        } else {
          $('#addUserMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Error');
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Create User');
        $('#addUserMsg').removeClass('d-none').addClass('alert alert-danger').text('Error. Please try again.');
      }
    });
  });

  window.removeUser = function(uid, btn) {
    if (!confirm('Remove this team member? They will lose access to all projects.')) return;
    $.post(BASE_URL + 'client-portal/remove_user/' + uid, withCsrf({}), function(res) {
      if (res.success) $(btn).closest('tr').fadeOut(300, function(){ $(this).remove(); });
    }, 'json').fail(function() {
      alert('Could not remove user. Please refresh the page and try again.');
    });
  };

  window.resetPassword = function(uid) {
    $('#resetUserId').val(uid);
    $('#newPassInput').val('');
    $('#resetPassMsg').addClass('d-none').removeClass('alert alert-success alert-danger').text('');
    showModal('resetPassModal');
  };

  $('#doResetPass').on('click', function() {
    var uid  = $('#resetUserId').val();
    var pass = $('#newPassInput').val();
    var $btn = $(this).prop('disabled', true);
    $.post(BASE_URL + 'client-portal/reset_password/' + uid, withCsrf({ password: pass }), function(res) {
      $btn.prop('disabled', false);
      if (res.success) {
        $('#resetPassMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text(res.message);
        setTimeout(function(){ hideModal('resetPassModal'); }, 1500);
      } else {
        $('#resetPassMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Could not reset password.');
      }
    }, 'json').fail(function(xhr) {
      $btn.prop('disabled', false);
      var msg = 'Could not reset password. Please refresh the page and try again.';
      if (xhr.status === 403) msg = 'Session expired. Please refresh the page and try again.';
      $('#resetPassMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(msg);
    });
  });

  window.openProjects = function(uid, name) {
    $('#projUserId').val(uid);
    $('#projUserName').text(name);
    $('.edit-proj-check').prop('checked', false);
    $('.edit-proj-perm').prop('disabled', true).val('view');
    $('#projMsg').addClass('d-none').removeClass('alert alert-success alert-danger').text('');
    $.get(BASE_URL + 'client-portal/user_projects/' + uid, function(res) {
      if (res.success && res.projects) {
        $.each(res.projects, function(pid, perm) {
          var check = document.querySelector('.edit-proj-check[data-project="' + pid + '"]');
          if (check) {
            check.checked = true;
            togglePermSelect(check);
            $('.edit-proj-perm[data-project="' + pid + '"]').val(perm);
          }
        });
      }
      showModal('projectsModal');
    }, 'json');
  };

  $('#saveProjects').on('click', function() {
    var uid = $('#projUserId').val();
    var $btn = $(this).prop('disabled', true).text('Saving...');
    $.post(BASE_URL + 'client-portal/save_user_projects/' + uid, withCsrf({
      projects: collectCheckedProjects('.edit-proj-check', '.edit-proj-perm')
    }), function(res) {
      $btn.prop('disabled', false).text('Save Assignments');
      if (res.success) {
        $('#projMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text(res.message);
        setTimeout(function(){ location.reload(); }, 1000);
      } else {
        $('#projMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Error');
      }
    }, 'json').fail(function() {
      $btn.prop('disabled', false).text('Save Assignments');
      $('#projMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text('Could not save. Please try again.');
    });
  });
})(jQuery);
</script>

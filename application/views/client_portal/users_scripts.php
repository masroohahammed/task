<script>
(function($) {
  var BASE_URL = '<?= base_url() ?>';
  var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  var CSRF_NAME = (document.querySelector('meta[name="csrf-param"]') || {}).content || 'csrf_token';

  function withCsrf(data) {
    var payload = data || {};
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

  function collectProjectPerms(selector) {
    var projects = {};
    $(selector).each(function() {
      var val = $(this).val();
      if (val) projects[$(this).data('project')] = val;
    });
    return projects;
  }

  $('#saveNewUser').on('click', function() {
    var $btn = $(this).prop('disabled', true).html('<i class="bi bi-check-lg me-1"></i> Saving...');
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
        projects:   collectProjectPerms('.add-proj-perm')
      }),
      success: function(res) {
        $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Add Member');
        if (res.success) {
          hideModal('addUserModal');
          location.reload();
        } else {
          $('#addUserMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Error');
        }
      },
      error: function() {
        $btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Add Member');
        $('#addUserMsg').removeClass('d-none').addClass('alert alert-danger').text('Error. Please try again.');
      }
    });
  });

  window.removeUser = function(uid, btn) {
    if (!confirm('Remove this team member?')) return;
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
    $('.edit-proj-perm').val('');
    $('#projMsg').addClass('d-none').removeClass('alert alert-success alert-danger').text('');
    $.get(BASE_URL + 'client-portal/user_projects/' + uid, function(res) {
      if (res.success && res.projects) {
        $.each(res.projects, function(pid, perm) {
          $('.edit-proj-perm[data-project="'+pid+'"]').val(perm);
        });
      }
      showModal('projectsModal');
    }, 'json');
  };

  $('#saveProjects').on('click', function() {
    var uid = $('#projUserId').val();
    $.post(BASE_URL + 'client-portal/save_user_projects/' + uid, withCsrf({
      projects: collectProjectPerms('.edit-proj-perm')
    }), function(res) {
      if (res.success) {
        $('#projMsg').removeClass('d-none alert-danger').addClass('alert alert-success').text(res.message);
        setTimeout(function(){ hideModal('projectsModal'); }, 1200);
      } else {
        $('#projMsg').removeClass('d-none alert-success').addClass('alert alert-danger').text(res.message || 'Error');
      }
    }, 'json');
  });
})(jQuery);
</script>

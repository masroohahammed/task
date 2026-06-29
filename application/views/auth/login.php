<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Techfod Task System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body class="auth-body">

<div class="auth-wrapper auth-wrapper-single">
  <div class="auth-panel-right auth-panel-full">
    <div class="auth-form-wrap">
      <div class="auth-form-header text-center text-lg-start">
        <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-2 mb-3">
          <div class="brand-icon-sm"><i class="bi bi-grid-3x3-gap-fill"></i></div>
          <span class="fw-800 fs-5">Techfod</span>
        </div>
        <h2>Welcome back</h2>
        <p>Sign in to continue</p>
      </div>

      <?php if (!empty($error)): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span><?= $error ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
      <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        <span><?= $success ?></span>
      </div>
      <?php endif; ?>

      <form action="<?= site_url('login') ?>" method="post" id="loginForm">
        <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()) ?>
        <input type="hidden" name="client_timezone" id="client_timezone" value="">
        <input type="hidden" name="geo_country_code" id="geo_country_code" value="">
        <input type="hidden" name="geo_timezone" id="geo_timezone" value="">

        <div class="form-floating mb-3">
          <input type="email" class="form-control <?= form_error('email') ? 'is-invalid' : '' ?>"
            id="email" name="email" placeholder="Email address"
            value="<?= set_value('email') ?>" required autocomplete="email">
          <label for="email"><i class="bi bi-envelope me-1"></i>Email address</label>
          <?php if (form_error('email')): ?>
          <div class="invalid-feedback"><?= form_error('email') ?></div>
          <?php endif; ?>
        </div>

        <div class="form-floating mb-4 position-relative">
          <input type="password" class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
            id="password" name="password" placeholder="Password" required autocomplete="current-password">
          <label for="password"><i class="bi bi-lock me-1"></i>Password</label>
          <button type="button" class="btn-eye" id="togglePassword">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
          <?php if (form_error('password')): ?>
          <div class="invalid-feedback"><?= form_error('password') ?></div>
          <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
        </div>
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" name="use_geo_on_login" value="1" id="useGeoOnLogin" checked>
          <label class="form-check-label small text-muted" for="useGeoOnLogin">
            Detect my location on sign-in to update timezone and country (optional)
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-login w-100">
          <span class="btn-text">Sign In</span>
          <i class="bi bi-arrow-right ms-2"></i>
        </button>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
try {
  document.getElementById('client_timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
} catch (e) {}

document.addEventListener('DOMContentLoaded', function() {
  var chk = document.getElementById('useGeoOnLogin');
  if (!chk || !chk.checked) return;
  fetch('https://ipapi.co/json/')
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d && d.country_code) document.getElementById('geo_country_code').value = String(d.country_code).toUpperCase();
      if (d && d.timezone) document.getElementById('geo_timezone').value = d.timezone;
    })
    .catch(function() {});
});

document.getElementById('togglePassword').addEventListener('click', function() {
  const pw = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (pw.type === 'password') {
    pw.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    pw.type = 'password';
    icon.className = 'bi bi-eye';
  }
});
</script>
</body>
</html>

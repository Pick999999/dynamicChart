<?php
/**
 * Sign In Page Content
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-signin">
  <div style="display:flex;align-items:center;justify-content:center;min-height:70vh;">
    <div class="card" style="max-width:440px;width:100%;padding:40px;">
      <div style="text-align:center;margin-bottom:32px;">
        <h2 style="font-size:28px;font-weight:800;margin-bottom:8px;">Welcome Back</h2>
        <p style="color:var(--text-tertiary);font-size:14px;">Enter your email and password to sign in</p>
      </div>

      <form onsubmit="return false;">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" placeholder="Your email address">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" class="form-input" placeholder="Your password">
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin:20px 0;">
          <label class="toggle-switch" style="display:flex;align-items:center;gap:10px;">
            <input type="checkbox">
            <span class="toggle-slider"></span>
            <span style="font-size:13px;color:var(--text-secondary);white-space:nowrap;position:relative;top:0;">Remember me</span>
          </label>
        </div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:14px;">
          SIGN IN
        </button>
      </form>

      <p style="text-align:center;margin-top:24px;font-size:13px;color:var(--text-tertiary);">
        Don't have an account?
        <a href="#" onclick="navigateToPage('signup');return false;" style="color:var(--accent-blue);text-decoration:none;font-weight:600;">Sign up</a>
      </p>
    </div>
  </div>
</div>

<script>
  function navigateToPage(page) {
    document.querySelector('.nav-link[data-page="'+page+'"]').click();
  }
</script>

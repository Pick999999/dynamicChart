<?php
/**
 * Sign Up Page Content
 */
header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-signup">
  <div style="display:flex;align-items:center;justify-content:center;min-height:70vh;">
    <div class="card" style="max-width:440px;width:100%;padding:40px;">
      <div style="text-align:center;margin-bottom:32px;">
        <h2 style="font-size:28px;font-weight:800;margin-bottom:8px;">Welcome!</h2>
        <p style="color:var(--text-tertiary);font-size:14px;">Use these awesome forms to register your account</p>
      </div>

      <form onsubmit="return false;">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" class="form-input" placeholder="Your full name">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-input" placeholder="Your email address">
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" class="form-input" placeholder="Your password">
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin:20px 0;">
          <label class="toggle-switch">
            <input type="checkbox">
            <span class="toggle-slider"></span>
          </label>
          <span style="font-size:13px;color:var(--text-secondary);">
            I agree to the <a href="#" style="color:var(--accent-blue);text-decoration:none;font-weight:600;">Terms and Conditions</a>
          </span>
        </div>
        <button class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;font-size:14px;">
          SIGN UP
        </button>
      </form>

      <p style="text-align:center;margin-top:24px;font-size:13px;color:var(--text-tertiary);">
        Already have an account?
        <a href="#" onclick="navigateToPage('signin');return false;" style="color:var(--accent-blue);text-decoration:none;font-weight:600;">Sign in</a>
      </p>
    </div>
  </div>
</div>

<script>
  function navigateToPage(page) {
    document.querySelector('.nav-link[data-page="'+page+'"]').click();
  }
</script>

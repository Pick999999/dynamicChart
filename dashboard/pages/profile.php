<?php
/**
 * Profile Page Content
 * Returns HTML fragment for the profile view
 */

header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-profile">
  <!-- Profile Header -->
  <div class="card profile-header-card" style="margin-bottom:24px;">
    <div class="profile-banner"></div>
    <div class="profile-info">
      <div class="profile-avatar-large">MJ</div>
      <div class="profile-meta">
        <h2>Mark Johnson</h2>
        <p>CEO / Co-Founder</p>
      </div>
      <div class="profile-stats">
        <div class="profile-stat">
          <div class="num">22</div>
          <div class="label">Projects</div>
        </div>
        <div class="profile-stat">
          <div class="num">10</div>
          <div class="label">Followers</div>
        </div>
        <div class="profile-stat">
          <div class="num">89</div>
          <div class="label">Following</div>
        </div>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <!-- Platform Settings -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Platform Settings</h3>
      </div>
      <p style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-tertiary);letter-spacing:1px;margin-bottom:12px;">ACCOUNT</p>
      <div class="setting-row">
        <div class="setting-info">
          <h4>Email me when someone follows me</h4>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-info">
          <h4>Email me when someone answers me</h4>
        </div>
        <label class="toggle-switch">
          <input type="checkbox">
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-info">
          <h4>Email me when someone mentions me</h4>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>

      <p style="font-size:11px;font-weight:700;text-transform:uppercase;color:var(--text-tertiary);letter-spacing:1px;margin:20px 0 12px;">APPLICATION</p>
      <div class="setting-row">
        <div class="setting-info">
          <h4>New launches and projects</h4>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-info">
          <h4>Monthly product updates</h4>
        </div>
        <label class="toggle-switch">
          <input type="checkbox">
          <span class="toggle-slider"></span>
        </label>
      </div>
      <div class="setting-row">
        <div class="setting-info">
          <h4>Subscribe to newsletter</h4>
        </div>
        <label class="toggle-switch">
          <input type="checkbox" checked>
          <span class="toggle-slider"></span>
        </label>
      </div>
    </div>

    <!-- Profile Information -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Profile Information</h3>
        <button class="btn btn-outline" style="font-size:12px;padding:6px 14px;">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
          Edit
        </button>
      </div>
      <p style="font-size:13px;color:var(--text-secondary);line-height:1.7;margin-bottom:20px;">
        Hi, I'm Mark Johnson, Decisions: If you can't decide, the answer is no. 
        If two equally difficult paths, choose the one more painful in the short term (pain avoidance is creating an illusion of equality).
      </p>
      <div style="display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;gap:8px;font-size:13px;">
          <span style="color:var(--text-tertiary);min-width:100px;">Full Name:</span>
          <span style="color:var(--text-primary);font-weight:600;">Mark Johnson</span>
        </div>
        <div style="display:flex;gap:8px;font-size:13px;">
          <span style="color:var(--text-tertiary);min-width:100px;">Mobile:</span>
          <span style="color:var(--text-primary);font-weight:600;">(44) 123 1234 123</span>
        </div>
        <div style="display:flex;gap:8px;font-size:13px;">
          <span style="color:var(--text-tertiary);min-width:100px;">Email:</span>
          <span style="color:var(--text-primary);font-weight:600;">mark@vision.ui</span>
        </div>
        <div style="display:flex;gap:8px;font-size:13px;">
          <span style="color:var(--text-tertiary);min-width:100px;">Location:</span>
          <span style="color:var(--text-primary);font-weight:600;">United States</span>
        </div>
        <div style="display:flex;gap:8px;font-size:13px;align-items:center;">
          <span style="color:var(--text-tertiary);min-width:100px;">Social:</span>
          <div style="display:flex;gap:12px;">
            <a href="#" style="color:var(--accent-blue);font-size:18px;text-decoration:none;">𝕏</a>
            <a href="#" style="color:var(--accent-blue);font-size:18px;text-decoration:none;">in</a>
            <a href="#" style="color:var(--accent-blue);font-size:18px;text-decoration:none;">f</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Conversations & Projects -->
  <div class="grid-2 mt-24">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Conversations</h3>
      </div>
      <div style="display:flex;flex-direction:column;gap:4px;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:var(--radius-sm);transition:0.2s;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(0,117,255,0.8);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">SF</div>
            <div>
              <h4 style="font-size:13px;font-weight:600;">Sophie B.</h4>
              <p style="font-size:12px;color:var(--text-tertiary);">Hi! I need more information...</p>
            </div>
          </div>
          <button class="btn btn-primary" style="padding:6px 12px;font-size:11px;">REPLY</button>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:var(--radius-sm);transition:0.2s;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(117,81,255,0.8);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">AM</div>
            <div>
              <h4 style="font-size:13px;font-weight:600;">Anne Marie</h4>
              <p style="font-size:12px;color:var(--text-tertiary);">Awesome work, congrats! 🎉</p>
            </div>
          </div>
          <button class="btn btn-primary" style="padding:6px 12px;font-size:11px;">REPLY</button>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:var(--radius-sm);transition:0.2s;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(1,181,116,0.8);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">IV</div>
            <div>
              <h4 style="font-size:13px;font-weight:600;">Ivanna</h4>
              <p style="font-size:12px;color:var(--text-tertiary);">About files I can...</p>
            </div>
          </div>
          <button class="btn btn-primary" style="padding:6px 12px;font-size:11px;">REPLY</button>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:var(--radius-sm);transition:0.2s;cursor:pointer;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
          <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:rgba(255,181,71,0.8);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">PT</div>
            <div>
              <h4 style="font-size:13px;font-weight:600;">Peterson</h4>
              <p style="font-size:12px;color:var(--text-tertiary);">Have a great afternoon...</p>
            </div>
          </div>
          <button class="btn btn-primary" style="padding:6px 12px;font-size:11px;">REPLY</button>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Projects</h3>
        <p class="card-subtitle">Architects design group</p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:20px;text-align:center;transition:0.2s;cursor:pointer;" onmouseover="this.style.borderColor='var(--border-active)'" onmouseout="this.style.borderColor='var(--border-color)'">
          <div style="width:50px;height:50px;border-radius:14px;background:rgba(0,117,255,0.15);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:24px;">🏢</div>
          <h4 style="font-size:14px;font-weight:700;margin-bottom:4px;">Modern</h4>
          <p style="font-size:12px;color:var(--text-tertiary);">As Uber works through...</p>
          <button class="btn btn-outline" style="margin-top:12px;font-size:11px;padding:6px 14px;width:100%;">VIEW PROJECT</button>
        </div>
        <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:20px;text-align:center;transition:0.2s;cursor:pointer;" onmouseover="this.style.borderColor='var(--border-active)'" onmouseout="this.style.borderColor='var(--border-color)'">
          <div style="width:50px;height:50px;border-radius:14px;background:rgba(117,81,255,0.15);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:24px;">🏠</div>
          <h4 style="font-size:14px;font-weight:700;margin-bottom:4px;">Scandinavian</h4>
          <p style="font-size:12px;color:var(--text-tertiary);">Music is something that...</p>
          <button class="btn btn-outline" style="margin-top:12px;font-size:11px;padding:6px 14px;width:100%;">VIEW PROJECT</button>
        </div>
        <div style="background:rgba(255,255,255,0.02);border:1px solid var(--border-color);border-radius:var(--radius-md);padding:20px;text-align:center;transition:0.2s;cursor:pointer;" onmouseover="this.style.borderColor='var(--border-active)'" onmouseout="this.style.borderColor='var(--border-color)'">
          <div style="width:50px;height:50px;border-radius:14px;background:rgba(1,181,116,0.15);margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:24px;">🏗️</div>
          <h4 style="font-size:14px;font-weight:700;margin-bottom:4px;">Minimalist</h4>
          <p style="font-size:12px;color:var(--text-tertiary);">Different people have...</p>
          <button class="btn btn-outline" style="margin-top:12px;font-size:11px;padding:6px 14px;width:100%;">VIEW PROJECT</button>
        </div>
      </div>
    </div>
  </div>
</div>

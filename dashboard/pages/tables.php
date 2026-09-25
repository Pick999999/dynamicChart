<?php
/**
 * Tables Page Content
 * Returns HTML fragment for the data tables view
 */

header('Content-Type: text/html; charset=utf-8');
?>

<div class="page-content" id="page-tables">
  <!-- Authors Table -->
  <div class="card" style="margin-bottom:24px;">
    <div class="card-header">
      <div>
        <h3 class="card-title">Authors Table</h3>
        <p class="card-subtitle">Member information and roles</p>
      </div>
      <button class="btn btn-primary" style="font-size:12px;padding:8px 16px;">+ Add New</button>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Author</th>
          <th>Function</th>
          <th>Status</th>
          <th>Employed</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div class="table-project" style="gap:14px;">
              <div class="table-project-icon" style="background:var(--accent-gradient);border-radius:12px;width:40px;height:40px;font-size:16px;font-weight:700;color:#fff;">MJ</div>
              <div>
                <span class="table-project-name">Mark Johnson</span>
                <p style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">mark@vision.ui</p>
              </div>
            </div>
          </td>
          <td>
            <div><span style="color:var(--text-primary);font-weight:600;">Manager</span></div>
            <div style="font-size:12px;color:var(--text-tertiary);">Organization</div>
          </td>
          <td><span class="card-badge success">Online</span></td>
          <td>23/04/18</td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:12px;font-weight:600;">Edit</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project" style="gap:14px;">
              <div class="table-project-icon" style="background:rgba(117,81,255,0.8);border-radius:12px;width:40px;height:40px;font-size:16px;font-weight:700;color:#fff;">AK</div>
              <div>
                <span class="table-project-name">Alexa Kurd</span>
                <p style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">alexa@vision.ui</p>
              </div>
            </div>
          </td>
          <td>
            <div><span style="color:var(--text-primary);font-weight:600;">Programmer</span></div>
            <div style="font-size:12px;color:var(--text-tertiary);">Developer</div>
          </td>
          <td><span class="card-badge" style="background:rgba(255,255,255,0.06);color:var(--text-tertiary);">Offline</span></td>
          <td>11/01/19</td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:12px;font-weight:600;">Edit</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project" style="gap:14px;">
              <div class="table-project-icon" style="background:rgba(0,212,255,0.8);border-radius:12px;width:40px;height:40px;font-size:16px;font-weight:700;color:#fff;">LM</div>
              <div>
                <span class="table-project-name">Laurent Michael</span>
                <p style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">laurent@vision.ui</p>
              </div>
            </div>
          </td>
          <td>
            <div><span style="color:var(--text-primary);font-weight:600;">Executive</span></div>
            <div style="font-size:12px;color:var(--text-tertiary);">Projects</div>
          </td>
          <td><span class="card-badge success">Online</span></td>
          <td>19/09/17</td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:12px;font-weight:600;">Edit</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project" style="gap:14px;">
              <div class="table-project-icon" style="background:rgba(1,181,116,0.8);border-radius:12px;width:40px;height:40px;font-size:16px;font-weight:700;color:#fff;">FP</div>
              <div>
                <span class="table-project-name">Freduardo Phil</span>
                <p style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">freduardo@vision.ui</p>
              </div>
            </div>
          </td>
          <td>
            <div><span style="color:var(--text-primary);font-weight:600;">Programmer</span></div>
            <div style="font-size:12px;color:var(--text-tertiary);">Developer</div>
          </td>
          <td><span class="card-badge success">Online</span></td>
          <td>24/12/08</td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:12px;font-weight:600;">Edit</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project" style="gap:14px;">
              <div class="table-project-icon" style="background:rgba(227,26,26,0.8);border-radius:12px;width:40px;height:40px;font-size:16px;font-weight:700;color:#fff;">DH</div>
              <div>
                <span class="table-project-name">Daniel Hamilton</span>
                <p style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">daniel@vision.ui</p>
              </div>
            </div>
          </td>
          <td>
            <div><span style="color:var(--text-primary);font-weight:600;">Manager</span></div>
            <div style="font-size:12px;color:var(--text-tertiary);">Executive</div>
          </td>
          <td><span class="card-badge" style="background:rgba(255,255,255,0.06);color:var(--text-tertiary);">Offline</span></td>
          <td>04/10/21</td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:12px;font-weight:600;">Edit</a></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Projects Table -->
  <div class="card">
    <div class="card-header">
      <div>
        <h3 class="card-title">Projects Table</h3>
        <p class="card-subtitle">Active projects and their progress</p>
      </div>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Project</th>
          <th>Budget</th>
          <th>Status</th>
          <th>Completion</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            <div class="table-project">
              <div class="table-project-icon" style="background:rgba(117,81,255,0.15);color:#7551FF;">⚡</div>
              <span class="table-project-name">Vision UI Dashboard</span>
            </div>
          </td>
          <td>$14,000</td>
          <td><span style="color:var(--text-primary);font-weight:600;">Working</span></td>
          <td>
            <div class="table-progress">
              <div class="progress-bar" style="width:120px;"><div class="progress-fill" style="width:60%"></div></div>
              <span class="progress-text">60%</span>
            </div>
          </td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:18px;">⋯</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project">
              <div class="table-project-icon" style="background:rgba(0,117,255,0.15);color:#0075FF;">🎯</div>
              <span class="table-project-name">Add Progress Track</span>
            </div>
          </td>
          <td>$3,000</td>
          <td><span style="color:var(--accent-red);font-weight:600;">Cancelled</span></td>
          <td>
            <div class="table-progress">
              <div class="progress-bar" style="width:120px;"><div class="progress-fill" style="width:10%;background:var(--accent-red);"></div></div>
              <span class="progress-text">10%</span>
            </div>
          </td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:18px;">⋯</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project">
              <div class="table-project-icon" style="background:rgba(1,181,116,0.15);color:#01B574;">🛒</div>
              <span class="table-project-name">Fix Platform Errors</span>
            </div>
          </td>
          <td>Not set</td>
          <td><span style="color:var(--accent-green);font-weight:600;">Done</span></td>
          <td>
            <div class="table-progress">
              <div class="progress-bar" style="width:120px;"><div class="progress-fill" style="width:100%;background:var(--accent-green);"></div></div>
              <span class="progress-text">100%</span>
            </div>
          </td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:18px;">⋯</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project">
              <div class="table-project-icon" style="background:rgba(0,212,255,0.15);color:#00D4FF;">📱</div>
              <span class="table-project-name">Launch Mobile App</span>
            </div>
          </td>
          <td>$20,500</td>
          <td><span style="color:var(--accent-green);font-weight:600;">Done</span></td>
          <td>
            <div class="table-progress">
              <div class="progress-bar" style="width:120px;"><div class="progress-fill" style="width:100%;background:var(--accent-green);"></div></div>
              <span class="progress-text">100%</span>
            </div>
          </td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:18px;">⋯</a></td>
        </tr>
        <tr>
          <td>
            <div class="table-project">
              <div class="table-project-icon" style="background:rgba(255,181,71,0.15);color:#FFB547;">📊</div>
              <span class="table-project-name">Add New Pricing Page</span>
            </div>
          </td>
          <td>$500</td>
          <td><span style="color:var(--text-primary);font-weight:600;">Working</span></td>
          <td>
            <div class="table-progress">
              <div class="progress-bar" style="width:120px;"><div class="progress-fill" style="width:25%"></div></div>
              <span class="progress-text">25%</span>
            </div>
          </td>
          <td><a href="#" style="color:var(--text-tertiary);text-decoration:none;font-size:18px;">⋯</a></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

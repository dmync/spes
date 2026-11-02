<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Life Backup — Sales Performance Platform (SPA)</title>

  <!-- libs -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

  <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
  <div class="app-shell">
    <header class="app-header shadow-sm">
      <div class="container d-flex align-items-center justify-content-between py-2">
        <div class="d-flex align-items-center gap-3">
          <img id="company_logo" src="assets/img/logo.png" alt="Life Backup" onerror="this.style.display='none'">
          <div>
            <div class="brand-title">Life Backup</div>
            <div class="brand-sub">Sales Performance Platform</div>
          </div>
        </div>

        <div class="d-flex align-items-center gap-3">
          <div id="user_info" class="text-end small text-muted"></div>
          <div id="global_actions" class="d-none">
            <button id="btn_notifications" class="btn btn-outline-light btn-sm"><i class="bi bi-bell"></i></button>
            <button id="btn_logout" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Sign out</button>
          </div>
        </div>
      </div>
    </header>

    <main class="container py-4">
      <!-- ROLE SELECTION -->
      <section id="role_selection_view" class="view">
        <div class="text-center mb-4">
          <h2>Welcome</h2>
          <p class="text-muted">Select your role first — then sign in.</p>
        </div>

        <div class="row g-3 justify-content-center">
          <?php
            $roles = [
              ['key'=>'manager','label'=>'Manager','icon'=>'bi-person-badge'],
              ['key'=>'supervisor','label'=>'Supervisor','icon'=>'bi-people'],
              ['key'=>'team_leader','label'=>'Team Leader','icon'=>'bi-people-fill'],
              ['key'=>'lia','label'=>'Life Insurance Advisor','icon'=>'bi-heart-pulse'],
              ['key'=>'agent','label'=>'Sales Agent','icon'=>'bi-person'],
              ['key'=>'cashier','label'=>'Cashier','icon'=>'bi-cash-stack']
            ];
            foreach ($roles as $r):
          ?>
          <div class="col-12 col-sm-6 col-md-4">
            <div class="role-card" data-role="<?=htmlspecialchars($r['key'])?>">
              <div class="role-card-body">
                <div class="role-icon"><i class="<?=htmlspecialchars($r['icon'])?>"></i></div>
                <div class="role-name"><?=htmlspecialchars($r['label'])?></div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- LOGIN -->
      <section id="login_view" class="view d-none">
        <div class="row justify-content-center">
          <div class="col-lg-6">
            <div class="card p-4 shadow-sm">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div><div id="login_role_label" class="login-role-badge">ROLE</div><div class="small text-muted">Secure access</div></div>
                <div class="small text-muted">Life Backup</div>
              </div>

              <div id="login_alert"></div>

              <form id="login_form">
                <input type="hidden" id="selected_role" name="selected_role" value="">
                <div class="mb-2">
                  <label class="form-label">Agent Code</label>
                  <input type="text" id="agent_code" name="agent_code" class="form-control" maxlength="20" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Password</label>
                  <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary w-100">Login</button>
                  <button type="button" id="btn_back_role" class="btn btn-outline-secondary">Back</button>
                </div>

                <div class="mt-3 small text-muted">Selected: <span id="login_role_display"></span></div>
              </form>
            </div>
          </div>
        </div>
      </section>

      <!-- MAIN APP -->
      <section id="app_view" class="view d-none">
        <div class="row">
          <nav id="sidebar" class="col-lg-3 mb-3">
            <div class="card p-3 shadow-sm">
              <div id="sidebar_header" class="mb-3">
                <div class="fw-bold" id="sidebar_role_label">Role</div>
                <div class="small text-muted" id="sidebar_user_name">User</div>
              </div>

              <ul id="sidebar_menu" class="list-unstyled mb-0"></ul>

              <div class="mt-3">
                <div id="quick_actions_container"></div>
              </div>
            </div>
          </nav>

          <section id="main_area" class="col-lg-9">
            <div id="main_content"></div>
          </section>
        </div>
      </section>

      <!-- CASHIER -->
      <section id="cashier_view" class="view d-none">
        <div class="card p-4 shadow-sm">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h4 class="mb-0">Cashier — Invoice Entry</h4>
              <div class="small text-muted">Enter premium collection invoices</div>
            </div>
            <div>
              <button id="btn_logout_cashier" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Sign Out</button>
            </div>
          </div>

          <div id="cashier_alert"></div>

          <form id="invoice_form" class="row g-2">
            <div class="col-md-4"><label class="form-label">Branch</label><select id="branch_code" name="branch_code" class="form-select"></select></div>
            <div class="col-md-4"><label class="form-label">Supervisor</label><select id="supervisor_code" name="supervisor_code" class="form-select"></select></div>
            <div class="col-md-4"><label class="form-label">Team Leader</label><select id="team_leader_code" name="team_leader_code" class="form-select"></select></div>

            <div class="col-md-4"><label class="form-label">Agent</label><select id="agent_select" name="agent_code" class="form-select"></select></div>
            <div class="col-md-4"><label class="form-label">Invoice Date</label><input type="date" id="invoice_date" name="invoice_date" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Cash Handed Over Date</label><input type="date" id="cash_handed_date" name="cash_handed_date" class="form-control"></div>

            <div class="col-md-4"><label class="form-label">Policy Number</label><input type="text" id="policy_number" name="policy_number" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Premium Amount (LKR)</label><input type="number" id="premium_amount" name="premium_amount" step="0.01" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Payment Frequency</label><select id="payment_frequency" name="payment_frequency" class="form-select"><option>Monthly</option><option>Quarter</option><option>By Annual</option><option>Annual</option></select></div>

            <div class="col-md-4 d-flex align-items-center"><label class="form-check-label me-2">Agent Signature</label><input type="checkbox" id="agent_signature" name="agent_signature" class="form-check-input"></div>

            <div class="col-12 mt-3"><div class="d-flex gap-2"><button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Save Invoice</button><button type="button" id="btn_export_invoice_pdf" class="btn btn-outline-light"><i class="bi bi-printer"></i> Export PDF</button></div></div>
          </form>
        </div>
      </section>

    </main>

    <footer class="text-center small text-muted py-3">
      <div>Life Backup — Sales Performance Platform</div>
    </footer>
  </div>

  <!-- Recruit Modal -->
  <div class="modal fade darkglass-modal" id="modalRecruit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Recruit new staff</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formRecruit">
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Role</label>
              <select id="recruit_role" class="form-select" required>
                <option value="supervisor">Supervisor</option>
                <option value="lia">Life Insurance Advisor</option>
                <option value="cashier">Cashier</option>
                <option value="team_leader">Team Leader</option>
                <option value="agent">Agent</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Agent Code</label>
              <input id="recruit_code" class="form-control" maxlength="20" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Full Name</label>
              <input id="recruit_name" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Branch</label>
              <select id="recruit_branch" class="form-select"></select>
            </div>
            <div class="form-text text-muted">A default password will be created. Change later in admin.</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Create</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Promote Modal -->
  <div class="modal fade darkglass-modal" id="modalPromote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Request / Approve Promotion</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formPromote">
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Agent Code</label>
              <input id="promote_code" class="form-control" maxlength="20" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Promote To</label>
              <select id="promote_to" class="form-select" required>
                <option value="team_leader">Team Leader</option>
                <option value="supervisor">Supervisor</option>
                <option value="manager">Manager</option>
                <option value="lia">Life Insurance Advisor</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Reason</label>
              <textarea id="promote_reason" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-text text-muted">Supervisor/Agent use this to request; manager uses this modal to approve pending promotions.</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Transfer Modal -->
  <div class="modal fade darkglass-modal" id="modalTransfer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Transfer / Assign Team</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formTransfer">
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Agent Code to Transfer</label>
              <input id="transfer_code" class="form-control" maxlength="20" required>
            </div>
            <div class="mb-2">
              <label class="form-label">New Supervisor (optional)</label>
              <select id="transfer_supervisor" class="form-select"></select>
            </div>
            <div class="mb-2">
              <label class="form-label">New Team Leader (optional)</label>
              <select id="transfer_tl" class="form-select"></select>
            </div>
            <div class="mb-2">
              <label class="form-label">New Branch (optional)</label>
              <select id="transfer_branch" class="form-select"></select>
            </div>
            <div class="form-text text-muted">Leave fields blank to keep current assignment.</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Apply Transfer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Resign Modal -->
  <div class="modal fade darkglass-modal" id="modalResign" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Resignation</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formResign">
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Agent Code (you or other)</label>
              <input id="resign_code" class="form-control" maxlength="20" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Reason (optional)</label>
              <textarea id="resign_reason" class="form-control" rows="3"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Submit Resignation</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Manage Targets Modal -->
  <div class="modal fade darkglass-modal" id="modalTargets" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Manage Monthly Targets</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="formTargets">
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Role</label>
              <select id="target_role" class="form-select" required>
                <option value="agent">Agent</option>
                <option value="lia">Life Insurance Advisor</option>
                <option value="team_leader">Team Leader</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">Monthly Policies Target (number)</label>
              <input id="target_policies" class="form-control" type="number" min="0" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Monthly Premium Target (LKR)</label>
              <input id="target_premium" class="form-control" type="number" min="0" step="0.01" required>
            </div>
            <div class="form-text text-muted">Targets set here are stored in activity_log (extend to dedicated targets table if required).</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Targets</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>

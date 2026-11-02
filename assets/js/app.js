$(function(){
  // init
  $('.view').addClass('d-none');
  $('#role_selection_view').removeClass('d-none');

  function applyHashRouting() {
    let hash = window.location.hash.replace('#', '').trim();

    if (!hash) return;

    // only load dashboard if the user is logged in
    if (logged && role) {
        loadRoleDashboard(hash);
    }
  }

  // when user manually changes #hash
  window.onhashchange = function() {
      applyHashRouting();
  };

  // when page loads
  $(document).ready(function () {
      applyHashRouting();
  });

  // hide duplicate cashier logout (keep top-right only)
  $('#btn_logout_cashier').hide();

  // toast helper
  function toast(msg,type='info'){
    const cls = (type==='success')?'bg-success':(type==='error'?'bg-danger':'bg-secondary');
    const $t = $(`<div class="toast align-items-center text-white ${cls} border-0 position-fixed" style="right:20px;top:20px;z-index:9999"><div class="d-flex"><div class="toast-body">${msg}</div><button type="button" class="btn-close btn-close-white ms-auto me-2" data-bs-dismiss="toast"></button></div></div>`);
    $('body').append($t);
    var bs = new bootstrap.Toast($t[0],{delay:3500}); bs.show();
    setTimeout(()=> $t.remove(),3800);
  }

  function postJSON(url,data,cb){ $.post(url,data,cb,'json').fail(()=>cb({status:'error',message:'Server error'})); }
  function getJSON(url,cb){ $.getJSON(url,cb).fail(()=>cb({status:'error',message:'Server error'})); }

  // role selection
  $('.role-card').on('click', function(){ const role=$(this).data('role'); $('#selected_role').val(role); $('#login_role_label').text(role.toUpperCase()); $('#login_role_display').text(role.charAt(0).toUpperCase()+role.slice(1)); $('#role_selection_view').addClass('d-none'); $('#login_view').removeClass('d-none'); });

  $('#btn_back_role').on('click', function(){ $('#login_view').addClass('d-none'); $('#role_selection_view').removeClass('d-none'); $('#login_form')[0].reset(); });

  // login
  $('#login_form').on('submit', function(e){
    e.preventDefault();
    $('#login_alert').html('');
    const selected_role = $('#selected_role').val();
    const agent_code = $('#agent_code').val().trim();
    const password = $('#password').val();
    if (!selected_role){ toast('Select role','error'); return; }
    postJSON('api/login.php',{agent_code,password}, function(resp){
      if (!resp || resp.status!=='success'){ $('#login_alert').html('<div class="alert alert-danger">'+(resp&&resp.message?resp.message:'Login failed')+'</div>'); return; }
      if (resp.role !== selected_role){ $('#login_alert').html('<div class="alert alert-danger">Selected role does not match account role.</div>'); return; }

      $('#user_info').text(resp.name + ' (' + resp.role + ')');
      $('#global_actions').removeClass('d-none');

      // cashier special: top-right logout only
      if (resp.role === 'cashier'){
        $('#login_view').addClass('d-none'); $('#cashier_view').removeClass('d-none'); $('#app_view').addClass('d-none'); loadDropdowns(); return;
      }

      // normal app
      $('#login_view').addClass('d-none'); $('#app_view').removeClass('d-none'); $('#sidebar_role_label').text(resp.role.toUpperCase()); $('#sidebar_user_name').text(resp.name);
      buildSidebarForRole(resp.role); renderInitialDashboard(resp.role);
      if (window.dashboardInterval) clearInterval(window.dashboardInterval); window.dashboardInterval = setInterval(()=>renderInitialDashboard(resp.role), 12000);
      loadDropdowns();
    });
  });

  // logout
  $('#btn_logout').on('click', function(){ postJSON('api/logout.php',{},()=>location.reload()); });

  // load dropdowns
  function loadDropdowns(){
    getJSON('api/get_dropdowns.php', function(resp){
      if (!resp || resp.status!=='success') return;
      $('#branch_code').empty(); resp.branches.forEach(b=>$('#branch_code').append(`<option value="${b.branch_code}">${b.branch_name} (${b.branch_code})</option>`));
      $('#supervisor_code').empty(); resp.supervisors.forEach(s=>$('#supervisor_code').append(`<option value="${s.agent_code}">${s.agent_code} - ${s.name}</option>`));
      $('#team_leader_code').empty(); resp.team_leaders.forEach(t=>$('#team_leader_code').append(`<option value="${t.agent_code}">${t.agent_code} - ${t.name}</option>`));
      $('#agent_select').empty(); resp.agents.forEach(a=>$('#agent_select').append(`<option value="${a.agent_code}">${a.agent_code} - ${a.name}</option>`));
      $('#recruit_branch,#transfer_branch').empty(); resp.branches.forEach(b=>$('#recruit_branch,#transfer_branch').append(`<option value="${b.branch_code}">${b.branch_name} (${b.branch_code})</option>`));
      $('#transfer_supervisor').empty().append('<option value="">-- None --</option>'); resp.supervisors.forEach(s=>$('#transfer_supervisor').append(`<option value="${s.agent_code}">${s.agent_code} - ${s.name}</option>`));
      $('#transfer_tl').empty().append('<option value="">-- None --</option>'); resp.team_leaders.forEach(t=>$('#transfer_tl').append(`<option value="${t.agent_code}">${t.agent_code} - ${t.name}</option>`));
    });
  }

  // cashier invoice save
  $('#invoice_form').on('submit', function(e){
    e.preventDefault();
    postJSON('api/save_invoice.php', $(this).serialize(), function(resp){
      if (resp && resp.status==='success'){ $('#cashier_alert').html('<div class="alert alert-success">Saved</div>'); toast('Saved','success'); $('#invoice_form')[0].reset(); postJSON('api/activity_log.php',{action:'save_invoice', agent_code: $('#agent_select').val()}, ()=>{}); }
      else { $('#cashier_alert').html('<div class="alert alert-danger">'+(resp&&resp.message?resp.message:'Save failed')+'</div>'); toast('Save failed','error'); }
    });
  });

  // sidebar builder & quick actions
  function buildSidebarForRole(role){
    const menu = $('#sidebar_menu').empty();
    const items = {
      'manager':[ {id:'mgr_overview',label:'Overview'},{id:'mgr_org',label:'Organization'},{id:'mgr_recruit',label:'Recruitment'},{id:'mgr_promotions',label:'Promotions'},{id:'mgr_reports',label:'Reports'} ],
      'supervisor':[ {id:'sup_overview',label:'Team Overview'},{id:'sup_tl',label:'Team Leaders'},{id:'sup_requests',label:'Requests'} ],
      'team_leader':[ {id:'tl_agents',label:'Agents'},{id:'tl_requests',label:'Requests'} ],
      'lia':[ {id:'lia_reports',label:'Reports'} ],
      'agent':[ {id:'agent_reports',label:'Reports'} ]
    };
    (items[role]||[]).forEach(it=> menu.append(`<li class="mb-2"><a href="#" class="text-white nav-link p-2" data-menu="${it.id}">${it.label}</a></li>`));
    $('#sidebar_menu a').on('click', function(e){ e.preventDefault(); handleMenuClick($(this).data('menu')); });

    // quick actions per role
    const q = $('#quick_actions_container').empty();
    if (role === 'manager'){
      q.append(`<div class="d-grid gap-2">
        <button id="qa_recruit_supervisor" class="btn btn-outline-light btn-sm">Recruit Supervisor(s)</button>
        <button id="qa_recruit_lia" class="btn btn-outline-light btn-sm">Recruit Life Advisor(s)</button>
        <button id="qa_recruit_cashier" class="btn btn-outline-light btn-sm">Recruit Cashier</button>
        <button id="qa_approve_promotions" class="btn btn-outline-light btn-sm">Approve Promotions</button>
        <button id="qa_transfer" class="btn btn-outline-light btn-sm">Transfer / Change Team</button>
        <button id="qa_view_hierarchy" class="btn btn-outline-light btn-sm">View Branch Hierarchy</button>
        <button id="qa_manage_targets" class="btn btn-outline-light btn-sm">Manage Staff Targets</button>
        <button id="qa_view_performance" class="btn btn-outline-light btn-sm">View Branch Performance</button>
        <button id="qa_resignation" class="btn btn-outline-light btn-sm">Resignation Requests</button>
      </div>`);
      $('#qa_recruit_supervisor').on('click', ()=>openRecruitModal('supervisor'));
      $('#qa_recruit_lia').on('click', ()=>openRecruitModal('lia'));
      $('#qa_recruit_cashier').on('click', ()=>openRecruitModal('cashier'));
      $('#qa_approve_promotions').on('click', loadPromotionsForApproval);
      $('#qa_transfer').on('click', ()=>$('#modalTransfer').modal('show'));
      $('#qa_view_hierarchy').on('click', loadOrgAdmin);
      $('#qa_manage_targets').on('click', ()=>$('#modalTargets').modal('show'));
      $('#qa_view_performance').on('click', ()=>loadRoleDashboard('manager'));
      $('#qa_resignation').on('click', loadResignRequests);
    } else if (role === 'supervisor') {
      q.append(`<div class="d-grid gap-2">
        <button id="qa_recruit_tl" class="btn btn-outline-light btn-sm">Recruit Team Lead(s)</button>
        <button id="qa_recruit_agent" class="btn btn-outline-light btn-sm">Recruit Agent(s)</button>
        <button id="qa_transfer_agents" class="btn btn-outline-light btn-sm">Transfer Agents</button>
        <button id="qa_request_promotions" class="btn btn-outline-light btn-sm">Request Promotions</button>
        <button id="qa_view_team" class="btn btn-outline-light btn-sm">View Team</button>
      </div>`);
      $('#qa_recruit_tl').on('click', ()=>openRecruitModal('team_leader'));
      $('#qa_recruit_agent').on('click', ()=>openRecruitModal('agent'));
      $('#qa_transfer_agents').on('click', ()=>$('#modalTransfer').modal('show'));
      $('#qa_request_promotions').on('click', ()=>$('#modalPromote').modal('show'));
      $('#qa_view_team').on('click', ()=>loadRoleDashboard('supervisor'));
    } else if (role === 'team_leader') {
      q.append(`<div class="d-grid gap-2">
        <button id="qa_recruit_agent_tl" class="btn btn-outline-light btn-sm">Recruit Agent(s)</button>
        <button id="qa_assign_agents" class="btn btn-outline-light btn-sm">Assign Agents</button>
        <button id="qa_request_promo_tl" class="btn btn-outline-light btn-sm">Request Promotion</button>
        <button id="qa_view_team_tl" class="btn btn-outline-light btn-sm">View Team</button>
      </div>`);
      $('#qa_recruit_agent_tl').on('click', ()=>openRecruitModal('agent'));
      $('#qa_assign_agents').on('click', ()=>$('#modalTransfer').modal('show'));
      $('#qa_request_promo_tl').on('click', ()=>$('#modalPromote').modal('show'));
      $('#qa_view_team_tl').on('click', ()=>loadRoleDashboard('team_leader'));
    } else if (role === 'agent' || role === 'lia') {
      q.append(`<div class="d-grid gap-2">
        <button id="qa_request_promo_agent" class="btn btn-outline-light btn-sm">Request Promotion</button>
        <button id="qa_resign_self" class="btn btn-outline-light btn-sm">Resign</button>
      </div>`);
      $('#qa_request_promo_agent').on('click', ()=>$('#modalPromote').modal('show'));
      $('#qa_resign_self').on('click', ()=>$('#modalResign').modal('show'));
    }
  }

  // recruit modal
  function openRecruitModal(role){
    $('#recruit_role').val(role); $('#recruit_code').val(''); $('#recruit_name').val(''); $('#modalRecruit').modal('show');
  }
  $('#formRecruit').on('submit', function(e){
    e.preventDefault();
    const payload = { agent_code: $('#recruit_code').val().trim(), name: $('#recruit_name').val().trim(), role: $('#recruit_role').val(), branch_code: $('#recruit_branch').val() };
    if (!payload.agent_code || !payload.name){ toast('Enter code & name','error'); return; }
    postJSON('api/add_agent.php', payload, function(resp){ if (resp && resp.status==='success'){ toast('Recruit added','success'); $('#modalRecruit').modal('hide'); loadDropdowns(); loadRoleDashboard($('#sidebar_role_label').text().toLowerCase()); } else toast(resp&&resp.message?resp.message:'Failed','error'); });
  });

  // promote
  function promoteCreateHandler(e){
    e.preventDefault();
    const payload = { agent_code: $('#promote_code').val().trim(), to_role: $('#promote_to').val(), reason: $('#promote_reason').val().trim() };
    if (!payload.agent_code || !payload.to_role){ toast('Fill required fields','error'); return; }
    postJSON('api/promote.php', payload, function(resp){ if (resp && resp.status==='success'){ toast('Promotion request submitted','success'); $('#modalPromote').modal('hide'); } else toast(resp&&resp.message?resp.message:'Failed','error'); });
  }
  $('#formPromote').off('submit').on('submit', promoteCreateHandler);

  // promotions approval list (manager)
  function loadPromotionsForApproval(){
    getJSON('api/list_promotions.php', function(resp){
      if (!resp || resp.status!=='success' || !resp.data || resp.data.length===0){ toast('No pending promotions','info'); return; }
      let html = `<div class="card p-3"><h5>Pending Promotions</h5><table class="table table-dark table-sm"><thead><tr><th>ID</th><th>Agent</th><th>To</th><th>Requested At</th><th>Action</th></tr></thead><tbody>`;
      resp.data.forEach(p => { if (p.status==='pending') html += `<tr><td>${p.promotion_id}</td><td>${p.agent_code} - ${p.agent_name||''}</td><td>${p.to_role}</td><td>${p.requested_at}</td><td><button class="btn btn-sm btn-success btn-approve-promo" data-id="${p.promotion_id}">Approve</button> <button class="btn btn-sm btn-danger btn-reject-promo" data-id="${p.promotion_id}">Reject</button></td></tr>`; });
      html += `</tbody></table></div>`;
      $('#main_content').html(html);
      $('.btn-approve-promo').on('click', function(){ const id=$(this).data('id'); postJSON('api/approve_promotion.php',{promotion_id:id, action:'approve'}, function(r){ if (r && r.status==='success'){ toast('Approved','success'); loadPromotionsForApproval(); } else toast('Failed','error'); }); });
      $('.btn-reject-promo').on('click', function(){ const id=$(this).data('id'); postJSON('api/approve_promotion.php',{promotion_id:id, action:'reject'}, function(r){ if (r && r.status==='success'){ toast('Rejected','success'); loadPromotionsForApproval(); } else toast('Failed','error'); }); });
    });
  }

  // transfer
  $('#formTransfer').on('submit', function(e){
    e.preventDefault();
    const payload = { agent_code: $('#transfer_code').val().trim(), supervisor_code: $('#transfer_supervisor').val() || null, team_leader_code: $('#transfer_tl').val() || null, branch_code: $('#transfer_branch').val() || null };
    if (!payload.agent_code){ toast('Agent code required','error'); return; }
    postJSON('api/transfer.php', payload, function(resp){ if (resp && resp.status==='success'){ toast('Transfer applied','success'); $('#modalTransfer').modal('hide'); loadDropdowns(); renderInitialDashboard($('#sidebar_role_label').text().toLowerCase()); } else toast(resp&&resp.message?resp.message:'Failed','error'); });
  });

  // resign
  $('#formResign').on('submit', function(e){
    e.preventDefault();
    const payload = { agent_code: $('#resign_code').val().trim(), reason: $('#resign_reason').val().trim() };
    if (!payload.agent_code){ toast('Agent code required','error'); return; }
    postJSON('api/resign.php', payload, function(resp){ if (resp && resp.status==='success'){ toast('Resignation recorded','success'); $('#modalResign').modal('hide'); loadDropdowns(); renderInitialDashboard($('#sidebar_role_label').text().toLowerCase()); } else toast(resp&&resp.message?resp.message:'Failed','error'); });
  });

  // manage targets
  $('#formTargets').on('submit', function(e){
    e.preventDefault();
    const payload = { role: $('#target_role').val(), policies: $('#target_policies').val(), premium: $('#target_premium').val() };
    if (!payload.role){ toast('Select role','error'); return; }
    postJSON('api/manage_targets.php', payload, function(resp){ if (resp && resp.status==='success'){ toast('Targets saved','success'); $('#modalTargets').modal('hide'); } else toast(resp&&resp.message?resp.message:'Failed','error'); });
  });

  // resign requests listing
  function loadResignRequests(){ getJSON('api/activity_log_list.php', function(resp){ if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="card p-3"><h5>Resign Requests</h5><div class="small text-muted">No data</div></div>'); return; } const items = resp.data.filter(x=>x.action==='resign'); if (!items.length){ $('#main_content').html('<div class="card p-3"><h5>Resign Requests</h5><div class="small text-muted">No resign events</div></div>'); return; } let html = `<div class="card p-3"><h5>Resign Events</h5><table class="table table-dark table-sm"><thead><tr><th>Agent</th><th>Reason</th><th>Time</th></tr></thead><tbody>`; items.forEach(it=> html += `<tr><td>${it.agent_code||''}</td><td>${it.details||''}</td><td>${it.created_at}</td></tr>`); html += `</tbody></table></div>`; $('#main_content').html(html); }); }

  // menu click handler (maps to our APIs)
  function handleMenuClick(id){
    if (!id) return;
    switch(id){
      case 'mgr_overview': loadRoleDashboard('manager'); break;
      case 'mgr_org': loadOrgAdmin(); break;
      case 'mgr_recruit': loadRecruitsPanel(); break;
      case 'mgr_promotions': loadPromotionsForApproval(); break;
      case 'mgr_reports': loadReports(); break;
      case 'sup_tl': loadSupervisorTeamLeaders(); break;
      case 'sup_requests': loadSupervisorRequests(); break;
      case 'sup_overview': loadRoleDashboard('supervisor'); break;
      case 'tl_agents': loadTeamLeaderAgents(); break;
      case 'tl_requests': loadTeamLeaderRequests(); break;
      case 'lia_reports': loadLIAReports(); break;
      case 'agent_reports': loadAgentReports(); break;
      default: loadRoleDashboard('manager'); break;
    }
  }

  function renderInitialDashboard(role){ loadRoleDashboard(role); }

  function loadRoleDashboard(role){
    window.location.hash = role
    getJSON('api/get_dashboard.php?role='+encodeURIComponent(role), function(resp){
      if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">Unable to load dashboard</div>'); return; }
      renderRoleDashboard(role, resp.data);
    });
  }

  function renderRoleDashboard(role, data){
    if (role==='manager') return renderManagerDashboard(data);
    if (role==='supervisor') return renderSupervisorDashboard(data);
    if (role==='team_leader') return loadTeamLeaderByCode(); // will call tl endpoint for current TL
    if (role==='lia') return loadLIAReports();
    if (role==='agent') return loadAgentReports();
    $('#main_content').html('<div class="alert alert-info">No dashboard for this role.</div>');
  }

  // Manager dashboard
  function renderManagerDashboard(data){
    data = data || {};
    const metrics = data.metrics || [{title:'Total Policies',value_display:'—',percent:0}];
    let html = `<div class="row g-3">`;
    html += `<div class="col-12"><div class="card p-3"><h5>Branch Overview</h5></div></div>`;
    html += `<div class="col-lg-8"><div class="row g-3">`;
    metrics.forEach(m => html += `<div class="col-md-6"><div class="metric-card p-3"><div class="metric-title">${m.title}</div><div class="metric-value">${m.value_display||'0'}</div><div class="mt-2"><div class="progress"><div class="bar" style="width:${Math.min(100,Math.round(m.percent||0))}%;"></div></div><div class="small text-muted mt-1">${Math.round(m.percent||0)}%</div></div></div></div>`);
    html += `</div></div>`;
    html += `<div class="col-lg-4"><div class="card p-3 mb-3"><h6>Trends</h6><canvas id="mgr_trends" height="160"></canvas></div><div class="card p-3"><h6>Distribution</h6><canvas id="mgr_donut" height="160"></canvas></div></div>`;
    html += `<div class="col-12"><div class="card p-3"><h5>Organization Structure</h5><div id="mgr_org_tree" style="max-height:420px;overflow:auto"></div></div></div>`;
    html += `</div>`;
    $('#main_content').html(html);

    const trends = data.trends || {labels:['Jan','Feb','Mar'],policies:[5,10,8]};
    const donut = data.distribution || {labels:['Agents','TLs','Sup','LIA'],values:[60,20,10,10]};
    try { const ctx = document.getElementById('mgr_trends').getContext('2d'); if (window.mgrTrendsChart) window.mgrTrendsChart.destroy(); window.mgrTrendsChart = new Chart(ctx,{type:'line',data:{labels:trends.labels,datasets:[{label:'Policies',data:trends.policies,fill:true}]}, options:{responsive:true}}); } catch(e){ console.warn(e); }
    try { const ctx2 = document.getElementById('mgr_donut').getContext('2d'); if (window.mgrDonut) window.mgrDonut.destroy(); window.mgrDonut = new Chart(ctx2,{type:'doughnut',data:{labels:donut.labels,datasets:[{data:donut.values}]}, options:{responsive:true}}); } catch(e){ console.warn(e); }

    getJSON('api/org_tree.php', function(r){ if (!r || r.status!=='success'){ $('#mgr_org_tree').html('<div class="small text-muted">No organization data</div>'); return; } $('#mgr_org_tree').html(renderOrgTreeHtml(r.data)); });
  }

  // Supervisor dashboard
  function renderSupervisorDashboard(data){
    data = data || {};
    let html = `<div class="row"><div class="col-lg-8"><div class="card p-3 mb-3"><h5>Supervisor — Team Overview</h5><div id="sup_team_metrics"></div></div></div><div class="col-lg-4"><div class="card p-3"><h5>Recent Activity</h5><div id="sup_recent"></div></div></div></div>`;
    $('#main_content').html(html);
    const metrics = data.metrics || [{title:'Team Policies', value_display:'0'},{title:'Team Premium (LKR)', value_display:'0'},{title:'Team Members', value_display:'0'}];
    let mm = ''; metrics.forEach(m=> mm += `<div class="metric-card mb-2 p-2"><div class="metric-title">${m.title}</div><div class="metric-value">${m.value_display}</div></div>`);
    $('#sup_team_metrics').html(mm);
    const recent = data.recent_invoices || [];
    let list = '<ul class="list-group list-group-flush">'; recent.slice(0,8).forEach(r => list += `<li class="list-group-item bg-transparent">${r.policy_number} — ${r.premium_amount} — ${r.agent_code}</li>`); list += '</ul>';
    $('#sup_recent').html(list);
  }

  // Supervisor: Team Leaders list
  function loadSupervisorTeamLeaders(){
    getJSON('api/supervisor_tl_list.php', function(resp){
      if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">Unable to load team leaders</div>'); return; }
      let html = `<div class="card p-3"><h5>Team Leaders</h5><table class="table table-dark table-sm"><thead><tr><th>TL Code</th><th>Name</th><th>Agents</th><th>Policies (Mth)</th><th>Actions</th></tr></thead><tbody>`;
      resp.data.forEach(tl=> html += `<tr><td>${tl.agent_code}</td><td>${tl.name}</td><td>${tl.agent_count||0}</td><td>${tl.month_policies||0}</td><td><button class="btn btn-sm btn-outline-light btn-view-tl" data-code="${tl.agent_code}">View</button></td></tr>`);
      html += `</tbody></table></div>`; $('#main_content').html(html);
      $('.btn-view-tl').on('click', function(){ const code=$(this).data('Code') || $(this).data('code'); loadTeamLeaderByCode(code); });
    });
  }

  function loadTeamLeaderByCode(tl_code){
    if (!tl_code) return loadRoleDashboard('team_leader');
    getJSON('api/teamleader_dashboard.php?tl='+encodeURIComponent(tl_code), function(resp){
      if (!resp || resp.status!=='success'){ toast('Unable to load TL data','error'); return; }
      // show TL dashboard using returned summary & top_agents
      const d = resp.data || {}; let html = `<div class="card p-3"><h5>Team Leader: ${tl_code}</h5><div class="row"><div class="col-md-4"><div class="metric-card p-2"><div class="metric-title">Policies (Mth)</div><div class="metric-value">${d.summary?d.summary.policies:0}</div></div></div><div class="col-md-4"><div class="metric-card p-2"><div class="metric-title">Premium (LKR)</div><div class="metric-value">${d.summary?d.summary.premium:0}</div></div></div></div><h6 class="mt-3">Top Agents</h6><table class="table table-dark table-sm"><thead><tr><th>Agent</th><th>Policies</th><th>Premium</th></tr></thead><tbody>`;
      (d.top_agents||[]).forEach(a=> html += `<tr><td>${a.agent_code}</td><td>${a.policies||0}</td><td>${a.premium||0}</td></tr>`);
      html += `</tbody></table></div>`; $('#main_content').html(html);
    });
  }

  // Supervisor: Requests
  function loadSupervisorRequests(){
    getJSON('api/supervisor_requests.php', function(resp){
      if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">No requests</div>'); return; }
      let html = `<div class="card p-3"><h5>Requests</h5><table class="table table-dark table-sm"><thead><tr><th>Agent</th><th>To Role</th><th>Status</th><th>Requested At</th></tr></thead><tbody>`;
      resp.data.forEach(r=> html += `<tr><td>${r.agent_code}</td><td>${r.to_role||''}</td><td>${r.status}</td><td>${r.requested_at}</td></tr>`);
      html += `</tbody></table></div>`; $('#main_content').html(html);
    });
  }

  // Team Leader: Agents list
  function loadTeamLeaderAgents(){
    getJSON('api/tl_agents_list.php', function(resp){
      if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">Unable to load agents</div>'); return; }
      let html = `<div class="card p-3"><h5>Agents</h5><table class="table table-dark table-sm"><thead><tr><th>Code</th><th>Name</th><th>Policies (Mth)</th><th>Premium</th></tr></thead><tbody>`;
      resp.data.forEach(a=> html += `<tr><td>${a.agent_code}</td><td>${a.name}</td><td>${a.month_policies||0}</td><td>${a.month_premium||0}</td></tr>`);
      html += `</tbody></table></div>`; $('#main_content').html(html);
    });
  }

  // Team Leader: Requests
  function loadTeamLeaderRequests(){
    getJSON('api/tl_requests.php', function(resp){
      if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">No requests</div>'); return; }
      let html = `<div class="card p-3"><h5>Requests</h5><table class="table table-dark table-sm"><thead><tr><th>Agent</th><th>To Role</th><th>Status</th><th>Requested At</th></tr></thead><tbody>`;
      resp.data.forEach(r=> html += `<tr><td>${r.agent_code}</td><td>${r.to_role||''}</td><td>${r.status}</td><td>${r.requested_at}</td></tr>`);
      html += `</tbody></table></div>`; $('#main_content').html(html);
    });
  }

// LIA reports
function loadLIAReports(){
  getJSON('api/lia_reports.php', function(resp){
    if (!resp || resp.status!=='success'){ 
      $('#main_content').html('<div class="alert alert-warning">No reports</div>'); 
      return; 
    }
    const d = resp.data || {}; 

    let html = `<div class="card p-3">
                  <h5>My Reports</h5>
                  <div class="row">
                    <div class="col-lg-8">
                      <canvas id="lia_trend" height="140"></canvas>
                    </div>
                    <div class="col-lg-4" id="lia_summary" class="p-2">
                    </div>
                  </div>
                </div>`;
    $('#main_content').html(html);

    // Chart
    try { 
      const ctx = document.getElementById('lia_trend').getContext('2d'); 
      if (window.liaTrend) window.liaTrend.destroy(); 
      window.liaTrend = new Chart(ctx,{
        type:'bar', 
        data:{
          labels: d.trends.labels, 
          datasets:[{label:'Policies', data:d.trends.policies}] 
        }, 
        options:{responsive:true} 
      }); 
    } catch(e){ console.warn(e); }

    // Card summary
    let summaryHtml = '';
    const summary = d.summary || {};
    for (const key in summary){
      summaryHtml += `<div class="card mb-2">
                        <div class="card-body p-2">
                          <h6 class="card-title mb-1">${key}</h6>
                          <p class="card-text mb-0">${summary[key]}</p>
                        </div>
                      </div>`;
    }
    $('#lia_summary').html(summaryHtml);
  });
}

// Agent reports
function loadAgentReports(){
  getJSON('api/agent_reports.php', function(resp){
    if (!resp || resp.status!=='success'){ 
      $('#main_content').html('<div class="alert alert-warning">No reports</div>'); 
      return; 
    }
    const d = resp.data || {}; 

    let html = `<div class="card p-3">
                  <h5>My Reports</h5>
                  <div class="row">
                    <div class="col-lg-8">
                      <canvas id="agent_trend" height="140"></canvas>
                    </div>
                    <div class="col-lg-4" id="agent_summary" class="p-2">
                    </div>
                  </div>
                </div>`;
    $('#main_content').html(html);

    // Chart
    try { 
      const ctx = document.getElementById('agent_trend').getContext('2d'); 
      if (window.agentTrend) window.agentTrend.destroy(); 
      window.agentTrend = new Chart(ctx,{
        type:'bar', 
        data:{
          labels: d.trends.labels, 
          datasets:[{label:'Policies', data:d.trends.policies}] 
        }, 
        options:{responsive:true} 
      }); 
    } catch(e){ console.warn(e); }

    // Card summary
    let summaryHtml = '';
    const summary = d.summary || {};
    for (const key in summary){
      summaryHtml += `<div class="card mb-2">
                        <div class="card-body p-2">
                          <h6 class="card-title mb-1">${key}</h6>
                          <p class="card-text mb-0">${summary[key]}</p>
                        </div>
                      </div>`;
    }
    $('#agent_summary').html(summaryHtml);
  });
}


  // org tree
  function loadOrgAdmin(){ getJSON('api/org_tree.php', function(r){ if (!r || r.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">No organization data</div>'); return; } $('#main_content').html('<div class="card p-3"><h5>Organization Structure</h5><div id="org_tree_container" style="max-height:600px;overflow:auto"></div></div>'); $('#org_tree_container').html(renderOrgTreeHtml(r.data)); }); }

  function renderOrgTreeHtml(tree){ let out=''; tree.forEach(branch=>{ out += `<div class="org-branch mb-3"><div class="fw-bold">Branch: ${branch.branch_name} (${branch.branch_code})</div>`; (branch.managers||[]).forEach(m=>{ out += `<div class="ps-3 mt-2"><div class="fw-semibold">Manager: ${m.name} (${m.agent_code})</div>`; (m.supervisors||[]).forEach(s=>{ out += `<div class="ps-4 mt-2"><div class="text-muted">Supervisor: ${s.name} (${s.agent_code})</div>`; (s.team_leaders||[]).forEach(tl=>{ out += `<div class="ps-5 mt-1"><div>TL: ${tl.name} (${tl.agent_code})</div></div>`; }); out += `</div>`; }); out += `</div>`; }); out += `</div>`; }); return out; }

  function loadRecruitsPanel(){ getJSON('api/recruits_list.php', function(resp){ if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">No recruits</div>'); return; } let html = `<div class="card p-3"><h5>Auto-detected Recruits</h5><table class="table table-dark table-sm"><thead><tr><th>Code</th><th>Branch</th><th>Role</th><th>Entered By</th><th>Time</th><th>Action</th></tr></thead><tbody>`; resp.data.forEach(r => { html += `<tr><td>${r.entered_code}</td><td>${r.branch_code||''}</td><td>${r.role_suggested||''}</td><td>${r.entered_by||''}</td><td>${r.created_at}</td><td><button class="btn btn-sm btn-outline-light btn-edit-recruit" data-code="${r.entered_code}">Convert</button></td></tr>`; }); html += `</tbody></table></div>`; $('#main_content').html(html); $('.btn-edit-recruit').on('click', function(){ const code = $(this).data('code'); $('#recruit_code').val(code); $('#recruit_name').val(''); $('#recruit_role').val('agent'); $('#modalRecruit').modal('show'); }); }); }

  function loadReports(){ getJSON('api/get_reports.php', function(resp){ if (!resp || resp.status!=='success'){ $('#main_content').html('<div class="alert alert-warning">No reports</div>'); return; } let html = `<div class="card p-3"><h5>Reports</h5><div class="row"><div class="col-lg-8"><canvas id="rep_trend" height="160"></canvas></div><div class="col-lg-4"><div id="rep_side" class="p-2"></div></div></div></div>`; $('#main_content').html(html); try { const ctx = document.getElementById('rep_trend').getContext('2d'); if (window.repTrend) window.repTrend.destroy(); window.repTrend = new Chart(ctx,{ type:'line', data:{ labels: resp.data.trends.labels, datasets:[{label:'Policies',data:resp.data.trends.policies,fill:true}] }, options:{responsive:true} }); } catch(e){ console.warn(e); } $('#rep_side').html('<pre style="color:#cfeef2">'+JSON.stringify(resp.data.summary||{}, null, 2)+'</pre>'); }); }

  // initial
  loadDropdowns();
});

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.html'); exit; }
$userName  = htmlspecialchars($_SESSION['user_name'] ?? 'User');
$userRole  = $_SESSION['user_role'] ?? 'tenant';
$isAdmin   = $userRole === 'admin';
$myTenantId = $_SESSION['tenant_id'] ?? null;
$initials  = strtoupper(substr($userName,0,1)).strtoupper(substr(strrchr($userName,' '),1,1));
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Rental Monitoring System</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --t50:#E1F5EE;--t100:#9FE1CB;--t200:#5DCAA5;--t400:#1D9E75;--t600:#0F6E56;--t800:#085041;
  --w:#fff;--g50:#F7FAF9;--g100:#EEF2F0;--g200:#DDE4E0;--g300:#C2CEC9;
  --g400:#96A9A2;--g500:#6B807A;--g700:#38504A;--g900:#162220;
  --am:#D97706;--ambg:#FEF3C7;--rd:#DC2626;--rdbg:#FEE2E2;
  --gr:#059669;--grbg:#D1FAE5;--bl:#2563EB;--blbg:#DBEAFE;--pu:#7C3AED;--pubg:#EDE9FE;
  --sw:240px;--th:64px;--r:10px;--rs:7px;
}
body{font-family:'DM Sans',sans-serif;background:var(--g50);color:var(--g900);display:flex;min-height:100vh;font-size:14px}
/* SIDEBAR */
.sb{width:var(--sw);flex-shrink:0;background:var(--t800);display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:50;overflow-y:auto}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-logo .mk{display:flex;align-items:center;gap:10px}
.li{width:34px;height:34px;border-radius:7px;background:var(--t400);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.li svg{width:17px;height:17px;fill:white}
.sb-logo h1{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;color:white;line-height:1.2}
.sb-logo p{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.3px}
.nv{padding:14px 10px 6px}
.nl{font-size:10px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:rgba(255,255,255,.28);padding:0 8px;margin-bottom:5px;margin-top:10px}
.ni{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:6px;color:rgba(255,255,255,.58);font-size:13px;font-weight:500;cursor:pointer;transition:all .18s;margin-bottom:2px;border:none;background:none;width:100%;text-align:left}
.ni svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.ni:hover{background:rgba(255,255,255,.08);color:white}.ni.active{background:rgba(255,255,255,.14);color:white}
.ni .bdg{margin-left:auto;background:var(--t400);color:white;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px}
.sb-ft{padding:12px 10px;border-top:1px solid rgba(255,255,255,.08);margin-top:auto}
.uc{display:flex;align-items:center;gap:9px;padding:7px 10px}
.av{width:30px;height:30px;border-radius:50%;background:var(--t200);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--t800);flex-shrink:0}
.uc-inf p{font-size:12.5px;font-weight:500;color:white;line-height:1.2}
.uc-inf span{font-size:10.5px;color:rgba(255,255,255,.38)}
.vo-tag{display:inline-flex;align-items:center;gap:5px;margin:6px 10px 0;padding:5px 10px;border-radius:6px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.5);font-size:11px}
.vo-tag svg{width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round}
.btn-lo{width:100%;margin-top:7px;padding:7px;border-radius:6px;background:rgba(255,255,255,.07);border:none;color:rgba(255,255,255,.6);font-family:'DM Sans',sans-serif;font-size:12px;font-weight:500;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .17s}
.btn-lo:hover{background:rgba(255,255,255,.13);color:white}
.btn-lo svg{width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round}
/* MAIN */
.main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}
.tb{height:var(--th);background:var(--w);border-bottom:1px solid var(--g200);display:flex;align-items:center;justify-content:space-between;padding:0 26px;position:sticky;top:0;z-index:40}
.pt{font-family:'Syne',sans-serif;font-size:17px;font-weight:700;color:var(--g900)}
.ps{font-size:12px;color:var(--g400)}
.tb-r{display:flex;align-items:center;gap:9px}
.sw{position:relative}
.sw input{padding:7px 13px 7px 34px;border:1.5px solid var(--g200);border-radius:var(--rs);background:var(--g50);font-family:'DM Sans',sans-serif;font-size:13px;color:var(--g900);outline:none;width:200px;transition:all .18s}
.sw input:focus{border-color:var(--t200);background:white;box-shadow:0 0 0 3px rgba(29,158,117,.10)}
.sw input::placeholder{color:var(--g300)}
.sw svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:13px;height:13px;stroke:var(--g300);fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;pointer-events:none}
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 15px;border-radius:var(--rs);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;border:none;transition:all .17s;white-space:nowrap}
.btn-p{background:var(--t400);color:white;box-shadow:0 2px 8px rgba(29,158,117,.22)}.btn-p:hover{background:var(--t600);transform:translateY(-1px)}
.btn-g{background:transparent;color:var(--g700);border:1.5px solid var(--g200)}.btn-g:hover{border-color:var(--g300);background:var(--g50)}
.btn-d{background:var(--rdbg);color:var(--rd);border:1.5px solid #FECACA}.btn-d:hover{background:#FECACA}
.btn svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.vob{display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:var(--rs);background:var(--t50);color:var(--t600);font-size:12.5px;font-weight:500}
.vob svg{width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round}
/* PANELS */
.content{padding:24px;flex:1}
.panel{display:none}.panel.active{display:block}
/* STATS */
.sg{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.sc{background:var(--w);border-radius:var(--r);border:1px solid var(--g200);padding:18px;display:flex;align-items:flex-start;gap:12px;transition:box-shadow .17s}
.sc:hover{box-shadow:0 4px 18px rgba(0,0,0,.06)}
.si{width:40px;height:40px;border-radius:var(--rs);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.si svg{width:19px;height:19px;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round;fill:none}
.si.t{background:var(--t50)}.si.t svg{stroke:var(--t600)}
.si.g{background:var(--grbg)}.si.g svg{stroke:var(--gr)}
.si.a{background:var(--ambg)}.si.a svg{stroke:var(--am)}
.si.r{background:var(--rdbg)}.si.r svg{stroke:var(--rd)}
.si.b{background:var(--blbg)}.si.b svg{stroke:var(--bl)}
.si.p{background:var(--pubg)}.si.p svg{stroke:var(--pu)}
.sn p{font-size:11.5px;color:var(--g400);font-weight:500;margin-bottom:3px}
.sn h3{font-family:'Syne',sans-serif;font-size:21px;font-weight:700;color:var(--g900);line-height:1}
.sn small{font-size:11px;color:var(--g400);margin-top:2px;display:block}
/* TABLE */
.sec{background:var(--w);border-radius:var(--r);border:1px solid var(--g200);overflow:hidden}
.sh{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--g100);flex-wrap:wrap;gap:10px}
.stl{font-family:'Syne',sans-serif;font-size:14.5px;font-weight:700;color:var(--g900)}
.stb{font-size:12px;color:var(--g400);margin-top:1px}
.fr{display:flex;gap:7px;align-items:center;flex-wrap:wrap}
.sf{padding:6px 11px;border:1.5px solid var(--g200);border-radius:var(--rs);font-family:'DM Sans',sans-serif;font-size:12.5px;color:var(--g700);background:var(--g50);outline:none;cursor:pointer}
.sf:focus{border-color:var(--t200)}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid var(--g100);background:var(--g50)}
thead th{padding:10px 18px;text-align:left;font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:var(--g400);white-space:nowrap}
tbody tr{border-bottom:1px solid var(--g100);transition:background .13s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:var(--g50)}
tbody td{padding:11px 18px;font-size:13px;color:var(--g700)}
.tn{font-weight:500;color:var(--g900)}.ts{font-size:12px;color:var(--g400)}
/* PENDING ROW HIGHLIGHT */
.row-pending{background:rgba(220,38,38,.03)!important}
.row-pending:hover{background:rgba(220,38,38,.06)!important}
.row-partial{background:rgba(217,119,6,.03)!important}
.row-partial:hover{background:rgba(217,119,6,.06)!important}
/* BADGES */
.badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:11.5px;font-weight:500}
.badge::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0}
.badge-active{background:var(--grbg);color:var(--gr)}.badge-active::before{background:var(--gr)}
.badge-vacant{background:var(--ambg);color:var(--am)}.badge-vacant::before{background:var(--am)}
.badge-overdue{background:var(--rdbg);color:var(--rd)}.badge-overdue::before{background:var(--rd)}
.badge-maintenance{background:var(--blbg);color:var(--bl)}.badge-maintenance::before{background:var(--bl)}
.badge-paid{background:var(--grbg);color:var(--gr)}.badge-paid::before{background:var(--gr)}
.badge-partial{background:var(--ambg);color:var(--am)}.badge-partial::before{background:var(--am)}
.badge-pending{background:var(--rdbg);color:var(--rd)}.badge-pending::before{background:var(--rd)}
/* ACTION BUTTONS */
.ab{display:flex;gap:5px}
.ib{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--g200);background:white;cursor:pointer;transition:all .14s}
.ib svg{width:12px;height:12px;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;fill:none}
.ib.vi svg{stroke:var(--t400)}.ib.vi:hover{background:var(--t50);border-color:var(--t100)}
.ib.ed svg{stroke:var(--am)}.ib.ed:hover{background:var(--ambg);border-color:#FDE68A}
.ib.dl svg{stroke:var(--rd)}.ib.dl:hover{background:var(--rdbg);border-color:#FECACA}
/* PAGINATION */
.pgn{display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid var(--g100)}
.pgn span{font-size:12.5px;color:var(--g400)}
.pbs{display:flex;gap:3px}
.pb{width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--g200);background:white;font-size:12px;font-weight:500;color:var(--g500);cursor:pointer;transition:all .14s}
.pb.active{background:var(--t400);border-color:var(--t400);color:white}
.pb:hover:not(.active){background:var(--g50)}
/* TENANT MY-UNIT CARD */
.my-unit-card{background:var(--w);border-radius:var(--r);border:1px solid var(--g200);padding:22px 24px;margin-bottom:20px;display:flex;align-items:flex-start;gap:20px}
.muc-icon{width:52px;height:52px;border-radius:var(--r);background:var(--t50);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.muc-icon svg{width:24px;height:24px;stroke:var(--t600);fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}
.muc-info h2{font-family:'Syne',sans-serif;font-size:18px;font-weight:700;color:var(--g900);margin-bottom:4px}
.muc-info p{font-size:13px;color:var(--g500);line-height:1.6}
.muc-meta{margin-top:12px;display:flex;gap:18px;flex-wrap:wrap}
.muc-meta span{font-size:12.5px;color:var(--g500)}
.muc-meta strong{color:var(--g900);font-weight:600}
/* MODAL */
.ov{position:fixed;inset:0;background:rgba(22,34,32,.45);display:flex;align-items:center;justify-content:center;z-index:200;opacity:0;pointer-events:none;transition:opacity .22s;backdrop-filter:blur(2px)}
.ov.open{opacity:1;pointer-events:all}
.mo{background:white;border-radius:16px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.17);transform:translateY(14px) scale(.98);transition:transform .22s;overflow:hidden;max-height:90vh;overflow-y:auto}
.ov.open .mo{transform:translateY(0) scale(1)}
.mo-hd{display:flex;align-items:center;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid var(--g100);position:sticky;top:0;background:white;z-index:2}
.mo-t{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;color:var(--g900)}
.mo-s{font-size:12px;color:var(--g400);margin-top:2px}
.mc{width:30px;height:30px;border-radius:6px;border:1.5px solid var(--g200);background:white;cursor:pointer;display:flex;align-items:center;justify-content:center}
.mc:hover{background:var(--g50)}
.mc svg{width:13px;height:13px;stroke:var(--g500);fill:none;stroke-width:2;stroke-linecap:round}
.mo-bd{padding:20px 24px}
.fg{display:grid;grid-template-columns:1fr 1fr;gap:13px}
.mf label{display:block;font-size:12px;font-weight:500;color:var(--g700);margin-bottom:5px}
.mf input,.mf select,.mf textarea{width:100%;padding:9px 12px;border:1.5px solid var(--g200);border-radius:var(--rs);font-family:'DM Sans',sans-serif;font-size:13px;color:var(--g900);background:var(--g50);outline:none;transition:all .17s}
.mf input:focus,.mf select:focus,.mf textarea:focus{border-color:var(--t200);background:white;box-shadow:0 0 0 3px rgba(29,158,117,.10)}
.mf input::placeholder{color:var(--g300)}
.mf textarea{resize:vertical;min-height:68px}
.mo-ft{padding:14px 24px 20px;display:flex;gap:9px;justify-content:flex-end;border-top:1px solid var(--g100)}
/* VIEW GRID */
.vg{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.vi label{font-size:11px;font-weight:600;letter-spacing:.3px;text-transform:uppercase;color:var(--g400);margin-bottom:3px;display:block}
.vi p{font-size:13.5px;color:var(--g900);font-weight:500}
.vi.full{grid-column:1/-1}
/* DELETE */
.del-mo{max-width:380px}
.del-ic{width:50px;height:50px;border-radius:50%;background:var(--rdbg);display:flex;align-items:center;justify-content:center;margin:0 auto 14px}
.del-ic svg{width:22px;height:22px;stroke:var(--rd);fill:none;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}
.del-mo .mo-bd{text-align:center;padding:26px 24px 18px}
.del-mo h3{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;color:var(--g900);margin-bottom:7px}
.del-mo p{font-size:13px;color:var(--g500);line-height:1.6}
/* REPORTS */
.rg{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.rc{background:var(--w);border-radius:var(--r);border:1px solid var(--g200);padding:20px}
.rl{font-size:11.5px;color:var(--g400);font-weight:500;margin-bottom:4px}
.rv{font-family:'Syne',sans-serif;font-size:26px;font-weight:700;color:var(--g900)}
.rsb{font-size:11.5px;color:var(--g400);margin-top:3px}
.bar-row{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.bar-lbl{width:44px;font-size:11px;color:var(--g500);text-align:right;flex-shrink:0}
.bar-trk{flex:1;height:10px;background:var(--g100);border-radius:99px;overflow:hidden}
.bar-fill{height:100%;background:var(--t400);border-radius:99px;transition:width .5s ease}
.bar-fill.pnd{background:var(--rd)}
.bar-amt{font-size:11px;color:var(--g500);width:72px;flex-shrink:0}
/* MONTH GROUP HEADER */
.month-group-hd{background:var(--g50);padding:8px 18px;font-size:11px;font-weight:600;letter-spacing:.5px;text-transform:uppercase;color:var(--g400);border-top:1px solid var(--g100)}
/* EMPTY & LOADING */
.empty{padding:48px 20px;text-align:center}
.empty svg{width:40px;height:40px;stroke:var(--g300);fill:none;stroke-width:1.4;stroke-linecap:round;stroke-linejoin:round;margin:0 auto 12px;display:block}
.empty p{font-size:13.5px;color:var(--g400)}
.loading{text-align:center;padding:36px;color:var(--g400);font-size:13px}
/* TOAST */
.toast{position:fixed;bottom:24px;right:24px;background:var(--t800);color:white;padding:11px 18px;border-radius:var(--r);font-size:13px;font-weight:500;opacity:0;transform:translateY(8px);transition:all .25s;pointer-events:none;z-index:999;display:flex;align-items:center;gap:8px;box-shadow:0 6px 24px rgba(4,52,44,.22)}
.toast.show{opacity:1;transform:translateY(0)}
.toast svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;flex-shrink:0}
.toast.err{background:#DC2626}
/* NOTICE BOX */
.notice{background:var(--t50);border:1px solid var(--t100);border-radius:var(--r);padding:14px 18px;margin-bottom:20px;font-size:13px;color:var(--t800);display:flex;align-items:center;gap:10px}
.notice svg{width:16px;height:16px;stroke:var(--t600);fill:none;stroke-width:2;stroke-linecap:round;flex-shrink:0}
@media(max-width:900px){.sg{grid-template-columns:1fr 1fr}.rg{grid-template-columns:1fr 1fr}}
@media(max-width:600px){.sg{grid-template-columns:1fr}.fg{grid-template-columns:1fr}.rg{grid-template-columns:1fr}}
</style>
</head>
<body>

<aside class="sb">
  <div class="sb-logo">
    <div class="mk">
      <div class="li"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/><path d="M9 21V12h6v9" fill="rgba(255,255,255,0.25)"/></svg></div>
      <div><h1>RentalMon</h1><p>Monitoring System</p></div>
    </div>
  </div>
  <div class="nv">
    <?php if($isAdmin): ?>
    <div class="nl">Main</div>
    <button class="ni active" data-panel="rentals"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/></svg>Rental Units<span class="bdg" id="nav-r-cnt">…</span></button>
    <button class="ni" data-panel="tenants"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>Tenants<span class="bdg" id="nav-t-cnt">…</span></button>
    <div class="nl">Finance</div>
    <button class="ni" data-panel="payments"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>Payments</button>
    <button class="ni" data-panel="reports"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Reports</button>
    <?php else: ?>
    <div class="nl">My Account</div>
    <button class="ni active" data-panel="my-unit"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/></svg>My Unit</button>
    <button class="ni" data-panel="my-payments"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>My Payments</button>
    <button class="ni" data-panel="my-profile"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>My Profile</button>
    <?php endif; ?>
  </div>
  <div class="sb-ft">
    <div class="uc">
      <div class="av"><?= $initials ?></div>
      <div class="uc-inf"><p><?= $userName ?></p><span><?= $isAdmin ? 'Administrator' : 'Tenant' ?></span></div>
    </div>
    <?php if(!$isAdmin): ?><div class="vo-tag"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>View Only Access</div><?php endif; ?>
    <button class="btn-lo" onclick="logout()"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Sign Out</button>
  </div>
</aside>

<div class="main">
  <div class="tb">
    <div><div class="pt" id="page-title"><?= $isAdmin ? 'Rental Units' : 'My Unit' ?></div><div class="ps" id="page-sub">Rental Monitoring System</div></div>
    <div class="tb-r">
      <?php if($isAdmin): ?>
      <div class="sw"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg><input type="text" id="g-search" placeholder="Search…" oninput="handleSearch(this.value)"></div>
      <button class="btn btn-p" id="add-btn" onclick="openAdd()"><svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span id="add-label">Add Rental</span></button>
      <?php else: ?>
      <div class="vob"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>View Only</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="content">

  <?php if($isAdmin): ?>
  <!-- ═══════════ ADMIN PANELS ═══════════════════════════════ -->

  <!-- RENTALS -->
  <div class="panel active" id="panel-rentals">
    <div class="sg">
      <div class="sc"><div class="si t"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/></svg></div><div class="sn"><p>Total Units</p><h3 id="r-total">—</h3><small>All properties</small></div></div>
      <div class="sc"><div class="si g"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="sn"><p>Active</p><h3 id="r-active">—</h3><small>Occupied</small></div></div>
      <div class="sc"><div class="si a"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><div class="sn"><p>Vacant</p><h3 id="r-vacant">—</h3><small>Available</small></div></div>
      <div class="sc"><div class="si r"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div class="sn"><p>Overdue</p><h3 id="r-overdue">—</h3><small>Payment due</small></div></div>
    </div>
    <div class="sec">
      <div class="sh"><div><div class="stl">Rental Units</div><div class="stb">All properties</div></div>
        <div class="fr">
          <select class="sf" id="r-sf" onchange="loadRentals()"><option value="">All Status</option><option>Active</option><option>Vacant</option><option>Overdue</option><option>Maintenance</option></select>
          <select class="sf" id="r-tf" onchange="loadRentals()"><option value="">All Types</option><option>Apartment</option><option>House</option><option>Condo</option><option>Studio</option><option>Commercial</option></select>
        </div>
      </div>
      <table><thead><tr><th>Unit / Property</th><th>Tenant</th><th>Type</th><th>Monthly Rent</th><th>Due</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="r-tbody"><tr><td colspan="7" class="loading">Loading…</td></tr></tbody></table>
      <div class="empty" id="r-empty" style="display:none"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/></svg><p>No rental units found.</p></div>
      <div class="pgn"><span id="r-info">—</span><div class="pbs" id="r-pages"></div></div>
    </div>
  </div>

  <!-- TENANTS -->
  <div class="panel" id="panel-tenants">
    <div class="sg" style="grid-template-columns:repeat(3,1fr)">
      <div class="sc"><div class="si t"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div class="sn"><p>Total Tenants</p><h3 id="t-total">—</h3><small>Registered</small></div></div>
      <div class="sc"><div class="si g"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="sn"><p>With Unit</p><h3 id="t-active">—</h3><small>Assigned</small></div></div>
      <div class="sc"><div class="si a"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><div class="sn"><p>No Unit</p><h3 id="t-nounit">—</h3><small>Unassigned</small></div></div>
    </div>
    <div class="sec">
      <div class="sh"><div><div class="stl">Tenants</div><div class="stb">All registered tenants</div></div></div>
      <table><thead><tr><th>Name</th><th>Contact</th><th>Email</th><th>Assigned Unit</th><th>ID Type</th><th>Actions</th></tr></thead>
      <tbody id="t-tbody"><tr><td colspan="6" class="loading">Loading…</td></tr></tbody></table>
      <div class="empty" id="t-empty" style="display:none"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg><p>No tenants found.</p></div>
      <div class="pgn"><span id="t-info">—</span><div class="pbs" id="t-pages"></div></div>
    </div>
  </div>

  <!-- PAYMENTS (ADMIN) -->
  <div class="panel" id="panel-payments">
    <div class="sg">
      <div class="sc"><div class="si g"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div><div class="sn"><p>Total Collected</p><h3 id="p-total">—</h3><small>All time</small></div></div>
      <div class="sc"><div class="si t"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div><div class="sn"><p>Paid Records</p><h3 id="p-paid">—</h3><small>Completed</small></div></div>
      <div class="sc"><div class="si r"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div class="sn"><p>Pending</p><h3 id="p-pending">—</h3><small>Unpaid months</small></div></div>
      <div class="sc"><div class="si a"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><div class="sn"><p>Partial Amt</p><h3 id="p-partial">—</h3><small>Incomplete</small></div></div>
    </div>
    <div class="sec">
      <div class="sh"><div><div class="stl">Payment Records</div><div class="stb">Sorted: Pending → Partial → Paid by month</div></div>
        <div class="fr">
          <select class="sf" id="p-sf" onchange="loadPayments()"><option value="">All Status</option><option>Paid</option><option>Partial</option><option>Pending</option></select>
          <select class="sf" id="p-mf" onchange="loadPayments()"><option value="">All Methods</option><option>Cash</option><option>GCash</option><option>Bank Transfer</option><option>Check</option></select>
          <select class="sf" id="p-yf" onchange="loadPayments()"><option value="">All Years</option><?php for($y=2023;$y<=2026;$y++) echo "<option>$y</option>"; ?></select>
        </div>
      </div>
      <table><thead><tr><th>Unit</th><th>Tenant</th><th>Amount</th><th>Month For</th><th>Paid Date</th><th>Method</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody id="p-tbody"><tr><td colspan="8" class="loading">Loading…</td></tr></tbody></table>
      <div class="empty" id="p-empty" style="display:none"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg><p>No payments found.</p></div>
      <div class="pgn"><span id="p-info">—</span><div class="pbs" id="p-pages"></div></div>
    </div>
  </div>

  <!-- REPORTS -->
  <div class="panel" id="panel-reports">
    <div class="rg">
      <div class="rc"><div class="rl">Total Revenue (All Time)</div><div class="rv" id="rpt-rev">—</div><div class="rsb">From all paid payments</div></div>
      <div class="rc"><div class="rl">Monthly Potential</div><div class="rv" id="rpt-pot">—</div><div class="rsb">From all active units</div></div>
      <div class="rc"><div class="rl">Overdue Units</div><div class="rv" id="rpt-ov" style="color:var(--rd)">—</div><div class="rsb">Require follow-up</div></div>
    </div>
    <div class="sec" style="margin-bottom:20px">
      <div class="sh"><div><div class="stl">Monthly Collection</div><div class="stb" id="rpt-yr-lbl">2025</div></div>
        <div class="fr"><select class="sf" id="rpt-yr" onchange="loadReports()"><?php for($y=2023;$y<=2026;$y++) echo "<option".($y==2025?' selected':'').">$y</option>"; ?></select></div>
      </div>
      <div style="padding:16px 20px" id="monthly-chart"><div class="loading">Loading…</div></div>
    </div>
    <div class="sec" style="margin-bottom:20px">
      <div class="sh"><div><div class="stl">Overdue Accounts</div><div class="stb">Units requiring follow-up</div></div></div>
      <table><thead><tr><th>Unit</th><th>Tenant</th><th>Contact</th><th>Monthly Rent</th><th>Pending Months</th><th>Est. Amount Due</th></tr></thead>
      <tbody id="rpt-ov-tb"><tr><td colspan="6" class="loading">Loading…</td></tr></tbody></table>
      <div class="empty" id="rpt-ov-empty" style="display:none"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><p>No overdue accounts!</p></div>
    </div>
    <div class="sec">
      <div class="sh"><div><div class="stl">Collection Per Unit</div><div class="stb">Payment summary by property</div></div></div>
      <table><thead><tr><th>Unit</th><th>Tenant</th><th>Monthly Rent</th><th>Total Collected</th><th>Pending Months</th><th>Status</th></tr></thead>
      <tbody id="rpt-col-tb"><tr><td colspan="6" class="loading">Loading…</td></tr></tbody></table>
    </div>
  </div>

  <?php else: ?>
  <!-- ═══════════ TENANT PANELS (own data only) ══════════════ -->

  <!-- MY UNIT -->
  <div class="panel active" id="panel-my-unit">
    <div class="notice"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      You can only view your own rental information. Contact your admin for any changes.
    </div>
    <div id="my-unit-content"><div class="loading">Loading your unit…</div></div>
  </div>

  <!-- MY PAYMENTS -->
  <div class="panel" id="panel-my-payments">
    <div class="sg" style="grid-template-columns:repeat(3,1fr)">
      <div class="sc"><div class="si g"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div><div class="sn"><p>Total Paid</p><h3 id="mp-total">—</h3><small>Your payments</small></div></div>
      <div class="sc"><div class="si r"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div class="sn"><p>Pending</p><h3 id="mp-pending">—</h3><small>Unpaid months</small></div></div>
      <div class="sc"><div class="si t"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div><div class="sn"><p>Paid Records</p><h3 id="mp-count">—</h3><small>Completed</small></div></div>
    </div>
    <div class="sec">
      <div class="sh"><div><div class="stl">My Payment History</div><div class="stb">Pending shown first, sorted by month</div></div>
        <div class="fr">
          <select class="sf" id="mp-sf" onchange="loadMyPayments()"><option value="">All Status</option><option>Paid</option><option>Partial</option><option>Pending</option></select>
        </div>
      </div>
      <table><thead><tr><th>Month</th><th>Amount</th><th>Paid Date</th><th>Method</th><th>Status</th><th>Note</th></tr></thead>
      <tbody id="mp-tbody"><tr><td colspan="6" class="loading">Loading…</td></tr></tbody></table>
      <div class="empty" id="mp-empty" style="display:none"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg><p>No payment records found.</p></div>
      <div class="pgn"><span id="mp-info">—</span><div class="pbs" id="mp-pages"></div></div>
    </div>
  </div>

  <!-- MY PROFILE -->
  <div class="panel" id="panel-my-profile">
    <div class="sec">
      <div class="sh"><div><div class="stl">My Profile</div><div class="stb">Your registered information</div></div></div>
      <div style="padding:24px" id="my-profile-content"><div class="loading">Loading…</div></div>
    </div>
  </div>

  <?php endif; ?>

  </div><!-- /content -->
</div><!-- /main -->

<?php if($isAdmin): ?>
<!-- ═══ RENTAL MODALS ════════════════════════════════════════ -->
<div class="ov" id="r-form-ov" onclick="closeOv(event,'r-form-ov')">
  <div class="mo">
    <div class="mo-hd"><div><div class="mo-t" id="r-form-t">Add Rental</div><div class="mo-s">Fill in details</div></div><button class="mc" onclick="closeM('r-form-ov')"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
    <div class="mo-bd"><div class="fg" style="gap:13px;display:grid">
      <div class="mf"><label>Unit Name *</label><input type="text" id="r-unit" placeholder="e.g. Unit 4B"></div>
      <div class="mf"><label>Property Type *</label><select id="r-type"><option value="">Select</option><option>Apartment</option><option>House</option><option>Condo</option><option>Studio</option><option>Commercial</option></select></div>
      <div class="mf" style="grid-column:1/-1"><label>Address *</label><input type="text" id="r-addr" placeholder="Full address"></div>
      <div class="mf" style="grid-column:1/-1"><label>Assign Tenant</label><select id="r-tid"><option value="">— No tenant —</option></select></div>
      <div class="mf"><label>Monthly Rent (₱) *</label><input type="number" id="r-rent" placeholder="0.00" min="0"></div>
      <div class="mf"><label>Due Day (1–31)</label><input type="number" id="r-due" placeholder="15" min="1" max="31"></div>
      <div class="mf"><label>Lease Start</label><input type="date" id="r-start"></div>
      <div class="mf"><label>Lease End</label><input type="date" id="r-end"></div>
      <div class="mf"><label>Status *</label><select id="r-status"><option>Active</option><option>Vacant</option><option>Overdue</option><option>Maintenance</option></select></div>
      <div class="mf" style="grid-column:1/-1"><label>Notes</label><textarea id="r-notes" placeholder="Optional…"></textarea></div>
    </div></div>
    <div class="mo-ft"><button class="btn btn-g" onclick="closeM('r-form-ov')">Cancel</button><button class="btn btn-p" onclick="saveRental()"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span id="r-save-l">Save</span></button></div>
  </div>
</div>
<div class="ov" id="r-view-ov" onclick="closeOv(event,'r-view-ov')">
  <div class="mo">
    <div class="mo-hd"><div><div class="mo-t">Rental Details</div><div class="mo-s">Full info</div></div><button class="mc" onclick="closeM('r-view-ov')"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
    <div class="mo-bd"><div class="vg">
      <div class="vi"><label>Unit</label><p id="rv-unit">—</p></div><div class="vi"><label>Type</label><p id="rv-type">—</p></div>
      <div class="vi full"><label>Address</label><p id="rv-addr">—</p></div>
      <div class="vi"><label>Tenant</label><p id="rv-tenant">—</p></div><div class="vi"><label>Contact</label><p id="rv-contact">—</p></div>
      <div class="vi"><label>Monthly Rent</label><p id="rv-rent">—</p></div><div class="vi"><label>Due Day</label><p id="rv-due">—</p></div>
      <div class="vi"><label>Lease Start</label><p id="rv-start">—</p></div><div class="vi"><label>Lease End</label><p id="rv-end">—</p></div>
      <div class="vi"><label>Status</label><p id="rv-status">—</p></div>
      <div class="vi full"><label>Notes</label><p id="rv-notes" style="color:var(--g500)">—</p></div>
    </div></div>
    <div class="mo-ft"><button class="btn btn-g" onclick="closeM('r-view-ov')">Close</button><button class="btn btn-p" id="rv-edit-btn">Edit</button></div>
  </div>
</div>

<!-- ═══ TENANT MODALS ════════════════════════════════════════ -->
<div class="ov" id="t-form-ov" onclick="closeOv(event,'t-form-ov')">
  <div class="mo">
    <div class="mo-hd"><div><div class="mo-t" id="t-form-t">Add Tenant</div><div class="mo-s">Fill in details</div></div><button class="mc" onclick="closeM('t-form-ov')"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
    <div class="mo-bd"><div class="fg" style="gap:13px;display:grid">
      <div class="mf"><label>First Name *</label><input type="text" id="t-fn" placeholder="Juan"></div>
      <div class="mf"><label>Last Name *</label><input type="text" id="t-ln" placeholder="dela Cruz"></div>
      <div class="mf"><label>Email</label><input type="email" id="t-em" placeholder="email@example.com"></div>
      <div class="mf"><label>Contact</label><input type="text" id="t-ct" placeholder="09XXXXXXXXX"></div>
      <div class="mf" style="grid-column:1/-1"><label>Address</label><input type="text" id="t-ad" placeholder="Full address"></div>
      <div class="mf"><label>ID Type</label><select id="t-it"><option value="">Select</option><option>PhilSys ID</option><option>Driver License</option><option>Passport</option><option>SSS</option><option>GSIS</option><option>Voter's ID</option><option>TIN</option></select></div>
      <div class="mf"><label>ID Number</label><input type="text" id="t-in" placeholder="ID number"></div>
      <div class="mf" style="grid-column:1/-1"><label>Notes</label><textarea id="t-nt" placeholder="Optional…"></textarea></div>
    </div></div>
    <div class="mo-ft"><button class="btn btn-g" onclick="closeM('t-form-ov')">Cancel</button><button class="btn btn-p" onclick="saveTenant()"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span id="t-save-l">Save</span></button></div>
  </div>
</div>
<div class="ov" id="t-view-ov" onclick="closeOv(event,'t-view-ov')">
  <div class="mo">
    <div class="mo-hd"><div><div class="mo-t">Tenant Details</div><div class="mo-s">Full info</div></div><button class="mc" onclick="closeM('t-view-ov')"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
    <div class="mo-bd"><div class="vg">
      <div class="vi"><label>First Name</label><p id="tv-fn">—</p></div><div class="vi"><label>Last Name</label><p id="tv-ln">—</p></div>
      <div class="vi"><label>Email</label><p id="tv-em">—</p></div><div class="vi"><label>Contact</label><p id="tv-ct">—</p></div>
      <div class="vi full"><label>Address</label><p id="tv-ad">—</p></div>
      <div class="vi"><label>ID Type</label><p id="tv-it">—</p></div><div class="vi"><label>ID Number</label><p id="tv-in">—</p></div>
      <div class="vi"><label>Assigned Unit</label><p id="tv-unit">—</p></div><div class="vi"><label>Unit Status</label><p id="tv-ust">—</p></div>
      <div class="vi full"><label>Notes</label><p id="tv-nt" style="color:var(--g500)">—</p></div>
    </div></div>
    <div id="tv-link-section" style="margin:0 24px 16px;padding:14px;background:var(--g50);border-radius:var(--rs);border:1.5px dashed var(--g200)">
      <p style="font-size:12px;font-weight:600;color:var(--g700);margin-bottom:8px">🔗 Portal Account Link</p>
      <div id="tv-link-status" style="font-size:12.5px;color:var(--g500);margin-bottom:8px">Checking…</div>
      <div style="display:flex;gap:7px;align-items:center;flex-wrap:wrap">
        <select id="tv-user-select" style="flex:1;padding:7px 10px;border:1.5px solid var(--g200);border-radius:var(--rs);font-family:'DM Sans',sans-serif;font-size:12.5px;color:var(--g700);background:white;outline:none">
          <option value="">— Select portal account to link —</option>
        </select>
        <button class="btn btn-p" onclick="linkTenantAccount()" style="white-space:nowrap;font-size:12.5px;padding:7px 12px">Link</button>
        <button class="btn btn-g" id="tv-unlink-btn" onclick="unlinkTenantAccount()" style="white-space:nowrap;font-size:12.5px;padding:7px 12px;display:none">Unlink</button>
      </div>
      <p style="font-size:11px;color:var(--g400);margin-top:7px">Link this tenant record to a portal account so they can log in and see their unit and payments.</p>
    </div>
    <div class="mo-ft"><button class="btn btn-g" onclick="closeM('t-view-ov')">Close</button><button class="btn btn-p" id="tv-edit-btn">Edit</button></div>
  </div>
</div>

<!-- ═══ PAYMENT MODALS ═══════════════════════════════════════ -->
<div class="ov" id="p-form-ov" onclick="closeOv(event,'p-form-ov')">
  <div class="mo">
    <div class="mo-hd"><div><div class="mo-t" id="p-form-t">Add Payment</div><div class="mo-s">Record a payment</div></div><button class="mc" onclick="closeM('p-form-ov')"><svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
    <div class="mo-bd"><div class="fg" style="gap:13px;display:grid">
      <div class="mf" style="grid-column:1/-1"><label>Rental Unit *</label><select id="p-rid"><option value="">Select unit</option></select></div>
      <div class="mf" style="grid-column:1/-1"><label>Tenant</label><select id="p-tid"><option value="">— Select tenant —</option></select></div>
      <div class="mf"><label>Amount (₱) *</label><input type="number" id="p-amt" placeholder="0.00" min="0"></div>
      <div class="mf"><label>Paid Date *</label><input type="date" id="p-date"></div>
      <div class="mf"><label>Month For</label><input type="month" id="p-month"></div>
      <div class="mf"><label>Method</label><select id="p-mth"><option>Cash</option><option>GCash</option><option>Bank Transfer</option><option>Check</option></select></div>
      <div class="mf"><label>Status</label><select id="p-sts"><option>Paid</option><option>Partial</option><option>Pending</option></select></div>
      <div class="mf" style="grid-column:1/-1"><label>Note</label><textarea id="p-note" placeholder="Optional…"></textarea></div>
    </div></div>
    <div class="mo-ft"><button class="btn btn-g" onclick="closeM('p-form-ov')">Cancel</button><button class="btn btn-p" onclick="savePayment()"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span id="p-save-l">Save</span></button></div>
  </div>
</div>

<!-- SHARED DELETE -->
<div class="ov" id="del-ov" onclick="closeOv(event,'del-ov')">
  <div class="mo del-mo">
    <div class="mo-bd"><div class="del-ic"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></div>
      <h3 id="del-title">Delete?</h3><p id="del-msg">This cannot be undone.</p></div>
    <div class="mo-ft" style="justify-content:center;gap:10px"><button class="btn btn-g" onclick="closeM('del-ov')">Cancel</button><button class="btn btn-d" onclick="confirmDel()">Delete</button></div>
  </div>
</div>
<?php endif; ?>

<div class="toast" id="toast"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span id="toast-msg"></span></div>

<script>
const IS_ADMIN   = <?= $isAdmin ? 'true' : 'false' ?>;
const MY_TID     = <?= $myTenantId ?? 'null' ?>;
const API = {r:'api/rentals.php',t:'api/tenants.php',p:'api/payments.php',rpt:'api/reports.php',auth:'api/auth.php'};
let rPage=1,tPage=1,pPage=1,mpPage=1,rEid=null,tEid=null,pEid=null,delCb=null,searchQ='',stmr=null;

// ── UTILS ────────────────────────────────────────────────────
function esc(s){return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function php(n){return '₱'+Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});}
function fmtD(d){if(!d)return'—';try{return new Date(d+'T00:00:00').toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});}catch{return d;}}
function fmtM(m){if(!m)return'—';const[y,mo]=m.split('-');return['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][parseInt(mo)-1]+' '+y;}
function badge(s){const m={Active:'active',Vacant:'vacant',Overdue:'overdue',Maintenance:'maintenance',Paid:'paid',Partial:'partial',Pending:'pending'};return`<span class="badge badge-${m[s]||'active'}">${esc(s)}</span>`;}
async function api(url,opts={}){const r=await fetch(url,{credentials:'same-origin',headers:{'Content-Type':'application/json'},...opts});if(r.status===401){window.location.href='index.html';return null;}return r.json();}
function toast(msg,t=''){const el=document.getElementById('toast');document.getElementById('toast-msg').textContent=msg;el.className='toast'+(t?' '+t:'')+' show';setTimeout(()=>el.classList.remove('show'),3200);}
function openM(id){document.getElementById(id).classList.add('open');}
function closeM(id){document.getElementById(id).classList.remove('open');}
function closeOv(e,id){if(e.target===document.getElementById(id))closeM(id);}
function pages(tot,cur,cb,el){const e=document.getElementById(el);e.innerHTML='';for(let i=1;i<=tot;i++){const b=document.createElement('button');b.className='pb'+(i===cur?' active':'');b.textContent=i;b.onclick=()=>cb(i);e.appendChild(b);}}
function handleSearch(v){clearTimeout(stmr);stmr=setTimeout(()=>{searchQ=v;rPage=1;tPage=1;pPage=1;const a=document.querySelector('.ni.active');if(a)a.click();},320);}

// ── NAV ──────────────────────────────────────────────────────
const panelTitles={rentals:'Rental Units',tenants:'Tenants',payments:'Payments',reports:'Reports','my-unit':'My Unit','my-payments':'My Payments','my-profile':'My Profile'};
const addLabels={rentals:'Add Rental',tenants:'Add Tenant',payments:'Add Payment',reports:null};

document.querySelectorAll('.ni[data-panel]').forEach(btn=>{
  btn.onclick=()=>{
    document.querySelectorAll('.ni').forEach(n=>n.classList.remove('active'));
    btn.classList.add('active');
    const p=btn.dataset.panel;
    document.querySelectorAll('.panel').forEach(el=>el.classList.remove('active'));
    const pan=document.getElementById('panel-'+p); if(pan) pan.classList.add('active');
    document.getElementById('page-title').textContent=panelTitles[p]||p;
    document.getElementById('page-sub').textContent='Rental Monitoring System › '+(panelTitles[p]||p);
    const gs=document.getElementById('g-search'); if(gs){gs.value='';searchQ='';}
    const ab=document.getElementById('add-btn');
    if(ab){const lbl=addLabels[p];if(lbl){ab.style.display='';document.getElementById('add-label').textContent=lbl;}else ab.style.display='none';}
    if(p==='rentals')loadRentals();
    else if(p==='tenants')loadTenants();
    else if(p==='payments')loadPayments();
    else if(p==='reports')loadReports();
    else if(p==='my-unit')loadMyUnit();
    else if(p==='my-payments')loadMyPayments();
    else if(p==='my-profile')loadMyProfile();
  };
});

function openAdd(){const p=document.querySelector('.ni.active')?.dataset.panel;if(p==='rentals')openAddRental();else if(p==='tenants')openAddTenant();else if(p==='payments')openAddPayment();}

// ══════ RENTALS (ADMIN) ══════════════════════════════════════
async function loadRentals(){
  const pr=new URLSearchParams({page:rPage,per_page:8});
  const sf=document.getElementById('r-sf')?.value; if(sf)pr.set('status',sf);
  const tf=document.getElementById('r-tf')?.value; if(tf)pr.set('type',tf);
  if(searchQ)pr.set('q',searchQ);
  document.getElementById('r-tbody').innerHTML='<tr><td colspan="7" class="loading">Loading…</td></tr>';
  const d=await api(`${API.r}?${pr}`); if(!d)return;
  if(d.stats){document.getElementById('r-total').textContent=d.stats.total??0;document.getElementById('r-active').textContent=d.stats.active??0;document.getElementById('r-vacant').textContent=d.stats.vacant??0;document.getElementById('r-overdue').textContent=d.stats.overdue??0;document.getElementById('nav-r-cnt').textContent=d.stats.total??0;}
  const rows=d.data||[];
  document.getElementById('r-empty').style.display=rows.length?'none':'block';
  document.getElementById('r-tbody').innerHTML=rows.map(r=>`<tr>
    <td><div class="tn">${esc(r.unit)}</div><div class="ts">${esc(r.address)}</div></td>
    <td>${esc(r.tenant_name||'—')}</td><td>${esc(r.type)}</td><td>${php(r.rent)}</td>
    <td>${r.due_day?'Day '+r.due_day:'—'}</td><td>${badge(r.status)}</td>
    <td><div class="ab">
      <button class="ib vi" onclick="viewRental(${r.id})" title="View"><svg viewBox="0 0 24 24" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
      <button class="ib ed" onclick="editRental(${r.id})" title="Edit"><svg viewBox="0 0 24 24" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
      <button class="ib dl" onclick="delRental(${r.id},'${esc(r.unit)}')" title="Delete"><svg viewBox="0 0 24 24" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></button>
    </div></td></tr>`).join('');
  document.getElementById('r-info').textContent=`Showing ${rows.length} of ${d.total}`;
  pages(d.pages,rPage,i=>{rPage=i;loadRentals();},'r-pages');
}
async function loadTDD(){const d=await api(`${API.t}?per_page=100&page=1`);if(!d)return;const s=document.getElementById('r-tid');const c=s.value;s.innerHTML='<option value="">— No tenant —</option>';(d.data||[]).forEach(t=>{const o=document.createElement('option');o.value=t.id;o.textContent=`${t.first_name} ${t.last_name}`;s.appendChild(o);});if(c)s.value=c;}
function openAddRental(){rEid=null;['r-unit','r-addr','r-rent','r-due','r-start','r-end','r-notes'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});document.getElementById('r-type').value='';document.getElementById('r-status').value='Active';document.getElementById('r-tid').value='';document.getElementById('r-form-t').textContent='Add Rental';document.getElementById('r-save-l').textContent='Save Rental';loadTDD();openM('r-form-ov');}
async function editRental(id){const d=await api(`${API.r}?id=${id}`);if(!d?.data)return;const r=d.data;rEid=id;document.getElementById('r-unit').value=r.unit||'';document.getElementById('r-type').value=r.type||'';document.getElementById('r-addr').value=r.address||'';document.getElementById('r-rent').value=r.rent||'';document.getElementById('r-due').value=r.due_day||'';document.getElementById('r-start').value=r.lease_start||'';document.getElementById('r-end').value=r.lease_end||'';document.getElementById('r-status').value=r.status||'Active';document.getElementById('r-notes').value=r.notes||'';document.getElementById('r-form-t').textContent='Edit Rental';document.getElementById('r-save-l').textContent='Update';await loadTDD();document.getElementById('r-tid').value=r.tenant_id||'';closeM('r-view-ov');openM('r-form-ov');}
async function saveRental(){const body={unit:document.getElementById('r-unit').value.trim(),type:document.getElementById('r-type').value,address:document.getElementById('r-addr').value.trim(),tenant_id:document.getElementById('r-tid').value||null,rent:document.getElementById('r-rent').value,due_day:document.getElementById('r-due').value||null,lease_start:document.getElementById('r-start').value||null,lease_end:document.getElementById('r-end').value||null,status:document.getElementById('r-status').value,notes:document.getElementById('r-notes').value.trim()};if(!body.unit||!body.type||!body.address||!body.rent){toast('Fill required fields.','err');return;}const url=rEid?`${API.r}?id=${rEid}`:API.r;const d=await api(url,{method:rEid?'PUT':'POST',body:JSON.stringify(body)});if(!d)return;if(d.success){closeM('r-form-ov');toast(d.message);loadRentals();}else toast(d.message,'err');}
async function viewRental(id){const d=await api(`${API.r}?id=${id}`);if(!d?.data)return;const r=d.data;document.getElementById('rv-unit').textContent=r.unit;document.getElementById('rv-type').textContent=r.type;document.getElementById('rv-addr').textContent=r.address;document.getElementById('rv-tenant').textContent=r.tenant_name||'—';document.getElementById('rv-contact').textContent=r.contact||'—';document.getElementById('rv-rent').textContent=php(r.rent);document.getElementById('rv-due').textContent=r.due_day?'Day '+r.due_day+' of month':'—';document.getElementById('rv-start').textContent=fmtD(r.lease_start);document.getElementById('rv-end').textContent=fmtD(r.lease_end);document.getElementById('rv-status').innerHTML=badge(r.status);document.getElementById('rv-notes').textContent=r.notes||'No notes.';document.getElementById('rv-edit-btn').onclick=()=>editRental(id);openM('r-view-ov');}
function delRental(id,nm){delCb=async()=>{const d=await api(`${API.r}?id=${id}`,{method:'DELETE'});if(d?.success){closeM('del-ov');toast(d.message);loadRentals();}else toast(d?.message||'Error','err');};document.getElementById('del-title').textContent='Delete Rental Unit?';document.getElementById('del-msg').innerHTML=`Delete <strong>${esc(nm)}</strong>? This cannot be undone.`;openM('del-ov');}

// ══════ TENANTS (ADMIN) ══════════════════════════════════════
async function loadTenants(){
  const pr=new URLSearchParams({page:tPage});if(searchQ)pr.set('q',searchQ);
  document.getElementById('t-tbody').innerHTML='<tr><td colspan="6" class="loading">Loading…</td></tr>';
  const d=await api(`${API.t}?${pr}`);if(!d)return;
  const rows=d.data||[];
  document.getElementById('t-total').textContent=d.total??0;document.getElementById('nav-t-cnt').textContent=d.total??0;
  api(`${API.t}?page=1&per_page=200`).then(all=>{const ar=all?.data||[];document.getElementById('t-active').textContent=ar.filter(r=>r.unit).length;document.getElementById('t-nounit').textContent=ar.filter(r=>!r.unit).length;});
  document.getElementById('t-empty').style.display=rows.length?'none':'block';
  document.getElementById('t-tbody').innerHTML=rows.map(t=>`<tr>
    <td><div class="tn">${esc(t.first_name)} ${esc(t.last_name)}</div></td>
    <td>${esc(t.contact||'—')}</td><td>${esc(t.email||'—')}</td>
    <td>${t.unit?`<span class="badge badge-active">${esc(t.unit)}</span>`:'<span style="color:var(--g400)">—</span>'}</td>
    <td>${esc(t.id_type||'—')}</td>
    <td><div class="ab">
      <button class="ib vi" onclick="viewTenant(${t.id})"><svg viewBox="0 0 24 24" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
      <button class="ib ed" onclick="editTenant(${t.id})"><svg viewBox="0 0 24 24" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
      <button class="ib dl" onclick="delTenant(${t.id},'${esc(t.first_name+' '+t.last_name)}')"><svg viewBox="0 0 24 24" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></button>
    </div></td></tr>`).join('');
  document.getElementById('t-info').textContent=`Showing ${rows.length} of ${d.total}`;
  pages(d.pages,tPage,i=>{tPage=i;loadTenants();},'t-pages');
}
function openAddTenant(){tEid=null;['t-fn','t-ln','t-em','t-ct','t-ad','t-in','t-nt'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});document.getElementById('t-it').value='';document.getElementById('t-form-t').textContent='Add Tenant';document.getElementById('t-save-l').textContent='Save Tenant';openM('t-form-ov');}
async function editTenant(id){const d=await api(`${API.t}?id=${id}`);if(!d?.data)return;const t=d.data;tEid=id;document.getElementById('t-fn').value=t.first_name||'';document.getElementById('t-ln').value=t.last_name||'';document.getElementById('t-em').value=t.email||'';document.getElementById('t-ct').value=t.contact||'';document.getElementById('t-ad').value=t.address||'';document.getElementById('t-it').value=t.id_type||'';document.getElementById('t-in').value=t.id_number||'';document.getElementById('t-nt').value=t.notes||'';document.getElementById('t-form-t').textContent='Edit Tenant';document.getElementById('t-save-l').textContent='Update';closeM('t-view-ov');openM('t-form-ov');}
async function saveTenant(){const body={first_name:document.getElementById('t-fn').value.trim(),last_name:document.getElementById('t-ln').value.trim(),email:document.getElementById('t-em').value.trim(),contact:document.getElementById('t-ct').value.trim(),address:document.getElementById('t-ad').value.trim(),id_type:document.getElementById('t-it').value,id_number:document.getElementById('t-in').value.trim(),notes:document.getElementById('t-nt').value.trim()};if(!body.first_name||!body.last_name){toast('Name required.','err');return;}const url=tEid?`${API.t}?id=${tEid}`:API.t;const d=await api(url,{method:tEid?'PUT':'POST',body:JSON.stringify(body)});if(!d)return;if(d.success){closeM('t-form-ov');toast(d.message);loadTenants();}else toast(d.message,'err');}
let tvCurrentTenantId=null, tvLinkedUserId=null;

async function viewTenant(id){
  const d=await api(`${API.t}?id=${id}`);if(!d?.data)return;
  const t=d.data;
  tvCurrentTenantId=id;
  tvLinkedUserId=null;
  document.getElementById('tv-fn').textContent=t.first_name||'—';
  document.getElementById('tv-ln').textContent=t.last_name||'—';
  document.getElementById('tv-em').textContent=t.email||'—';
  document.getElementById('tv-ct').textContent=t.contact||'—';
  document.getElementById('tv-ad').textContent=t.address||'—';
  document.getElementById('tv-it').textContent=t.id_type||'—';
  document.getElementById('tv-in').textContent=t.id_number||'—';
  document.getElementById('tv-unit').textContent=t.unit||'No unit assigned';
  document.getElementById('tv-ust').innerHTML=t.rental_status?badge(t.rental_status):'—';
  document.getElementById('tv-nt').textContent=t.notes||'No notes.';
  document.getElementById('tv-edit-btn').onclick=()=>editTenant(id);
  // Load link section
  await loadLinkSection(id, t.email);
  openM('t-view-ov');
}

async function loadLinkSection(tenantId, tenantEmail){
  const statusEl=document.getElementById('tv-link-status');
  const sel=document.getElementById('tv-user-select');
  const unlinkBtn=document.getElementById('tv-unlink-btn');
  statusEl.innerHTML='<span style="color:var(--g400)">Loading portal accounts…</span>';
  sel.innerHTML='<option value="">— Select portal account —</option>';
  unlinkBtn.style.display='none';
  tvLinkedUserId=null;

  const ud=await api(`${API.auth}?action=users`);
  if(!ud?.data){statusEl.textContent='Could not load users.';return;}

  const users=ud.data||[];
  // Find if any user is already linked to this tenant
  const linked=users.find(u=>u.tenant_id==tenantId);

  if(linked){
    tvLinkedUserId=linked.id;
    statusEl.innerHTML=`<span style="color:var(--gr)">✓ Linked to: <strong>${esc(linked.first_name+' '+linked.last_name)}</strong> (${esc(linked.email)})</span>`;
    unlinkBtn.style.display='';
    sel.style.display='none';
    document.querySelector('#tv-link-section .btn-p').style.display='none';
  } else {
    statusEl.innerHTML='<span style="color:var(--am)">⚠ No portal account linked yet.</span>';
    sel.style.display='';
    document.querySelector('#tv-link-section .btn-p').style.display='';
    // Populate dropdown with unlinked tenant accounts
    // Pre-select if email matches
    users.filter(u=>!u.tenant_id).forEach(u=>{
      const o=document.createElement('option');
      o.value=u.id;
      o.textContent=`${u.first_name} ${u.last_name} (${u.email})`;
      if(u.email===tenantEmail) o.selected=true;
      sel.appendChild(o);
    });
    // Also show already-linked ones but disabled
    users.filter(u=>u.tenant_id&&u.tenant_id!=tenantId).forEach(u=>{
      const o=document.createElement('option');
      o.value=u.id;
      o.textContent=`${u.first_name} ${u.last_name} — linked to ${u.linked_tenant_name||'another'}`;
      o.disabled=true;
      sel.appendChild(o);
    });
  }
}

async function linkTenantAccount(){
  const userId=document.getElementById('tv-user-select').value;
  if(!userId){toast('Please select a portal account.','err');return;}
  const d=await api(`${API.auth}?action=link_account`,{method:'POST',body:JSON.stringify({user_id:parseInt(userId),tenant_id:tvCurrentTenantId})});
  if(d?.success){toast('Account linked! Tenant can now see their unit.');await loadLinkSection(tvCurrentTenantId);}
  else toast(d?.message||'Error','err');
}

async function unlinkTenantAccount(){
  if(!tvLinkedUserId){return;}
  if(!confirm('Remove the portal account link for this tenant?'))return;
  const d=await api(`${API.auth}?action=unlink_account`,{method:'POST',body:JSON.stringify({user_id:tvLinkedUserId})});
  if(d?.success){toast('Account unlinked.');await loadLinkSection(tvCurrentTenantId);}
  else toast(d?.message||'Error','err');
}
function delTenant(id,nm){delCb=async()=>{const d=await api(`${API.t}?id=${id}`,{method:'DELETE'});if(d?.success){closeM('del-ov');toast(d.message);loadTenants();}else toast(d?.message||'Error','err');};document.getElementById('del-title').textContent='Delete Tenant?';document.getElementById('del-msg').innerHTML=`Delete <strong>${esc(nm)}</strong>? Their rental assignment will be cleared.`;openM('del-ov');}

// ══════ PAYMENTS (ADMIN) ═════════════════════════════════════
async function loadPayments(){
  const pr=new URLSearchParams({page:pPage});
  const sf=document.getElementById('p-sf')?.value; if(sf)pr.set('status',sf);
  const mf=document.getElementById('p-mf')?.value; if(mf)pr.set('method',mf);
  const yf=document.getElementById('p-yf')?.value; if(yf)pr.set('year',yf);
  if(searchQ)pr.set('q',searchQ);
  document.getElementById('p-tbody').innerHTML='<tr><td colspan="8" class="loading">Loading…</td></tr>';
  const d=await api(`${API.p}?${pr}`);if(!d)return;
  if(d.stats){document.getElementById('p-total').textContent=php(d.stats.total_collected);document.getElementById('p-paid').textContent=d.stats.paid_count??0;document.getElementById('p-pending').textContent=d.stats.pending_count??0;document.getElementById('p-partial').textContent=php(d.stats.total_partial);}
  const rows=d.data||[];
  document.getElementById('p-empty').style.display=rows.length?'none':'block';
  document.getElementById('p-tbody').innerHTML=rows.map(p=>`<tr class="${p.status==='Pending'?'row-pending':p.status==='Partial'?'row-partial':''}">
    <td class="tn">${esc(p.unit||'—')}</td><td>${esc(p.tenant_name||'—')}</td>
    <td><strong>${php(p.amount)}</strong></td><td>${fmtM(p.month_for)}</td>
    <td>${fmtD(p.paid_date)}</td><td>${esc(p.method||'—')}</td>
    <td>${badge(p.status)}</td>
    <td><div class="ab">
      <button class="ib ed" onclick="editPayment(${p.id})"><svg viewBox="0 0 24 24" stroke="currentColor"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
      <button class="ib dl" onclick="delPayment(${p.id})"><svg viewBox="0 0 24 24" stroke="currentColor"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg></button>
    </div></td></tr>`).join('');
  document.getElementById('p-info').textContent=`Showing ${rows.length} of ${d.total}`;
  pages(d.pages,pPage,i=>{pPage=i;loadPayments();},'p-pages');
}
async function loadRDD(){const d=await api(`${API.r}?per_page=100&page=1`);if(!d)return;const s=document.getElementById('p-rid');s.innerHTML='<option value="">Select unit</option>';(d.data||[]).forEach(r=>{const o=document.createElement('option');o.value=r.id;o.textContent=`${r.unit} — ${r.tenant_name||'Vacant'}`;if(r.tenant_id)o.dataset.tid=r.tenant_id;s.appendChild(o);});}
async function loadTDDP(){const d=await api(`${API.t}?per_page=100&page=1`);if(!d)return;const s=document.getElementById('p-tid');s.innerHTML='<option value="">— No tenant —</option>';(d.data||[]).forEach(t=>{const o=document.createElement('option');o.value=t.id;o.textContent=`${t.first_name} ${t.last_name}`;s.appendChild(o);});}
function openAddPayment(){pEid=null;['p-amt','p-date','p-month','p-note'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});document.getElementById('p-mth').value='Cash';document.getElementById('p-sts').value='Paid';document.getElementById('p-form-t').textContent='Add Payment';document.getElementById('p-save-l').textContent='Save Payment';loadRDD();loadTDDP();openM('p-form-ov');}
async function editPayment(id){const d=await api(`${API.p}?id=${id}`);if(!d?.data)return;const p=d.data;pEid=id;await loadRDD();await loadTDDP();document.getElementById('p-rid').value=p.rental_id||'';document.getElementById('p-tid').value=p.tenant_id||'';document.getElementById('p-amt').value=p.amount||'';document.getElementById('p-date').value=p.paid_date||'';document.getElementById('p-month').value=p.month_for||'';document.getElementById('p-mth').value=p.method||'Cash';document.getElementById('p-sts').value=p.status||'Paid';document.getElementById('p-note').value=p.note||'';document.getElementById('p-form-t').textContent='Edit Payment';document.getElementById('p-save-l').textContent='Update';openM('p-form-ov');}
async function savePayment(){const body={rental_id:document.getElementById('p-rid').value,tenant_id:document.getElementById('p-tid').value||null,amount:document.getElementById('p-amt').value,paid_date:document.getElementById('p-date').value,month_for:document.getElementById('p-month').value||null,method:document.getElementById('p-mth').value,status:document.getElementById('p-sts').value,note:document.getElementById('p-note').value.trim()};if(!body.rental_id||!body.paid_date){toast('Rental and date required.','err');return;}const url=pEid?`${API.p}?id=${pEid}`:API.p;const d=await api(url,{method:pEid?'PUT':'POST',body:JSON.stringify(body)});if(!d)return;if(d.success){closeM('p-form-ov');toast(d.message);loadPayments();}else toast(d.message,'err');}
function delPayment(id){delCb=async()=>{const d=await api(`${API.p}?id=${id}`,{method:'DELETE'});if(d?.success){closeM('del-ov');toast(d.message);loadPayments();}else toast(d?.message||'Error','err');};document.getElementById('del-title').textContent='Delete Payment?';document.getElementById('del-msg').textContent='This payment record will be removed permanently.';openM('del-ov');}

// ══════ REPORTS (ADMIN) ══════════════════════════════════════
async function loadReports(){
  const yr=document.getElementById('rpt-yr').value;
  document.getElementById('rpt-yr-lbl').textContent=yr;
  const[ov,mo,od,col]=await Promise.all([api(`${API.rpt}?type=overview`),api(`${API.rpt}?type=monthly&year=${yr}`),api(`${API.rpt}?type=overdue`),api(`${API.rpt}?type=collection`)]);
  if(ov?.rental_stats){document.getElementById('rpt-pot').textContent=php(ov.rental_stats.active_revenue);document.getElementById('rpt-ov').textContent=ov.rental_stats.overdue??0;}
  if(ov?.payment_stats)document.getElementById('rpt-rev').textContent=php(ov.payment_stats.total_collected);
  if(mo?.data){const mx=Math.max(...mo.data.map(m=>parseFloat(m.collected)||0),1);const mn=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];document.getElementById('monthly-chart').innerHTML=mo.data.map((m,i)=>`<div class="bar-row"><div class="bar-lbl">${mn[i]}</div><div style="flex:1"><div class="bar-trk"><div class="bar-fill${m.collected>0?'':' pnd'}" style="width:${m.collected>0?Math.round((m.collected/mx)*100):0}%"></div></div>${m.pending>0?`<div class="bar-trk" style="margin-top:3px;height:6px"><div class="bar-fill pnd" style="width:${Math.min(100,m.pending*10)}%"></div></div>`:''}</div><div class="bar-amt">${m.collected>0?php(m.collected):'—'}</div></div>`).join('');}
  if(od?.data){const rows=od.data;document.getElementById('rpt-ov-empty').style.display=rows.length?'none':'block';document.getElementById('rpt-ov-tb').innerHTML=rows.length?rows.map(r=>`<tr><td class="tn">${esc(r.unit)}</td><td>${esc(r.tenant_name||'—')}</td><td>${esc(r.contact||'—')}</td><td>${php(r.rent)}</td><td><span class="badge badge-overdue">${r.pending_months||0} month${r.pending_months!=1?'s':''}</span></td><td><strong style="color:var(--rd)">${php((r.pending_months||0)*r.rent)}</strong></td></tr>`).join(''):'<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--g400)">No overdue accounts.</td></tr>';}
  if(col?.data)document.getElementById('rpt-col-tb').innerHTML=col.data.map(r=>`<tr><td class="tn">${esc(r.unit)}</td><td>${esc(r.tenant_name||'—')}</td><td>${php(r.rent)}</td><td><strong style="color:var(--gr)">${php(r.total_paid)}</strong></td><td>${r.pending_months>0?`<span class="badge badge-overdue">${r.pending_months}</span>`:'<span style="color:var(--g400)">0</span>'}</td><td>${badge(r.status)}</td></tr>`).join('');
}

// ══════ TENANT PANELS ════════════════════════════════════════
// ══════ MY UNIT ═══════════════════════════════════════════
async function loadMyUnit(){
  document.getElementById('my-unit-content').innerHTML='<div class="loading">Loading…</div>';
  // Fetch full tenant data which includes own unit + neighbors + vacant units
  const d=await api(`${API.t}`);if(!d)return;
  if(!d.linked||!d.data){
    document.getElementById('my-unit-content').innerHTML='<div class="empty"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/></svg><p>No rental unit assigned to your account yet.<br>Please contact your administrator.</p></div>';
    return;
  }
  const t=d.data;
  let html='';

  // ── My assigned unit ──────────────────────────────────────
  if(t.rental_id){
    html+=`<div class="my-unit-card" style="border-left:4px solid var(--t400)">
      <div class="muc-icon"><svg viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/></svg></div>
      <div class="muc-info" style="flex:1">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
          <h2 style="margin:0">${esc(t.unit)}</h2>
          <span style="font-size:11px;background:var(--t50);color:var(--t600);padding:2px 8px;border-radius:99px;font-weight:600">MY UNIT</span>
        </div>
        <p>${esc(t.unit_address||'—')}</p>
        <div style="margin-top:8px">${badge(t.rental_status||'Active')}</div>
        <div class="muc-meta">
          <span>Type: <strong>${esc(t.unit_type||'—')}</strong></span>
          <span>Monthly Rent: <strong>${php(t.rent)}</strong></span>
          <span>Due: <strong>${t.due_day?'Day '+t.due_day+' of month':'—'}</strong></span>
          <span>Lease Start: <strong>${fmtD(t.lease_start)}</strong></span>
          <span>Lease End: <strong>${fmtD(t.lease_end)}</strong></span>
        </div>
        ${t.unit_notes?`<p style="margin-top:10px;font-size:13px;color:var(--g500)">Note: ${esc(t.unit_notes)}</p>`:''}
      </div>
    </div>`;
  } else {
    html+=`<div class="notice"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>You have no unit assigned yet. Contact your admin.</div>`;
  }

  // ── Other occupied units (neighbors) — no personal info ──
  const nb=t.neighbors||[];
  if(nb.length){
    html+=`<div class="sec" style="margin-top:20px">
      <div class="sh"><div><div class="stl">Other Occupied Units</div><div class="stb">Unit availability in the building — no tenant info shown</div></div></div>
      <table><thead><tr><th>Unit</th><th>Type</th><th>Address</th><th>Status</th></tr></thead><tbody>`;
    html+=nb.map(r=>`<tr>
      <td class="tn">${esc(r.unit)}</td>
      <td>${esc(r.type)}</td>
      <td style="font-size:12px;color:var(--g500)">${esc(r.unit_address||'—')}</td>
      <td>${badge(r.status)}</td>
    </tr>`).join('');
    html+=`</tbody></table></div>`;
  }

  // ── Vacant / available units ──────────────────────────────
  const vc=t.vacant_units||[];
  if(vc.length){
    html+=`<div class="sec" style="margin-top:20px">
      <div class="sh"><div><div class="stl">Available Units</div><div class="stb">Currently vacant — contact admin to inquire</div></div></div>
      <table><thead><tr><th>Unit</th><th>Type</th><th>Address</th><th>Monthly Rent</th><th>Notes</th></tr></thead><tbody>`;
    html+=vc.map(r=>`<tr>
      <td class="tn">${esc(r.unit)}</td>
      <td>${esc(r.type)}</td>
      <td style="font-size:12px;color:var(--g500)">${esc(r.unit_address||'—')}</td>
      <td><strong>${php(r.rent)}</strong></td>
      <td style="font-size:12px;color:var(--g500)">${esc(r.notes||'—')}</td>
    </tr>`).join('');
    html+=`</tbody></table></div>`;
  }

  document.getElementById('my-unit-content').innerHTML=html;
}

// ══════ MY PAYMENTS ════════════════════════════════════════
async function loadMyPayments(){
  const sf=document.getElementById('mp-sf')?.value||'';
  const pr=new URLSearchParams({page:mpPage,action:'panel'});
  document.getElementById('mp-tbody').innerHTML='<tr><td colspan="6" class="loading">Loading…</td></tr>';
  const d=await api(`${API.p}?${pr}`);if(!d)return;

  // Stats from panel summary
  const sm=d.summary||{};
  document.getElementById('mp-total').textContent=php(sm.total_paid||0);
  document.getElementById('mp-pending').textContent=sm.unpaid_months||0;
  document.getElementById('mp-count').textContent=sm.paid_months||d.all?.filter(p=>p.status==='Paid').length||0;

  // Current month callout
  const cm=d.current_payment;
  const cmHtml=cm
    ?`<div style="background:${cm.status==='Paid'?'var(--grbg)':cm.status==='Partial'?'var(--ambg)':'var(--rdbg)'};border-radius:var(--r);padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:13px;font-weight:500">This month (${fmtM(d.current_month)})</span>
        ${badge(cm.status)}
      </div>`
    :`<div style="background:var(--rdbg);border-radius:var(--r);padding:12px 18px;margin-bottom:16px;font-size:13px;color:var(--rd);font-weight:500">
        This month (${fmtM(d.current_month||new Date().toISOString().slice(0,7))}) — No payment recorded yet ${badge('Pending')}
      </div>`;

  // Build table sorted by month, grouped: Pending first, then Partial, then Paid
  let all=d.all||[];
  if(sf) all=all.filter(p=>p.status===sf);

  document.getElementById('mp-empty').style.display=all.length?'none':'block';

  // Group by month desc, within each month show status order
  const grouped={};
  all.forEach(p=>{
    const m=p.month_for||'unknown';
    if(!grouped[m]) grouped[m]=[];
    grouped[m].push(p);
  });
  const months=Object.keys(grouped).sort((a,b)=>b.localeCompare(a));

  let rows='';
  months.forEach(m=>{
    // Sort within month: Pending → Partial → Paid
    const mRows=grouped[m].sort((a,b)=>{
      const o={Pending:0,Partial:1,Paid:2};
      return (o[a.status]??3)-(o[b.status]??3);
    });
    rows+=`<tr><td colspan="6" class="month-group-hd">${fmtM(m)}</td></tr>`;
    rows+=mRows.map(p=>`
      <tr class="${p.status==='Pending'?'row-pending':p.status==='Partial'?'row-partial':''}">
        <td><strong>${fmtM(p.month_for)}</strong></td>
        <td>${php(p.amount)}</td>
        <td>${p.status==='Pending'?'<span style="color:var(--g400);font-size:12px">Not paid</span>':fmtD(p.paid_date)}</td>
        <td>${p.status!=='Pending'?esc(p.method||'—'):'—'}</td>
        <td>${badge(p.status)}</td>
        <td style="font-size:12px;color:var(--g500)">${esc(p.note||'—')}</td>
      </tr>`).join('');
  });

  // Inject the current-month callout above the table
  const tblContainer=document.getElementById('mp-tbody').closest('.sec');
  const existingCallout=tblContainer.querySelector('.month-callout');
  if(existingCallout) existingCallout.remove();
  const calloutDiv=document.createElement('div');
  calloutDiv.className='month-callout';
  calloutDiv.style.cssText='padding:12px 18px 0';
  calloutDiv.innerHTML=cmHtml;
  tblContainer.querySelector('.sh').after(calloutDiv);

  document.getElementById('mp-tbody').innerHTML=rows;
  document.getElementById('mp-info').textContent=`${all.length} record${all.length!==1?'s':''}`;
  // No pagination needed — all records shown (already from panel endpoint)
  document.getElementById('mp-pages').innerHTML='';
}

// ══════ MY PROFILE ═════════════════════════════════════════
async function loadMyProfile(){
  document.getElementById('my-profile-content').innerHTML='<div class="loading">Loading…</div>';
  const d=await api(`${API.t}`);if(!d)return;
  if(!d.linked||!d.data){
    document.getElementById('my-profile-content').innerHTML='<p style="color:var(--g400);font-size:13px">Profile not found. Contact your administrator.</p>';
    return;
  }
  const t=d.data;
  const sm=t.payment_summary||{};
  document.getElementById('my-profile-content').innerHTML=`
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px">
      <div style="background:var(--grbg);border-radius:var(--r);padding:14px 16px;text-align:center">
        <div style="font-size:18px;font-weight:700;color:var(--gr);font-family:'Syne',sans-serif">${php(sm.total_paid||0)}</div>
        <div style="font-size:11px;color:var(--gr);margin-top:2px">Total Paid</div>
      </div>
      <div style="background:${sm.unpaid_months>0?'var(--rdbg)':'var(--grbg)'};border-radius:var(--r);padding:14px 16px;text-align:center">
        <div style="font-size:18px;font-weight:700;color:${sm.unpaid_months>0?'var(--rd)':'var(--gr)'};font-family:'Syne',sans-serif">${sm.unpaid_months||0}</div>
        <div style="font-size:11px;color:${sm.unpaid_months>0?'var(--rd)':'var(--gr)'};margin-top:2px">Unpaid Months</div>
      </div>
      <div style="background:var(--blbg);border-radius:var(--r);padding:14px 16px;text-align:center">
        <div style="font-size:18px;font-weight:700;color:var(--bl);font-family:'Syne',sans-serif">${sm.paid_months||0}</div>
        <div style="font-size:11px;color:var(--bl);margin-top:2px">Months Paid</div>
      </div>
    </div>
    <div class="vg">
      <div class="vi"><label>First Name</label><p>${esc(t.first_name||'—')}</p></div>
      <div class="vi"><label>Last Name</label><p>${esc(t.last_name||'—')}</p></div>
      <div class="vi"><label>Email</label><p>${esc(t.email||'—')}</p></div>
      <div class="vi"><label>Contact</label><p>${esc(t.contact||'—')}</p></div>
      <div class="vi full"><label>Personal Address</label><p>${esc(t.address||'—')}</p></div>
      <div class="vi"><label>ID Type</label><p>${esc(t.id_type||'—')}</p></div>
      <div class="vi"><label>ID Number</label><p>${esc(t.id_number||'—')}</p></div>
      <div class="vi"><label>Assigned Unit</label><p>${t.unit?`<span style="background:var(--t50);color:var(--t600);padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600">${esc(t.unit)}</span>`:'Not assigned'}</p></div>
      <div class="vi"><label>Unit Status</label><p>${t.rental_status?badge(t.rental_status):'—'}</p></div>
      <div class="vi"><label>Monthly Rent</label><p>${t.rent?`<strong>${php(t.rent)}</strong>`:'—'}</p></div>
      <div class="vi"><label>Due Day</label><p>${t.due_day?'Day '+t.due_day+' of the month':'—'}</p></div>
      <div class="vi"><label>Lease Start</label><p>${fmtD(t.lease_start)}</p></div>
      <div class="vi"><label>Lease End</label><p>${fmtD(t.lease_end)}</p></div>
      <div class="vi full"><label>Unit Address</label><p>${esc(t.unit_address||'—')}</p></div>
      ${t.notes?`<div class="vi full"><label>Notes</label><p style="color:var(--g500)">${esc(t.notes)}</p></div>`:''}
    </div>
    <p style="margin-top:20px;font-size:12.5px;color:var(--g400)">To update your information, please contact your administrator.</p>`;
}

function confirmDel(){if(delCb)delCb();}
async function logout(){await api(`${API.auth}?action=logout`,{method:'POST'});window.location.href='index.html';}

// ── INIT ─────────────────────────────────────────────────────
if(IS_ADMIN) loadRentals();
else loadMyUnit();
</script>
</body>
</html>
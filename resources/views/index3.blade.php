<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Cek Tagihan | MIT Muhammadiyah Sukarame</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="Cek tagihan siswa MIT Muhammadiyah Sukarame — Kota Bandar Lampung, Prov. Lampung">
<meta name="theme-color" content="#14532d">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="MIT Muhammadiyah Sukarame">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" type="image/png" href="{{ asset('mit.png') }}">
<link rel="apple-touch-icon" href="{{ asset('mit.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
(function(){
  try {
    if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
  } catch (e) {}
})();
window.__pwaDeferredPrompt = null;
window.__pwaInstallReady = false;
window.addEventListener('beforeinstallprompt', function (e) {
  e.preventDefault();
  window.__pwaDeferredPrompt = e;
  window.__pwaInstallReady = true;
  window.dispatchEvent(new Event('pwa-install-ready'));
});
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
}
</script>
<script src="https://cdn.tailwindcss.com"></script>
@if(config('services.turnstile.enabled'))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{box-sizing:border-box;-webkit-tap-highlight-color:transparent}
:root{
--bg:#f3f6f4;--surface:#ffffff;--surface2:#f0f4f1;--border:#dce6df;--border2:#c5d4ca;
--text:#13261a;--text2:#3f5a48;--text3:#6b8173;
--accent:#15803d;--accent-h:#166534;--accent-soft:#ecf8ef;
--danger:#dc2626;
color-scheme:light;
}
html.dark{
--bg:#0c1610;--surface:#15241b;--surface2:#1c2e24;--border:#2a4033;--border2:#3d5a48;
--text:#eef6f0;--text2:#b7c9bc;--text3:#87a090;
--accent:#4ade80;--accent-h:#22c55e;--accent-soft:#14532d;
--danger:#f87171;
color-scheme:dark;
}
html,body{margin:0}
body{font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;min-height:100dvh;transition:background .2s,color .2s}
.page-bg{position:fixed;inset:0;pointer-events:none;background:
  radial-gradient(1200px 420px at 50% -10%, rgba(21,128,61,.16), transparent 60%),
  radial-gradient(700px 280px at 100% 100%, rgba(21,128,61,.07), transparent 55%)}
html.dark .page-bg{background:
  radial-gradient(1200px 420px at 50% -10%, rgba(74,222,128,.12), transparent 60%),
  radial-gradient(700px 280px at 100% 100%, rgba(20,83,45,.35), transparent 55%)}
.wrap{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:1.25rem 1rem 2.5rem;padding-left:max(1rem,env(safe-area-inset-left));padding-right:max(1rem,env(safe-area-inset-right));padding-bottom:max(2.5rem,env(safe-area-inset-bottom))}
.form-grid{display:grid;grid-template-columns:1fr}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;gap:10px}
.brand-wrap{display:flex;align-items:center;gap:12px;min-width:0}
.brand-logo{height:52px;width:auto;max-width:220px;object-fit:contain;object-position:left center;flex-shrink:0;border-radius:8px;background:#fff}
.brand{font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--text3);text-transform:uppercase;margin-bottom:3px}
h1{font-size:1.35rem;font-weight:700;color:var(--text);line-height:1.25;margin:0}
.theme-btn{display:flex;align-items:center;justify-content:center;gap:7px;min-width:44px;min-height:44px;padding:0 12px;border-radius:12px;border:1px solid var(--border2);background:var(--surface);font-size:13px;color:var(--text2);cursor:pointer;transition:background .15s,border .15s;flex-shrink:0}
.theme-btn:hover{background:var(--surface2)}
.theme-label{white-space:nowrap}
.topbar-actions{display:flex;align-items:center;gap:8px;flex-shrink:0}
.install-btn{display:flex;align-items:center;justify-content:center;gap:7px;min-width:44px;min-height:44px;padding:0 12px;border-radius:12px;border:1px solid var(--border2);background:var(--accent-soft);font-size:13px;font-weight:600;color:var(--accent);cursor:pointer;transition:background .15s,border .15s;flex-shrink:0}
.install-btn.is-hidden{display:none !important}
.install-btn:hover{background:var(--border)}
.install-hero{text-align:center;padding:.35rem 0 .2rem}
.install-hero img{width:72px;height:72px;border-radius:16px;object-fit:cover;margin:0 auto .9rem;display:block;background:#fff}
.install-hero h4{margin:0 0 .45rem;font-size:1.05rem;font-weight:700;color:var(--text)}
.install-hero p{margin:0;font-size:13px;color:var(--text2);line-height:1.5}
.btn-install-now{width:100%;min-height:48px;border:none;border-radius:12px;background:var(--accent);color:#fff;font-size:15px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-install-now:hover{background:var(--accent-h)}
.btn-install-now:disabled{opacity:.65;cursor:not-allowed}
html.dark .btn-install-now{color:#052e16}
.card{background:var(--surface);border:1px solid var(--border);border-radius:18px;padding:1.35rem 1.2rem;margin-bottom:1rem;box-shadow:0 10px 28px rgba(19,38,26,.05)}
.section-title{font-size:11px;font-weight:700;letter-spacing:.08em;color:var(--text3);text-transform:uppercase;margin-bottom:1.15rem;padding-bottom:.8rem;border-bottom:1px solid var(--border)}
.field{margin-bottom:1rem}
.field label{display:block;font-size:13px;font-weight:600;color:var(--text2);margin-bottom:7px}
.field label em{color:var(--danger);font-style:normal}
.field input,.field select{width:100%;min-height:48px;padding:12px 14px;border-radius:12px;border:1px solid var(--border2);background-color:var(--surface2);color:var(--text);font-size:16px;outline:none;transition:border .15s,background .15s,box-shadow .15s}
.pw-wrap{position:relative}
.pw-wrap input{padding-right:48px}
.pw-toggle{position:absolute;right:6px;top:50%;transform:translateY(-50%);width:40px;height:40px;border:none;background:transparent;color:var(--text3);cursor:pointer;display:flex;align-items:center;justify-content:center;border-radius:10px}
.pw-toggle:hover{color:var(--text);background:var(--surface)}
.field select{appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%236b8173' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;background-size:16px;padding-right:40px}
html.dark .field select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2387a090' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E")}
.field input:focus,.field select:focus{border-color:var(--accent);background-color:var(--surface);box-shadow:0 0 0 4px rgba(21,128,61,.12)}
.field input::placeholder{color:var(--text3)}
.field select option{background:var(--surface);color:var(--text)}
.turnstile-area{display:flex;justify-content:center;padding:.35rem 0;overflow-x:auto;max-width:100%}
.submit-btn{width:100%;min-height:50px;padding:13px 16px;border-radius:12px;border:none;background:var(--accent);color:#fff;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:1.15rem;transition:background .15s,transform .15s}
.submit-btn:hover{background:var(--accent-h)}
.submit-btn:active{transform:scale(.99)}
html.dark .submit-btn{color:#052e16}
.divider{border:none;border-top:1px solid var(--border);margin:1.35rem 0}
.student-grid{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1.15rem}
.sf{background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:11px 12px}
.sf label{display:block;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:var(--text3);margin-bottom:4px}
.sf p{font-size:14px;font-weight:650;color:var(--text);margin:0;word-break:break-word;line-height:1.35}
.nova-row{display:flex;align-items:center;justify-content:space-between;gap:8px}
.nova-row p{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;letter-spacing:.02em;min-width:0}
.btn-copy-nova{flex-shrink:0;min-width:34px;height:34px;padding:0 8px;border-radius:9px;border:1px solid var(--border2);background:var(--surface);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;gap:5px;cursor:pointer;transition:background .15s,border .15s}
.btn-copy-nova:hover{background:var(--accent-soft);border-color:var(--accent)}
.btn-copy-nova span{font-size:11px;font-weight:700}
.btn-copy{min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--border2);background:var(--surface);color:var(--accent);font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:7px}
.btn-copy:hover{background:var(--accent-soft);border-color:var(--accent)}
.tbl-bar{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;flex-wrap:wrap;gap:.65rem}
.tbl-title{font-size:15px;font-weight:700;color:var(--text);line-height:1.35}
.tbl-controls{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.tbl-controls select{min-height:38px;padding:7px 30px 7px 10px;border-radius:10px;border:1px solid var(--border2);background-color:var(--surface2);color:var(--text);font-size:13px;outline:none;cursor:pointer;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%236b8173' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center}
html.dark .tbl-controls select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%2387a090' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E")}
.btn-showall{min-height:38px;padding:7px 12px;border-radius:10px;border:1px solid var(--border2);background:var(--surface2);color:var(--text2);font-size:13px;cursor:pointer;transition:background .15s}
.btn-showall:hover{background:var(--border)}
.btn-logout{min-height:38px;padding:7px 12px;border-radius:10px;border:1px solid #fecaca;background:transparent;color:#dc2626;font-size:13px;font-weight:600;cursor:pointer;transition:background .15s}
.btn-logout:hover{background:#fef2f2}
html.dark .btn-logout{border-color:#7f1d1d;color:#f87171}
html.dark .btn-logout:hover{background:#450a0a}
.tbl-info{font-size:12px;color:var(--text3)}
.tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:12px;border:1px solid var(--border);background:var(--surface)}
table{width:100%;border-collapse:collapse;font-size:13px}
thead tr{background:var(--surface2)}
th{padding:11px 12px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--text3);text-transform:uppercase;border-bottom:1px solid var(--border);white-space:nowrap}
td{padding:11px 12px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover{background:var(--surface2)}
.card-list{display:none;flex-direction:column;gap:10px}
.bill-card{background:var(--surface2);border:1px solid var(--border);border-radius:14px;padding:13px 14px}
.bill-card-top{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px}
.bill-card h3{margin:0 0 6px;font-size:15px;font-weight:650;line-height:1.35;color:var(--text)}
.bill-amount{margin:0 0 8px;font-size:17px;font-weight:700;color:var(--accent)}
.bill-meta{display:flex;flex-wrap:wrap;gap:6px 10px;font-size:12px;color:var(--text3)}
.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:100px;font-size:11px;font-weight:700}
.badge-unpaid{background:#fef2f2;color:#b91c1c}
html.dark .badge-unpaid{background:#450a0a;color:#fca5a5}
.badge-paid{background:#ecf8ef;color:#15803d}
html.dark .badge-paid{background:#052e16;color:#86efac}
.btn-detail{min-height:34px;padding:6px 12px;border-radius:9px;border:1px solid #bbf7d0;color:#15803d;background:transparent;font-size:12px;font-weight:600;cursor:pointer;transition:background .15s}
.btn-detail:hover{background:#ecf8ef}
html.dark .btn-detail{border-color:#14532d;color:#86efac}
html.dark .btn-detail:hover{background:#14532d}
.pagination{display:flex;gap:6px;justify-content:center;margin-top:.9rem;flex-wrap:wrap}
.pg-btn{min-width:38px;min-height:38px;padding:6px 10px;border-radius:10px;border:1px solid var(--border2);background:var(--surface2);color:var(--text2);font-size:13px;cursor:pointer;transition:background .15s}
.pg-btn:hover:not(.pg-active){background:var(--border)}
.pg-active{background:var(--accent);color:#fff;border-color:var(--accent)}
html.dark .pg-active{color:#052e16}
.error-box{margin-top:.25rem;background:#fef2f2;border:1px solid #fecaca;border-radius:14px;padding:1rem 1.15rem}
.error-box p{color:#b91c1c;font-size:14px;margin:0}
html.dark .error-box{background:#450a0a;border-color:#7f1d1d}
html.dark .error-box p{color:#fca5a5}
.modal-bg{display:none;position:fixed;inset:0;background:rgba(12,22,16,.5);z-index:100;align-items:center;justify-content:center;padding:1rem;padding-bottom:max(1rem,env(safe-area-inset-bottom))}
.modal-bg.open{display:flex}
.modal-box{background:var(--surface);border:1px solid var(--border2);border-radius:18px;width:100%;max-width:520px;max-height:88vh;overflow-y:auto}
.modal-head{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.15rem;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--surface)}
.modal-head h3{font-size:16px;font-weight:700;color:var(--text);margin:0}
.modal-x{background:none;border:none;cursor:pointer;color:var(--text3);width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center}
.modal-x:hover{background:var(--surface2);color:var(--text)}
.modal-body{padding:1.15rem}
.modal-row{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
.modal-row:last-child{border-bottom:none}
.modal-row-lbl{font-size:13px;color:var(--text2)}
.modal-row-val{font-size:13px;font-weight:650;color:var(--text);text-align:right}
.modal-foot{padding:.9rem 1.15rem;border-top:1px solid var(--border)}
.btn-close-full{width:100%;min-height:44px;padding:10px;border-radius:12px;border:1px solid var(--border2);background:transparent;color:var(--text2);font-size:14px;font-weight:600;cursor:pointer}
.btn-close-full:hover{background:var(--surface2)}
.chk{width:16px;height:16px;accent-color:var(--accent);cursor:pointer}
.select-all-mobile{display:none;align-items:center;gap:8px;font-size:13px;font-weight:600;color:var(--text2);margin-bottom:.65rem}
.pay-note{font-size:13px;color:var(--danger);margin:.95rem 0 .7rem}
.pay-summary{display:none;justify-content:space-between;align-items:center;gap:10px;padding:12px 14px;border-radius:12px;background:var(--accent-soft);border:1px solid var(--border);margin-bottom:.75rem;font-size:13px;color:var(--text2)}
.pay-summary.open{display:flex}
.pay-summary b{color:var(--accent)}
.pay-btn{width:100%;min-height:50px;padding:13px 16px;border-radius:12px;border:none;background:#15803d;color:#fff;font-size:15px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s}
.pay-btn:hover{background:#166534}
html.dark .pay-btn{background:#16a34a;color:#052e16}
.pay-btn:disabled{opacity:.6;cursor:not-allowed}
.bill-check{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:var(--text2)}
.modal-box.pay-modal{max-width:960px}
.pay-info{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px 16px;margin-bottom:1.15rem;padding:14px 16px;border:1px solid var(--border);border-radius:12px;background:var(--surface2)}
.pay-info .pi-lbl{display:block;font-size:11px;font-weight:600;letter-spacing:.03em;text-transform:uppercase;color:var(--text3);margin-bottom:4px}
.pay-info .pi-val{font-size:14px;font-weight:650;color:var(--text);line-height:1.35;word-break:break-word}
.pay-tbl-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:.15rem 0 1rem;border:1px solid var(--border);border-radius:12px}
.pay-tbl{width:100%;min-width:720px;border-collapse:collapse;table-layout:fixed}
.pay-tbl th{padding:10px 12px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;color:var(--text3);background:var(--surface2);border-bottom:1px solid var(--border);white-space:nowrap}
.pay-tbl td{padding:10px 12px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:13px;color:var(--text)}
.pay-tbl tr:last-child td{border-bottom:none}
.pay-tbl th:nth-child(1){width:22%}
.pay-tbl th:nth-child(2),.pay-tbl th:nth-child(3){width:12%}
.pay-tbl th:nth-child(4){width:9%;text-align:center}
.pay-tbl th:nth-child(5),.pay-tbl th:nth-child(6){width:14%;text-align:right}
.pay-tbl th:nth-child(7){width:17%;text-align:right}
.pay-tbl td.num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
.pay-tbl td.cicil{text-align:center}
.pay-tbl td.bayar-col{text-align:right}
.pay-cell.bayar-input{width:100%;max-width:140px;min-height:40px;padding:8px 10px;border-radius:8px;border:1px solid var(--accent);background:var(--surface);color:var(--text);font-size:13px;text-align:right;font-variant-numeric:tabular-nums;margin-left:auto;display:block;-moz-appearance:textfield}
.pay-cell.bayar-input::-webkit-outer-spin-button,.pay-cell.bayar-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.pay-cell.bayar-input[readonly],.pay-cell.bayar-input:disabled{opacity:.9;cursor:default;border-color:var(--border2);background:var(--surface2)}
.pay-cell.bayar-input:focus{outline:none;box-shadow:0 0 0 4px rgba(21,128,61,.12)}
.pay-total{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:12px;background:var(--accent-soft);margin-bottom:1rem;font-size:14px;font-weight:700}
.pay-actions{display:flex;gap:10px}
.btn-ghost{flex:1;min-height:46px;border-radius:12px;border:1px solid var(--border2);background:var(--surface);color:var(--text2);font-size:14px;font-weight:600;cursor:pointer}
.btn-ghost:hover{background:var(--surface2)}
.btn-pay-confirm{flex:1.4;min-height:46px;border-radius:12px;border:none;background:#16a34a;color:#fff;font-size:14px;font-weight:600;cursor:pointer}
.btn-pay-confirm:hover{background:#15803d}
.btn-pay-confirm:disabled{opacity:.65;cursor:not-allowed}
html.dark .btn-pay-confirm{color:#052e16;background:#4ade80}
html.dark .btn-pay-confirm:hover{background:#22c55e}
.va-box{text-align:center;padding:.35rem 0 .2rem}
.va-box h4{margin:0 0 .55rem;font-size:15px;font-weight:700}
.va-number{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:1.35rem;font-weight:700;letter-spacing:.04em;color:var(--accent);word-break:break-all;margin:.2rem 0 .7rem}
.va-meta{font-size:13px;color:var(--text2);margin:0 0 .35rem}
.va-help{font-size:12px;color:var(--text3);line-height:1.5;margin:.85rem 0 0}
.pay-input{width:118px;min-height:38px;padding:7px 10px;border-radius:10px;border:1px solid var(--border2);background:var(--surface2);color:var(--text);font-size:13px;text-align:right}
.pay-input:disabled{opacity:.65;cursor:not-allowed}
.pay-input:focus{border-color:var(--accent);outline:none;box-shadow:0 0 0 4px rgba(21,128,61,.12);background:var(--surface)}
.badge-cicil{background:#ecf8ef;color:#15803d}
html.dark .badge-cicil{background:#052e16;color:#86efac}
.badge-no-cicil{background:#fff7ed;color:#c2410c}
html.dark .badge-no-cicil{background:#431407;color:#fdba74}
.badge-active{background:#ecf8ef;color:#15803d}
html.dark .badge-active{background:#052e16;color:#86efac}
.badge-inactive{background:var(--surface2);color:var(--text3);border:1px solid var(--border)}
.ma-list{display:flex;flex-direction:column;gap:8px;margin-bottom:1.15rem}
.ma-item{display:flex;align-items:center;justify-content:space-between;gap:10px;width:100%;padding:12px 14px;border-radius:12px;border:1px solid var(--border);background:var(--surface2);text-align:left;cursor:pointer;transition:border .15s,background .15s,box-shadow .15s;color:var(--text);font:inherit}
.ma-item:hover:not(.is-active){border-color:var(--accent);background:var(--surface);box-shadow:0 0 0 3px rgba(21,128,61,.1)}
.ma-item.is-active{border-color:var(--accent);cursor:default;box-shadow:0 0 0 3px rgba(21,128,61,.12)}
.ma-item-main{min-width:0;flex:1}
.ma-item-name{font-size:14px;font-weight:700;margin:0 0 2px;word-break:break-word}
.ma-item-meta{font-size:12px;color:var(--text3);margin:0;word-break:break-word}
.ma-item-right{display:flex;flex-direction:row;align-items:center;gap:8px;flex-shrink:0}
.ma-del{width:34px;height:34px;border-radius:9px;border:1px solid #fecaca;background:transparent;color:#dc2626;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;padding:0}
.ma-del:hover{background:#fef2f2}
html.dark .ma-del{border-color:#7f1d1d;color:#f87171}
html.dark .ma-del:hover{background:#450a0a}
.ma-section-label{font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text3);margin:0 0 .75rem}
.ma-error{display:none;margin:0 0 .85rem;padding:10px 12px;border-radius:10px;background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;font-size:13px}
html.dark .ma-error{background:#450a0a;border-color:#7f1d1d;color:#fca5a5}
.ma-error.open{display:block}
.ma-success{display:none;margin:0 0 .85rem;padding:10px 12px;border-radius:10px;background:#ecf8ef;border:1px solid #bbf7d0;color:#15803d;font-size:13px}
html.dark .ma-success{background:#052e16;border-color:#14532d;color:#86efac}
.ma-success.open{display:block}
.siswa-head{display:flex;justify-content:space-between;align-items:center;gap:.65rem;margin-bottom:1.15rem;padding-bottom:.8rem;border-bottom:1px solid var(--border);flex-wrap:wrap}
.siswa-head .section-title{margin:0;padding:0;border:none}
.bill-pay-row{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:10px}
.footer{text-align:center;font-size:12px;color:var(--text3);margin-top:1.5rem;padding-bottom:.5rem}
.scroll-top{position:fixed;bottom:max(1.15rem,env(safe-area-inset-bottom));right:max(1rem,env(safe-area-inset-right));width:44px;height:44px;border-radius:50%;border:1px solid var(--border2);background:var(--surface);color:var(--text2);display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 20px rgba(19,38,26,.12);z-index:20}
.scroll-top:hover{background:var(--surface2)}
.empty-note{text-align:center;padding:1.5rem 1rem;color:var(--text3);font-size:13px}
.guide-card{overflow:hidden}
.guide-layout{display:grid;grid-template-columns:1fr;gap:1.1rem}
.guide-preview{position:relative;display:block;width:100%;border:none;padding:0;border-radius:14px;overflow:hidden;cursor:pointer;background:var(--surface2);border:1px solid var(--border);aspect-ratio:16/11;text-align:left}
.guide-preview img{width:100%;height:100%;object-fit:cover;object-position:top center;display:block;transition:transform .35s ease}
.guide-preview:hover img{transform:scale(1.03)}
.guide-preview-overlay{position:absolute;inset:auto 0 0 0;display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;background:linear-gradient(transparent,rgba(12,22,16,.72));color:#fff;font-size:13px;font-weight:700}
.guide-copy h2{margin:0 0 .4rem;font-size:1.05rem;font-weight:700;color:var(--text);line-height:1.3}
.guide-copy p{margin:0 0 .9rem;font-size:13px;color:var(--text2);line-height:1.55}
.guide-tips{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 1.05rem;padding:0;list-style:none}
.guide-tips li{display:inline-flex;align-items:center;gap:6px;padding:7px 11px;border-radius:999px;background:var(--accent-soft);border:1px solid var(--border);font-size:12px;font-weight:600;color:var(--text2)}
.guide-tips li b{color:var(--accent);font-weight:700}
.guide-actions{display:flex;flex-wrap:wrap;gap:8px}
.btn-guide{min-height:44px;padding:10px 16px;border-radius:12px;border:none;background:var(--accent);color:#fff;font-size:14px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:background .15s}
.btn-guide:hover{background:var(--accent-h)}
html.dark .btn-guide{color:#052e16}
.btn-guide-ghost{min-height:44px;padding:10px 16px;border-radius:12px;border:1px solid var(--border2);background:var(--surface);color:var(--text2);font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;transition:background .15s}
.btn-guide-ghost:hover{background:var(--surface2)}
.guide-link{display:inline-flex;align-items:center;gap:6px;margin-top:.85rem;font-size:13px;font-weight:600;color:var(--accent);background:none;border:none;cursor:pointer;padding:0}
.guide-link:hover{text-decoration:underline}
.modal-box.panduan-modal{max-width:min(920px,100%);max-height:92vh}
.panduan-body{padding:.85rem 1rem 1rem}
.panduan-frame{border-radius:12px;border:1px solid var(--border);background:var(--surface2);overflow:auto;max-height:min(72vh,820px);-webkit-overflow-scrolling:touch}
.panduan-frame img{display:block;width:100%;height:auto}
.panduan-note{margin:.75rem 0 0;font-size:12px;color:var(--text3);line-height:1.5;text-align:center}
@media(min-width:900px){
.wrap{padding:1.5rem 1.5rem 2.75rem;padding-left:max(1.5rem,env(safe-area-inset-left));padding-right:max(1.5rem,env(safe-area-inset-right))}
.form-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:0 1.15rem;align-items:end}
.form-grid .field{margin-bottom:1rem}
.student-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:.85rem}
.card{padding:1.5rem 1.45rem}
.guide-layout{grid-template-columns:minmax(0,1.05fr) minmax(0,1fr);gap:1.35rem;align-items:center}
.guide-preview{aspect-ratio:4/3}
}
@media(max-width:720px){
.tbl-wrap{display:none}
.card-list{display:flex}
.select-all-mobile{display:flex}
.pay-actions{flex-direction:column}
.pay-info{grid-template-columns:1fr 1fr}
}
@media(max-width:520px){
.wrap{padding-top:1rem}
.brand-logo{height:42px;max-width:170px}
h1{font-size:1.15rem}
.theme-label{display:none}
.theme-btn,.install-btn{padding:0;width:44px}
.card{padding:1.1rem .95rem;border-radius:16px}
.student-grid{gap:.55rem}
.sf{padding:10px}
.sf p{font-size:13px}
.tbl-title{font-size:14px;width:100%}
.modal-bg{align-items:flex-end;padding:0}
.modal-box{max-width:none;border-radius:18px 18px 0 0;max-height:90vh}
}
</style>
</head>
<body>
@php $viewOnly = true; @endphp
<div class="page-bg"></div>
<div class="wrap">
  <div class="topbar">
    <div class="brand-wrap">
      <img src="{{ asset('mit.png') }}" alt="MIT Muhammadiyah Sukarame" class="brand-logo">
      <div>
        <div class="brand">MIT Muhammadiyah Sukarame</div>
        <h1>Cek tagihan</h1>
        <div class="brand" style="margin-top:4px;text-transform:none;letter-spacing:0;font-weight:500">Kota Bandar Lampung, Prov. Lampung</div>
      </div>
    </div>
    <div class="topbar-actions">
      <button class="theme-btn" onclick="toggleTheme()" id="themeBtn" type="button" aria-label="Ubah tema">
        <svg id="themeIcon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <span class="theme-label" id="themeLabel">Mode gelap</span>
      </button>
      <button class="install-btn" id="installBtn" type="button" aria-label="Install aplikasi" title="Install aplikasi">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span class="theme-label">Install</span>
      </button>
    </div>
  </div>

  <div class="card" id="akunCard" @if(isset($result) && !empty($result['status'])) hidden @endif>
    <form method="POST" action="/" id="billForm">
      @csrf
      <div class="section-title">Informasi akun</div>
      <div class="form-grid">
        <div class="field">
          <label>Nomor VA <em>*</em></label>
          <input type="text" name="no_cust" id="noCust" inputmode="numeric" autocomplete="username" placeholder="777726xxxxxxxxxx" value="{{ old('no_cust', $va ?? '') }}" required>
        </div>
        <div class="field">
          <label>Password <em>*</em></label>
          <div class="pw-wrap">
            <input type="password" name="password" id="password" autocomplete="current-password" placeholder="Masukkan password" required>
            <button type="button" class="pw-toggle" id="togglePassword" aria-label="Tampilkan password">
              <svg id="iconEye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg id="iconEyeOff" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" hidden><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.223-3.444M6.18 6.18A9.966 9.966 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.974 9.974 0 01-4.245 5.253M3 3l18 18"/></svg>
            </button>
          </div>
        </div>
      </div>
      <input type="hidden" name="academic_year" id="academic_year" value="all">
      @if(config('services.turnstile.enabled'))
      <div class="field">
        <label>Verifikasi keamanan <em>*</em></label>
        <div class="turnstile-area">
          <div id="turnstile-widget"></div>
          <input type="hidden" name="cf_turnstile_response" id="cfToken">
        </div>
      </div>
      @endif
      <button type="submit" name="submit" class="submit-btn">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Cek tagihan
      </button>
    </form>
    <button type="button" class="guide-link" onclick="openPanduanModal()">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      Lihat panduan pembayaran VA
    </button>

    @if(session('error'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      var dark = document.documentElement.classList.contains('dark');
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: @json(session('error')),
        confirmButtonColor: '#15803d',
        background: dark ? '#15241b' : '#fff',
        color: dark ? '#eef6f0' : '#13261a'
      });
    });
    </script>
    @endif
    @if(session('success'))
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      var dark = document.documentElement.classList.contains('dark');
      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: @json(session('success')),
        timer: 2000,
        showConfirmButton: false,
        background: dark ? '#15241b' : '#fff',
        color: dark ? '#eef6f0' : '#13261a'
      });
    });
    </script>
    @endif
  </div>

  @if(isset($result))
    @if($result['status'])
      <div class="card" id="resultSection">
        <div class="siswa-head">
          <div class="section-title">Data siswa</div>
          <div class="tbl-controls">
            <button type="button" class="btn-logout" id="btnLogout" onclick="logoutAkun()">Logout</button>
            <button type="button" class="btn-showall" id="btnMultiAkun" onclick="openMultiAkunModal()">Multi akun</button>
          </div>
        </div>
        @php
          $angkatanSiswa = $result['data']['angkatan'] ?? $result['data']['tahun_akademik'] ?? $result['data']['tahun_masuk'] ?? '-';
          $kelompokSiswa = $result['data']['kelompok'] ?? $result['data']['jurusan'] ?? '-';
        @endphp
        <div class="student-grid">
          <div class="sf"><label>Nama</label><p>{{ $result['data']['nama'] ?? '-' }}</p></div>
          <div class="sf"><label>Kelas</label><p>{{ $result['data']['kelas'] ?? '-' }}</p></div>
          <div class="sf"><label>Kelompok</label><p>{{ $kelompokSiswa }}</p></div>
          <div class="sf"><label>Angkatan / Tahun masuk</label><p>{{ $angkatanSiswa }}</p></div>
          <div class="sf"><label>Saldo VA</label><p>Rp {{ number_format($result['data']['saldo'] ?? 0, 0, ',', '.') }}</p></div>
          @php
            $novaSiswa = \App\Http\Controllers\TagihanController::formatNova(
              $result['data']['no_cust'] ?? $result['data']['va_number'] ?? '-'
            );
          @endphp
          <div class="sf">
            <label>NOVA</label>
            <div class="nova-row">
              <p id="novaText">{{ $novaSiswa }}</p>
              @if($novaSiswa !== '' && $novaSiswa !== '-')
              <button type="button" class="btn-copy-nova" onclick="copyNova('novaText')" aria-label="Salin nomor VA">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                <span>Salin</span>
              </button>
              @endif
            </div>
          </div>
          <div class="sf"><label>Jenjang</label><p>{{ $result['data']['jenjang'] ?? '-' }}</p></div>
        </div>

        <div class="divider"></div>

        <div class="tbl-bar" style="align-items:flex-end">
          <div>
            <div class="tbl-title" id="tagihanTitle">Tagihan aktif — {{ ($academic_year ?? 'all') === 'all' ? 'Semua tahun akademik' : $academic_year }}</div>
            <p class="tbl-info" style="margin-top:4px">Filter berdasarkan tahun akademik / pelajaran</p>
          </div>
          <div class="tbl-controls">
            <select id="billYearFilter" aria-label="Filter tahun akademik" onchange="onBillYearChange(this)">
              <option value="all" {{ ($academic_year ?? 'all') === 'all' ? 'selected' : '' }}>Semua tahun akademik</option>
            </select>
          </div>
        </div>

        <div class="tbl-bar">
          <div class="tbl-controls">
            <select id="tagihanPerPage" onchange="changeTagihanPerPage()" aria-label="Jumlah data">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <button class="btn-showall" type="button" onclick="showAllTagihan()">Tampilkan semua</button>
          </div>
        </div>
        <div id="tagihanInfo" class="tbl-info" style="margin-bottom:.75rem"></div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                @unless($viewOnly)
                <th><input type="checkbox" class="chk" id="selectAll" onclick="toggleSelectAll(this)" aria-label="Pilih semua"></th>
                @endunless
                <th>No</th>
                <th>Nama tagihan</th>
                <th>Periode</th>
                <th>Nominal</th>
                <th>Sisa tagihan</th>
                <th>Sudah dibayar</th>
                @unless($viewOnly)
                <th>Dapat dicicil</th>
                <th>Bayar</th>
                @endunless
              </tr>
            </thead>
            <tbody id="tagihanTableBody">
              @forelse($result['data']['tagihan'] as $i => $tagih)
              @php
                $bolehCicil = (int)($tagih['isINSTALLABLE'] ?? $tagih['isinstallable'] ?? $tagih['is_installment'] ?? 0) === 1;
                $sudahBayar = (int)($tagih['sudah_dibayar'] ?? 0);
                $totalTagih = (int)($tagih['total_tagihan'] ?? 0);
                $sisaTagih = (int)($tagih['sisa_tagihan'] ?? max(0, $totalTagih - $sudahBayar));
                $tahunTagih = trim((string) ($tagih['tahun_akademik_tagihan'] ?? $tagih['BTA'] ?? ''));
              @endphp
              <tr data-index="{{ $i }}" data-tahun="{{ $tahunTagih }}">
                @unless($viewOnly)
                <td>
                  <input type="checkbox" class="chk tagihan-checkbox" value="{{ $tagih['AA'] ?? '' }}" data-index="{{ $i }}" {{ $sisaTagih <= 0 ? 'disabled' : '' }} aria-label="Pilih tagihan">
                </td>
                @endunless
                <td>{{ $i+1 }}</td>
                <td>{{ ucwords(str_replace('_', ' ', strtolower($tagih['nama_tagihan']))) }}</td>
                <td>{{ $tagih['periode'] ?: '-' }}</td>
                <td>Rp {{ number_format($totalTagih, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($sisaTagih, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($sudahBayar, 0, ',', '.') }}</td>
                @unless($viewOnly)
                <td>
                  @if($bolehCicil)
                    <span class="badge badge-cicil">Ya</span>
                  @else
                    <span class="badge badge-no-cicil">Tidak</span>
                  @endif
                </td>
                <td>
                  <input type="number" class="pay-input bayar-input" data-index="{{ $i }}" min="1" max="{{ $sisaTagih }}" value="0" disabled inputmode="numeric" aria-label="Nominal bayar">
                </td>
                @endunless
              </tr>
              @empty
              <tr><td colspan="{{ $viewOnly ? 6 : 9 }}" class="empty-note">Tidak ada data tersedia</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @unless($viewOnly)
        <label class="select-all-mobile">
          <input type="checkbox" class="chk" id="selectAllMobile" onclick="toggleSelectAll(this)"> Pilih semua
        </label>
        @endunless
        <div class="card-list" id="tagihanCardList">
          @forelse($result['data']['tagihan'] as $i => $tagih)
          @php
            $bolehCicil = (int)($tagih['isINSTALLABLE'] ?? $tagih['isinstallable'] ?? $tagih['is_installment'] ?? 0) === 1;
            $sudahBayar = (int)($tagih['sudah_dibayar'] ?? 0);
            $totalTagih = (int)($tagih['total_tagihan'] ?? 0);
            $sisaTagih = (int)($tagih['sisa_tagihan'] ?? max(0, $totalTagih - $sudahBayar));
            $tahunTagih = trim((string) ($tagih['tahun_akademik_tagihan'] ?? $tagih['BTA'] ?? ''));
          @endphp
          <article class="bill-card" data-index="{{ $i }}" data-tahun="{{ $tahunTagih }}">
            <div class="bill-card-top">
              @if($viewOnly)
                <span class="badge badge-unpaid">Belum lunas</span>
              @else
              <label class="bill-check">
                <input type="checkbox" class="chk tagihan-checkbox" value="{{ $tagih['AA'] ?? '' }}" data-index="{{ $i }}" {{ $sisaTagih <= 0 ? 'disabled' : '' }}> Pilih
              </label>
              @if($bolehCicil)
                <span class="badge badge-cicil">Bisa dicicil</span>
              @else
                <span class="badge badge-no-cicil">Tidak dicicil</span>
              @endif
              @endif
            </div>
            <h3>{{ ucwords(str_replace('_', ' ', strtolower($tagih['nama_tagihan']))) }}</h3>
            <p class="bill-amount">Rp {{ number_format($totalTagih, 0, ',', '.') }}</p>
            <div class="bill-meta">
              <span>Periode {{ $tagih['periode'] ?: '-' }}</span>
              <span>Sisa tagihan Rp {{ number_format($sisaTagih, 0, ',', '.') }}</span>
              <span>Sudah dibayar Rp {{ number_format($sudahBayar, 0, ',', '.') }}</span>
            </div>
            @unless($viewOnly)
            <div class="bill-pay-row">
              <span class="bill-meta">Bayar</span>
              <input type="number" class="pay-input bayar-input" data-index="{{ $i }}" min="1" max="{{ $sisaTagih }}" value="0" disabled inputmode="numeric" aria-label="Nominal bayar">
            </div>
            @endunless
          </article>
          @empty
          <div class="empty-note">Tidak ada data tersedia</div>
          @endforelse
        </div>
        <div id="tagihanPagination" class="pagination"></div>

        @if(!empty($result['data']['tagihan']))
          @if($viewOnly)
          <p class="pay-note" style="color:var(--text2)">Halaman ini hanya untuk melihat tagihan. Pembayaran tidak tersedia.</p>
          @else
          <p class="pay-note">*Pilih tagihan yang akan dibayar. Yang tidak bisa dicicil harus dibayar sesuai sisa. Yang bisa dicicil, nominal bayar tidak boleh melebihi sisa tagihan.</p>
          <div class="pay-summary" id="paySummary">
            <span id="paySummaryText">0 tagihan dipilih</span>
            <b id="paySummaryTotal">Rp 0</b>
          </div>
          <button type="button" class="pay-btn" id="btnBayar" onclick="showPaymentModal()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            Bayar tagihan
          </button>
          @endif
        @endif

        <div class="divider"></div>

        <div class="tbl-bar">
          <div class="tbl-title" id="lunasTitle">Tagihan lunas — {{ ($academic_year ?? 'all') === 'all' ? 'Semua tahun akademik' : $academic_year }}</div>
          <div class="tbl-controls">
            <select id="lunasPerPage" onchange="changeLunasPerPage()" aria-label="Jumlah data">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <button class="btn-showall" type="button" onclick="showAllLunas()">Tampilkan semua</button>
          </div>
        </div>
        <div id="lunasInfo" class="tbl-info" style="margin-bottom:.75rem"></div>
        <div class="tbl-wrap">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Urutan</th>
                <th>Nama tagihan</th>
                <th>Periode</th>
                <th>Nominal</th>
                <th>Tgl bayar</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody id="lunasTableBody">
              @forelse($result['data']['tagihan_lunas'] ?? [] as $i => $tagih)
              <tr data-index="{{ $i }}" data-tahun="{{ trim((string) ($tagih['tahun_akademik_tagihan'] ?? $tagih['BTA'] ?? '')) }}">
                <td>{{ $i+1 }}</td>
                <td>{{ $tagih['FURUTAN'] ?? '-' }}</td>
                <td>{{ ucwords(str_replace('_', ' ', strtolower($tagih['nama_tagihan']))) }}</td>
                <td>{{ $tagih['periode'] ?: '-' }}</td>
                <td>Rp {{ number_format($tagih['total_tagihan'], 0, ',', '.') }}</td>
                <td>{{ !empty($tagih['PAIDDT']) ? \Carbon\Carbon::parse($tagih['PAIDDT'])->format('Y-m-d') : '-' }}</td>
                <td><button type="button" class="btn-detail" onclick="showLunasDetail({{ $i }})">Lihat</button></td>
              </tr>
              @empty
              <tr><td colspan="7" class="empty-note">Tidak ada tagihan lunas</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-list" id="lunasCardList">
          @forelse($result['data']['tagihan_lunas'] ?? [] as $i => $tagih)
          <article class="bill-card" data-index="{{ $i }}" data-tahun="{{ trim((string) ($tagih['tahun_akademik_tagihan'] ?? $tagih['BTA'] ?? '')) }}">
            <div class="bill-card-top">
              <span class="badge badge-paid">Lunas</span>
              <button type="button" class="btn-detail" onclick="showLunasDetail({{ $i }})">Detail</button>
            </div>
            <h3>{{ ucwords(str_replace('_', ' ', strtolower($tagih['nama_tagihan']))) }}</h3>
            <p class="bill-amount">Rp {{ number_format($tagih['total_tagihan'], 0, ',', '.') }}</p>
            <div class="bill-meta">
              <span>Periode {{ $tagih['periode'] ?: '-' }}</span>
              <span>Urutan {{ $tagih['FURUTAN'] ?? '-' }}</span>
              <span>Bayar {{ !empty($tagih['PAIDDT']) ? \Carbon\Carbon::parse($tagih['PAIDDT'])->format('d M Y') : '-' }}</span>
            </div>
          </article>
          @empty
          <div class="empty-note">Tidak ada tagihan lunas</div>
          @endforelse
        </div>
        <div id="lunasPagination" class="pagination"></div>
      </div>
      <script>setTimeout(()=>{document.getElementById('resultSection').scrollIntoView({behavior:'smooth',block:'start'})},100);</script>
    @else
      <div class="error-box">
        <p>{{ $result['message'] ?? 'Data tidak ditemukan' }}</p>
      </div>
    @endif
  @endif

  <div class="card guide-card" id="panduanCard">
    <div class="section-title">Panduan pembayaran</div>
    <div class="guide-layout">
      <button type="button" class="guide-preview" onclick="openPanduanModal()" aria-label="Perbesar panduan pembayaran">
        <img src="{{ asset('panduan.png') }}" alt="Panduan pembayaran Virtual Account MIT Muhammadiyah Sukarame" loading="lazy" decoding="async">
        <span class="guide-preview-overlay">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
          Perbesar panduan
        </span>
      </button>
      <div class="guide-copy">
        <h2>Cara bayar Virtual Account</h2>
        <p>Panduan resmi MIT Muhammadiyah Sukarame untuk pembayaran via mobile banking, ATM bank lain, e-wallet, atau Muamalat DIN.</p>
        <ul class="guide-tips">
          <li>Bank tujuan: <b>Muamalat</b></li>
          <li>Prefix VA: <b>777726</b></li>
          <li>Tambah biaya: <b>Rp 2.000</b></li>
        </ul>
        <div class="guide-actions">
          <button type="button" class="btn-guide" onclick="openPanduanModal()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Lihat panduan
          </button>
          <a class="btn-guide-ghost" href="{{ asset('panduan.png') }}" target="_blank" rel="noopener">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Buka tab baru
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="footer">© {{ date('Y') }} MIT Muhammadiyah Sukarame — Kota Bandar Lampung, Prov. Lampung. All rights reserved.</div>
</div>

<div id="detailModal" class="modal-bg">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Detail tagihan</h3>
      <button class="modal-x" onclick="closeDetailModal()" aria-label="Tutup" type="button">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="modal-row"><span class="modal-row-lbl">Nama tagihan</span><span class="modal-row-val" id="mNama"></span></div>
      <div class="modal-row" id="mTahunRow" hidden><span class="modal-row-lbl">Tahun akademik</span><span class="modal-row-val" id="mTahun"></span></div>
      <div class="modal-row"><span class="modal-row-lbl">Periode</span><span class="modal-row-val" id="mPeriode"></span></div>
      <div class="modal-row"><span class="modal-row-lbl">Tgl bayar</span><span class="modal-row-val" id="mPaidDt"></span></div>
      <div id="mDetailTable"></div>
    </div>
    <div class="modal-foot">
      <button class="btn-close-full" onclick="closeDetailModal()" type="button">Tutup</button>
    </div>
  </div>
</div>

@unless($viewOnly)
<div id="paymentModal" class="modal-bg">
  <div class="modal-box pay-modal">
    <div class="modal-head">
      <h3>Bayar Tagihan</h3>
      <button class="modal-x" onclick="closePaymentModal()" aria-label="Tutup" type="button">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body" id="paymentBody"></div>
    <div class="modal-foot" id="paymentFoot"></div>
  </div>
</div>
@endunless

<div id="installModal" class="modal-bg">
  <div class="modal-box" style="max-width:420px">
    <div class="modal-head">
      <h3>Install aplikasi</h3>
      <button class="modal-x" onclick="closeInstallModal()" aria-label="Tutup" type="button">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="install-hero">
        <img src="{{ asset('mit.png') }}" alt="MIT Muhammadiyah Sukarame">
        <h4>MIT Muhammadiyah Sukarame</h4>
        <p id="installModalText">Pasang aplikasi ke perangkat Anda agar lebih cepat dibuka dan mudah diakses.</p>
      </div>
    </div>
    <div class="modal-foot" style="display:flex;flex-direction:column;gap:8px">
      <button type="button" class="btn-install-now" id="btnInstallNow">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Install sekarang
      </button>
      <button class="btn-close-full" onclick="closeInstallModal()" type="button">Nanti saja</button>
    </div>
  </div>
</div>

<div id="panduanModal" class="modal-bg" onclick="if(event.target===this)closePanduanModal()">
  <div class="modal-box panduan-modal" role="dialog" aria-modal="true" aria-labelledby="panduanModalTitle">
    <div class="modal-head">
      <h3 id="panduanModalTitle">Panduan pembayaran VA</h3>
      <button class="modal-x" onclick="closePanduanModal()" aria-label="Tutup" type="button">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="panduan-body">
      <div class="panduan-frame">
        <img src="{{ asset('panduan.png') }}" alt="Panduan pembayaran Virtual Account MIT Muhammadiyah Sukarame" loading="lazy" decoding="async">
      </div>
      <p class="panduan-note">Pastikan menambah biaya admin <b>Rp 2.000</b> pada nominal tagihan. VA Open Payment: prefix <b>777726</b>.</p>
    </div>
    <div class="modal-foot" style="display:flex;flex-wrap:wrap;gap:8px">
      <a class="btn-guide" style="flex:1;text-decoration:none" href="{{ asset('panduan.png') }}" target="_blank" rel="noopener">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        Buka penuh
      </a>
      <button class="btn-close-full" style="flex:1" onclick="closePanduanModal()" type="button">Tutup</button>
    </div>
  </div>
</div>

@if(isset($result) && !empty($result['status']))
@php
  $activeNoCust = \App\Http\Controllers\TagihanController::normalizeVa($result['data']['no_cust'] ?? ($va ?? ''));
  $multiAccounts = collect($multiAccounts ?? []);
  if ($multiAccounts->isEmpty()) {
    $multiAccounts = collect([[
      'no_cust' => $activeNoCust,
      'va_display' => $va ?? ($result['data']['va_number'] ?? $activeNoCust),
      'nama' => $result['data']['nama'] ?? '-',
      'kelas' => $result['data']['kelas'] ?? '-',
      'jenjang' => $result['data']['jenjang'] ?? '-',
      'is_active' => true,
    ]]);
  }
@endphp
<div id="multiAkunModal" class="modal-bg">
  <div class="modal-box">
    <div class="modal-head">
      <h3>Multi akun</h3>
      <button class="modal-x" onclick="closeMultiAkunModal()" aria-label="Tutup" type="button">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <p class="ma-section-label">Daftar akun</p>
      <div id="maError" class="ma-error"></div>
      <div id="maSuccess" class="ma-success"></div>
      <div class="ma-list" id="maList">
        @foreach($multiAccounts as $acc)
          @php $isActive = !empty($acc['is_active']); @endphp
          <div
            class="ma-item {{ $isActive ? 'is-active' : '' }}"
            data-no-cust="{{ $acc['no_cust'] ?? '' }}"
            @if($isActive) aria-current="true" @endif
          >
            <div class="ma-item-main">
              <p class="ma-item-name">{{ $acc['nama'] ?? '-' }}</p>
              <p class="ma-item-meta">{{ $acc['kelas'] ?? '-' }} · VA {{ $acc['va_display'] ?? ($acc['no_cust'] ?? '-') }}</p>
            </div>
            <div class="ma-item-right">
              <span class="badge {{ $isActive ? 'badge-active' : 'badge-inactive' }}">{{ $isActive ? 'Aktif' : 'Nonaktif' }}</span>
              @if(count($multiAccounts) > 1)
              <button type="button" class="ma-del" data-hapus-no-cust="{{ $acc['no_cust'] ?? '' }}" title="Hapus dari multi akun" aria-label="Hapus dari multi akun">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      <div class="divider" style="margin:1rem 0"></div>
      <p class="ma-section-label">Tambah akun</p>
      <form id="multiAkunForm" onsubmit="return submitTambahMultiAkun(event)">
        @csrf
        <div class="field">
          <label>Nomor VA <em>*</em></label>
          <input type="text" name="no_cust" id="maNoCust" placeholder="777726xxxxxxxxxx" required autocomplete="username">
        </div>
        <div class="field">
          <label>Password <em>*</em></label>
          <div class="pw-wrap">
            <input type="password" name="password" id="maPassword" placeholder="Masukkan password" required autocomplete="current-password">
            <button type="button" class="pw-toggle" id="maTogglePassword" aria-label="Tampilkan password">
              <svg id="maIconEye" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              <svg id="maIconEyeOff" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" hidden><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
            </button>
          </div>
        </div>
        <input type="hidden" name="academic_year" id="maAcademicYear" value="all">
        <button type="submit" class="submit-btn" id="maSubmitBtn">
          <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
          Tambah akun
        </button>
      </form>
    </div>
    <div class="modal-foot">
      <button class="btn-close-full" onclick="closeMultiAkunModal()" type="button">Tutup</button>
    </div>
  </div>
</div>

<form id="multiAkunSwitchForm" method="POST" action="{{ route('multi-akun.switch') }}" style="display:none">
  @csrf
  <input type="hidden" name="no_cust" id="maSwitchNoCust">
  <input type="hidden" name="academic_year" id="maSwitchYear" value="all">
</form>
@endif

<form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none">
  @csrf
</form>

<button class="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Scroll ke atas" type="button">
  <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
</button>

<script>
let currentTheme = (typeof localStorage !== 'undefined' && localStorage.getItem('theme') === 'dark') ? 'dark' : 'light';
let turnstileEnabled = @json((bool) config('services.turnstile.enabled'));
let turnstileToken = turnstileEnabled ? null : 'bypass';
let tagihanPage = 1, tagihanPerPageVal = 10, tagihanAll = false;
let lunasPage = 1, lunasPerPageVal = 10, lunasAll = false;

const ICON_MOON = '<path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
const ICON_SUN = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';

function syncThemeUI(theme) {
  currentTheme = theme;
  const lbl = document.getElementById('themeLabel');
  const icon = document.getElementById('themeIcon');
  if (theme === 'dark') {
    lbl.textContent = 'Mode terang';
    icon.innerHTML = ICON_SUN;
  } else {
    lbl.textContent = 'Mode gelap';
    icon.innerHTML = ICON_MOON;
  }
}

function applyTheme(theme, resetWidget) {
  document.documentElement.classList.toggle('dark', theme === 'dark');
  localStorage.setItem('theme', theme);
  syncThemeUI(theme);
  if (resetWidget) resetTurnstile();
}

function toggleTheme() {
  applyTheme(currentTheme === 'light' ? 'dark' : 'light', true);
}

function initTurnstile() {
  if (!turnstileEnabled) return;
  const w = document.getElementById('turnstile-widget');
  if (!w) return;
  w.innerHTML = '';
  turnstileToken = null;
  const tokenEl = document.getElementById('cfToken');
  if (tokenEl) tokenEl.value = '';
  if (typeof turnstile === 'undefined') { setTimeout(initTurnstile, 150); return; }
  turnstile.render('#turnstile-widget', {
    sitekey: @json(config('services.turnstile.site_key')),
    theme: currentTheme === 'dark' ? 'dark' : 'light',
    size: window.innerWidth < 420 ? 'compact' : 'normal',
    retry: 'auto',
    callback: t => { turnstileToken = t; const el = document.getElementById('cfToken'); if (el) el.value = t; },
    'error-callback': () => { turnstileToken = null; const el = document.getElementById('cfToken'); if (el) el.value = ''; },
    'expired-callback': () => { turnstileToken = null; const el = document.getElementById('cfToken'); if (el) el.value = ''; }
  });
}

function resetTurnstile() {
  if (!turnstileEnabled) return;
  if (typeof turnstile !== 'undefined') {
    const w = document.getElementById('turnstile-widget');
    if (w) w.innerHTML = '';
    setTimeout(initTurnstile, 100);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  syncThemeUI(currentTheme);
  document.documentElement.classList.toggle('dark', currentTheme === 'dark');

  const togglePw = document.getElementById('togglePassword');
  if (togglePw) {
    togglePw.addEventListener('click', () => {
      const field = document.getElementById('password');
      const eye = document.getElementById('iconEye');
      const eyeOff = document.getElementById('iconEyeOff');
      const show = field.getAttribute('type') === 'password';
      field.setAttribute('type', show ? 'text' : 'password');
      togglePw.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      if (eye) eye.hidden = show;
      if (eyeOff) eyeOff.hidden = !show;
    });
  }

  fillYearSelects();
  initTagihanPagination();
  initLunasPagination();
});

const preselectedYear = @json($academic_year ?? 'all');
let selectedBillYear = preselectedYear || 'all';

function collectBillYears() {
  const years = new Set();
  document.querySelectorAll('[data-tahun]').forEach(el => {
    const y = String(el.getAttribute('data-tahun') || '').trim();
    if (y && y !== '-' && y.toLowerCase() !== 'all') years.add(y);
  });
  return Array.from(years).sort().reverse();
}

function fillYearSelects() {
  const years = collectBillYears();
  const s = document.getElementById('billYearFilter');
  if (!s) return;
  const current = s.value || 'all';
  s.innerHTML = '';
  const def = document.createElement('option');
  def.value = 'all';
  def.textContent = 'Semua tahun akademik';
  s.appendChild(def);
  years.forEach(y => {
    const o = document.createElement('option');
    o.value = y;
    o.textContent = y;
    s.appendChild(o);
  });
  const match = Array.from(s.options).find(o => o.value === current);
  s.value = match ? current : 'all';
  selectedBillYear = s.value || 'all';
  applyBillYearFilter(false);
}

function onBillYearChange(sel) {
  selectedBillYear = sel.value || 'all';
  applyBillYearFilter(true);
}

function applyBillYearFilter(resetPage) {
  if (resetPage) { tagihanPage = 1; lunasPage = 1; }
  const label = (!selectedBillYear || selectedBillYear === 'all') ? 'Semua tahun akademik' : selectedBillYear;
  const t1 = document.getElementById('tagihanTitle');
  const t2 = document.getElementById('lunasTitle');
  if (t1) t1.textContent = 'Tagihan aktif — ' + label;
  if (t2) t2.textContent = 'Tagihan lunas — ' + label;
  initTagihanPagination();
  initLunasPagination();
}

window.onload = initTurnstile;

function yearOf(el) {
  return String(el.getAttribute('data-tahun') || '').trim();
}

function hideAllBillItems(tableBodyId) {
  const el = document.getElementById(tableBodyId);
  if (el) el.querySelectorAll('tr[data-index]').forEach(r => { r.style.display = 'none'; });
  const cardList = document.getElementById(tableBodyId.replace('TableBody', 'CardList'));
  if (cardList) cardList.querySelectorAll('[data-index]').forEach(c => { c.style.display = 'none'; });
}

function getRows(id) {
  const el = document.getElementById(id);
  if (!el) return [];
  return Array.from(el.querySelectorAll('tr[data-index]')).filter(r => {
    if (!selectedBillYear || selectedBillYear === 'all') return true;
    return yearOf(r) === String(selectedBillYear).trim();
  });
}

function syncCards(tableBodyId, rows) {
  const cardList = document.getElementById(tableBodyId.replace('TableBody', 'CardList'));
  if (!cardList) return;
  const vis = {};
  rows.forEach(r => { vis[r.dataset.index] = r.style.display; });
  cardList.querySelectorAll('[data-index]').forEach(c => {
    const matchYear = !selectedBillYear || selectedBillYear === 'all' || yearOf(c) === String(selectedBillYear).trim();
    c.style.display = matchYear && Object.prototype.hasOwnProperty.call(vis, c.dataset.index) ? vis[c.dataset.index] : 'none';
  });
}

function paginateRows(rows, page, perPage, showAll, infoId, paginationFn, tableBodyId) {
  if (tableBodyId) hideAllBillItems(tableBodyId);
  const total = rows.length;
  rows.forEach(r => { r.style.display = 'none'; });
  if (showAll) { rows.forEach(r => { r.style.display = ''; }); }
  else {
    const start = (page - 1) * perPage;
    for (let i = start; i < Math.min(start + perPage, total); i++) rows[i].style.display = '';
  }
  if (tableBodyId) syncCards(tableBodyId, rows);
  const s2 = total === 0 ? 0 : (showAll ? 1 : (page - 1) * perPage + 1);
  const e2 = showAll ? total : Math.min(page * perPage, total);
  const inf = document.getElementById(infoId);
  if (inf) inf.textContent = `Menampilkan ${s2} – ${e2} dari ${total} data`;
  paginationFn(showAll ? 1 : Math.ceil(total / perPage || 0), total);
}

function renderPagination(id, totalPages, totalRows, showAll, getCurrent, goTo) {
  const el = document.getElementById(id);
  if (!el) return;
  if (showAll || totalRows === 0) { el.innerHTML = ''; return; }
  const cur = getCurrent();
  let h = '';
  if (cur > 1) h += `<button class="pg-btn" type="button" onclick="${goTo}(${cur-1})">‹</button>`;
  const max = 5;
  let sp = Math.max(1, cur - Math.floor(max/2));
  let ep = Math.min(totalPages, sp + max - 1);
  if (ep - sp < max - 1) sp = Math.max(1, ep - max + 1);
  if (sp > 1) { h += `<button class="pg-btn" type="button" onclick="${goTo}(1)">1</button>`; if (sp > 2) h += `<span style="padding:5px 4px;color:var(--text3)">…</span>`; }
  for (let i = sp; i <= ep; i++) h += `<button class="pg-btn${i === cur ? ' pg-active' : ''}" type="button" onclick="${goTo}(${i})">${i}</button>`;
  if (ep < totalPages) { if (ep < totalPages - 1) h += `<span style="padding:5px 4px;color:var(--text3)">…</span>`; h += `<button class="pg-btn" type="button" onclick="${goTo}(${totalPages})">${totalPages}</button>`; }
  if (cur < totalPages) h += `<button class="pg-btn" type="button" onclick="${goTo}(${cur+1})">›</button>`;
  el.innerHTML = h;
}

function initTagihanPagination() {
  hideAllBillItems('tagihanTableBody');
  const rows = getRows('tagihanTableBody');
  paginateRows(rows, tagihanPage, tagihanPerPageVal, tagihanAll, 'tagihanInfo', (tp, tr) => renderPagination('tagihanPagination', tp, tr, tagihanAll, () => tagihanPage, 'goToTagihanPage'), 'tagihanTableBody');
}

function initLunasPagination() {
  hideAllBillItems('lunasTableBody');
  const rows = getRows('lunasTableBody');
  paginateRows(rows, lunasPage, lunasPerPageVal, lunasAll, 'lunasInfo', (tp, tr) => renderPagination('lunasPagination', tp, tr, lunasAll, () => lunasPage, 'goToLunasPage'), 'lunasTableBody');
}

function goToTagihanPage(p) { tagihanPage = p; paginateRows(getRows('tagihanTableBody'), tagihanPage, tagihanPerPageVal, tagihanAll, 'tagihanInfo', (tp, tr) => renderPagination('tagihanPagination', tp, tr, tagihanAll, () => tagihanPage, 'goToTagihanPage'), 'tagihanTableBody'); }
function goToLunasPage(p) { lunasPage = p; paginateRows(getRows('lunasTableBody'), lunasPage, lunasPerPageVal, lunasAll, 'lunasInfo', (tp, tr) => renderPagination('lunasPagination', tp, tr, lunasAll, () => lunasPage, 'goToLunasPage'), 'lunasTableBody'); }

function changeTagihanPerPage() { tagihanPerPageVal = parseInt(document.getElementById('tagihanPerPage').value); tagihanPage = 1; tagihanAll = false; initTagihanPagination(); }
function changeLunasPerPage() { lunasPerPageVal = parseInt(document.getElementById('lunasPerPage').value); lunasPage = 1; lunasAll = false; initLunasPagination(); }
function showAllTagihan() { tagihanAll = true; initTagihanPagination(); }
function showAllLunas() { lunasAll = true; initLunasPagination(); }

function formatPeriode(value) {
  const raw = String(value || '').trim();
  if (!raw || raw === '-') return '-';
  const m = raw.match(/^(\d{4})[-]?(\d{2})/);
  if (m) {
    const names = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    const bulan = parseInt(m[2], 10);
    if (bulan >= 1 && bulan <= 12) return names[bulan - 1] + ' ' + m[1];
  }
  return raw;
}

function formatExpDate(value) {
  if (!value || String(value).indexOf('0000-00-00') === 0) return '-';
  const d = new Date(value);
  if (isNaN(d.getTime())) {
    const raw = String(value);
    return raw.length >= 10 ? raw.slice(0, 10) : raw;
  }
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return y + '-' + m + '-' + day;
}

function showLunasDetail(index) {
  const tahunRow = document.getElementById('mTahunRow');
  if (tahunRow) tahunRow.hidden = true;
  showDetailModal(tagihanLunas[index] || {});
}

function showDetailModal(tagihan) {
  document.getElementById('mNama').textContent = tagihan.nama_tagihan ? tagihan.nama_tagihan.toLowerCase().replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
  document.getElementById('mTahun').textContent = tagihan.tahun_akademik_tagihan || '-';
  const periodeEl = document.getElementById('mPeriode');
  if (periodeEl) periodeEl.textContent = tagihan.periode || '-';
  const paidEl = document.getElementById('mPaidDt');
  if (paidEl) paidEl.textContent = formatExpDate(tagihan.PAIDDT || tagihan.paiddt || null);
  let details = Array.isArray(tagihan.detail) ? tagihan.detail.slice() : [];
  if (!details.length && (tagihan.PAIDDT || tagihan.PAIDST === '1' || tagihan.paidst === '1')) {
    details = [{
      sumber: 'tran',
      trxdate: tagihan.PAIDDT || tagihan.paiddt || '-',
      metode: 'Lunas',
      noreff: tagihan.periode || '-',
      akun_detail: tagihan.nama_tagihan || 'Pembayaran',
      nominal_detail: tagihan.total_tagihan || tagihan.sudah_dibayar || 0
    }];
  }
  const isTran = details.some(d => d.sumber === 'tran' || d.trxdate || d.TRXDATE);
  const th = 'padding:9px 12px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.05em;color:var(--text3);text-transform:uppercase;border-bottom:1px solid var(--border)';
  const td = 'padding:9px 12px;border-bottom:1px solid var(--border);font-size:13px;color:var(--text)';
  let t = '<table style="width:100%;border-collapse:collapse;margin-top:.75rem"><thead><tr style="background:var(--surface2)">';
  if (isTran) {
    t += `<th style="${th}">Tanggal</th><th style="${th}">Metode</th><th style="${th}">Ref</th><th style="${th};text-align:right">Nominal</th></tr></thead><tbody>`;
    if (details.length) {
      details.forEach(d => {
        t += `<tr>
          <td style="${td}">${esc(formatExpDate(d.trxdate || d.TRXDATE))}</td>
          <td style="${td}">${esc(d.metode || d.akun_detail || '-')}</td>
          <td style="${td}">${esc(d.noreff || d.transno || '-')}</td>
          <td style="${td};text-align:right;font-weight:650">Rp ${parseInt(d.nominal_detail||0).toLocaleString('id-ID')}</td>
        </tr>`;
      });
    } else {
      t += `<tr><td colspan="4" style="padding:1.5rem;text-align:center;color:var(--text3);font-size:13px">Tidak ada rincian</td></tr>`;
    }
  } else {
    t += `<th style="${th}">Komponen</th><th style="${th};text-align:right">Nominal</th></tr></thead><tbody>`;
    if (details.length) {
      details.forEach(d => {
        t += `<tr><td style="${td}">${esc(d.akun_detail||'-')}</td><td style="${td};text-align:right;font-weight:650">Rp ${parseInt(d.nominal_detail||0).toLocaleString('id-ID')}</td></tr>`;
      });
    } else {
      t += `<tr><td colspan="2" style="padding:1.5rem;text-align:center;color:var(--text3);font-size:13px">Tidak ada rincian</td></tr>`;
    }
  }
  t += '</tbody></table>';
  document.getElementById('mDetailTable').innerHTML = t;
  document.getElementById('detailModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDetailModal() {
  document.getElementById('detailModal').classList.remove('open');
  document.body.style.overflow = '';
}

window.addEventListener('click', e => {
  if (e.target.id === 'detailModal') closeDetailModal();
  if (e.target.id === 'paymentModal') closePaymentModal();
  if (e.target.id === 'multiAkunModal') closeMultiAkunModal();
  if (e.target.id === 'installModal') closeInstallModal();
});

document.getElementById('billForm').addEventListener('submit', e => {
  if (turnstileEnabled && !turnstileToken) { e.preventDefault(); alert('Selesaikan verifikasi keamanan terlebih dahulu!'); }
});

const tagihanAktif = @json(isset($result['data']['tagihan']) ? $result['data']['tagihan'] : []);
const tagihanLunas = @json(isset($result['data']['tagihan_lunas']) ? $result['data']['tagihan_lunas'] : []);
const siswaBayar = {
  id: @json($result['data']['id'] ?? null),
  nama: @json($result['data']['nama'] ?? ''),
  kelas: @json($result['data']['kelas'] ?? ''),
  no_cust: @json($result['data']['no_cust'] ?? ''),
  num2nd: @json($result['data']['num2nd'] ?? ''),
  va_number: @json($result['data']['va_number'] ?? ''),
  tahun_akademik: @json($academic_year ?? ''),
  jenjang: @json($result['data']['jenjang'] ?? ''),
  saldo: @json($result['data']['saldo'] ?? 0)
};
const viewOnly = @json($viewOnly);
const generateVaUrl = @json(route('generate-va'));
const multiAkunTambahUrl = @json(route('multi-akun.tambah'));
const multiAkunHapusUrl = @json(route('multi-akun.hapus'));
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let multiAkunAccounts = @json(isset($result) && !empty($result['status']) ? ($multiAccounts ?? []) : []);

function openMultiAkunModal() {
  const modal = document.getElementById('multiAkunModal');
  if (!modal) return;
  clearMaMessages();
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  const yearEl = document.getElementById('maAcademicYear');
  if (yearEl) yearEl.value = 'all';
}

function closeMultiAkunModal() {
  const modal = document.getElementById('multiAkunModal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
}

function clearMaMessages() {
  const err = document.getElementById('maError');
  const ok = document.getElementById('maSuccess');
  if (err) { err.textContent = ''; err.classList.remove('open'); }
  if (ok) { ok.textContent = ''; ok.classList.remove('open'); }
}

function showMaError(msg) {
  const err = document.getElementById('maError');
  const ok = document.getElementById('maSuccess');
  if (ok) ok.classList.remove('open');
  if (!err) return;
  err.textContent = msg || 'Terjadi kesalahan';
  err.classList.add('open');
}

function showMaSuccess(msg) {
  const err = document.getElementById('maError');
  const ok = document.getElementById('maSuccess');
  if (err) err.classList.remove('open');
  if (!ok) return;
  ok.textContent = msg || 'Berhasil';
  ok.classList.add('open');
}

function renderMultiAkunList(accounts) {
  const list = document.getElementById('maList');
  if (!list) return;
  multiAkunAccounts = Array.isArray(accounts) ? accounts : [];
  if (!multiAkunAccounts.length) {
    list.innerHTML = '<div class="empty-note">Belum ada akun terhubung</div>';
    return;
  }
  const showDelete = multiAkunAccounts.length > 1;
  list.innerHTML = multiAkunAccounts.map(acc => {
    const active = !!acc.is_active;
    const nama = esc(acc.nama || '-');
    const kelas = esc(acc.kelas || '-');
    const va = esc(acc.va_display || acc.no_cust || '-');
    const noCust = esc(String(acc.no_cust || ''));
    const delBtn = showDelete
      ? `<button type="button" class="ma-del" data-hapus-no-cust="${noCust}" title="Hapus dari multi akun" aria-label="Hapus dari multi akun">
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>`
      : '';
    return `
      <div class="ma-item ${active ? 'is-active' : ''}" data-no-cust="${noCust}" ${active ? 'aria-current="true"' : ''}>
        <div class="ma-item-main">
          <p class="ma-item-name">${nama}</p>
          <p class="ma-item-meta">${kelas} · VA ${va}</p>
        </div>
        <div class="ma-item-right">
          <span class="badge ${active ? 'badge-active' : 'badge-inactive'}">${active ? 'Aktif' : 'Nonaktif'}</span>
          ${delBtn}
        </div>
      </div>`;
  }).join('');
}

function logoutAkun() {
  try { sessionStorage.clear(); } catch (e) {}
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const form = document.getElementById('logoutForm');
  const goHome = () => window.location.replace('/');
  fetch(@json(url('/logout')), {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'text/html'
    },
    credentials: 'same-origin',
    redirect: 'follow'
  }).then(goHome).catch(() => {
    if (form) form.submit();
    else goHome();
  });
}

function switchMultiAkun(noCust) {
  const form = document.getElementById('multiAkunSwitchForm');
  const input = document.getElementById('maSwitchNoCust');
  const year = document.getElementById('maSwitchYear');
  if (!form || !input || !noCust) {
    showMaError('Form pindah akun tidak tersedia. Silakan cek tagihan ulang.');
    return;
  }
  input.value = noCust;
  if (year) year.value = 'all';
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      title: 'Beralih akun...',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });
  }
  form.submit();
}

async function hapusMultiAkun(noCust, namaHint) {
  if (!noCust) return;
  clearMaMessages();
  const label = namaHint || noCust;
  if (typeof Swal !== 'undefined') {
    const conf = await Swal.fire({
      icon: 'warning',
      title: 'Hapus dari multi akun?',
      html: `Akun <b>${esc(label)}</b> akan diputus dari koneksi multi akun.`,
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc2626',
      ...swalTheme()
    });
    if (!conf.isConfirmed) return;
  } else if (!confirm('Hapus akun dari multi akun?')) {
    return;
  }

  try {
    const res = await fetch(multiAkunHapusUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ no_cust: noCust })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.status) {
      showMaError(data.message || 'Gagal menghapus akun');
      return;
    }
    renderMultiAkunList(data.data?.accounts || []);
    showMaSuccess(data.message || 'Akun berhasil dihapus dari multi akun');
  } catch (err) {
    showMaError('Terjadi kesalahan jaringan');
  }
}

document.addEventListener('click', (e) => {
  const del = e.target.closest('#maList .ma-del');
  if (del) {
    e.preventDefault();
    e.stopPropagation();
    const noCust = del.getAttribute('data-hapus-no-cust');
    const row = del.closest('.ma-item');
    const nama = row?.querySelector('.ma-item-name')?.textContent || noCust;
    hapusMultiAkun(noCust, nama);
    return;
  }

  const item = e.target.closest('#maList .ma-item');
  if (!item || item.classList.contains('is-active')) return;
  const noCust = item.getAttribute('data-no-cust');
  if (!noCust) return;
  e.preventDefault();
  switchMultiAkun(noCust);
});

async function submitTambahMultiAkun(e) {
  e.preventDefault();
  clearMaMessages();
  const btn = document.getElementById('maSubmitBtn');
  const noCust = document.getElementById('maNoCust')?.value?.trim();
  const password = document.getElementById('maPassword')?.value || '';
  const academicYear = 'all';
  if (!noCust || !password) {
    showMaError('Lengkapi VA dan password');
    return false;
  }
  if (btn) {
    btn.disabled = true;
    btn.style.opacity = '.7';
  }
  try {
    const res = await fetch(multiAkunTambahUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        no_cust: noCust,
        password: password,
        academic_year: academicYear
      })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok || !data.status) {
      showMaError(data.message || 'Gagal menambahkan akun');
      return false;
    }
    renderMultiAkunList(data.data?.accounts || []);
    document.getElementById('maNoCust').value = '';
    document.getElementById('maPassword').value = '';
    showMaSuccess(data.message || 'Akun berhasil ditambahkan');
  } catch (err) {
    showMaError('Terjadi kesalahan jaringan');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.style.opacity = '';
    }
  }
  return false;
}

document.addEventListener('DOMContentLoaded', () => {
  const toggleMaPw = document.getElementById('maTogglePassword');
  if (toggleMaPw) {
    toggleMaPw.addEventListener('click', () => {
      const field = document.getElementById('maPassword');
      const eye = document.getElementById('maIconEye');
      const eyeOff = document.getElementById('maIconEyeOff');
      if (!field) return;
      const show = field.getAttribute('type') === 'password';
      field.setAttribute('type', show ? 'text' : 'password');
      toggleMaPw.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      if (eye) eye.hidden = show;
      if (eyeOff) eyeOff.hidden = !show;
    });
  }
});

function formatRp(n) {
  return 'Rp ' + (parseInt(n, 10) || 0).toLocaleString('id-ID');
}

function formatNovaDisplay(va) {
  let n = String(va ?? '').replace(/\D+/g, '');
  if (!n) return '-';
  const prefixes = ['777726', '797777', '757777', '797766', '751000', '797783', '77726', '96240'];
  for (let i = 0; i < 4; i++) {
    let hit = false;
    for (const p of prefixes) {
      if (n.startsWith(p) && n.length > p.length) {
        n = n.slice(p.length);
        hit = true;
        break;
      }
    }
    if (!hit) break;
  }
  n = n.replace(/^0+/, '') || n;
  return n ? ('777726' + n.padStart(10, '0')) : '-';
}

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function billName(name) {
  return esc((name || '-').toLowerCase().replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
}

function isCicil(item) {
  return String(item?.isINSTALLABLE ?? item?.isinstallable ?? item?.is_installment ?? 0) === '1';
}

function sudahBayarOf(item) {
  return parseInt(item?.sudah_dibayar, 10) || 0;
}

function sisaTagihan(item) {
  if (item?.sisa_tagihan != null && item.sisa_tagihan !== '') {
    return Math.max(0, parseInt(item.sisa_tagihan, 10) || 0);
  }
  return Math.max(0, (parseInt(item?.total_tagihan, 10) || 0) - sudahBayarOf(item));
}

function setBayarForIndex(idx, value, enabled) {
  document.querySelectorAll('.bayar-input[data-index="' + idx + '"]').forEach(inp => {
    const item = tagihanAktif[idx];
    const max = sisaTagihan(item);
    const cicil = isCicil(item);
    inp.max = max;
    if (enabled) {
      inp.disabled = !cicil;
      inp.readOnly = !cicil;
      inp.value = Math.min(max, Math.max(0, value));
    } else {
      inp.disabled = true;
      inp.readOnly = true;
      inp.value = 0;
    }
  });
}

function getBayarAmount(idx) {
  const inp = document.querySelector('.bayar-input[data-index="' + idx + '"]');
  const item = tagihanAktif[idx];
  const max = sisaTagihan(item);
  let n = inp ? (parseInt(String(inp.value).replace(/\D/g, ''), 10) || 0) : 0;
  if (n < 0) n = 0;
  if (n > max) n = max;
  if (item && !isCicil(item) && n > 0) n = max;
  return n;
}

function clampBayarInput(inp) {
  const idx = inp.dataset.index;
  const item = tagihanAktif[idx];
  const max = sisaTagihan(item);
  let n = parseInt(String(inp.value).replace(/\D/g, ''), 10) || 0;
  if (n < 1) n = 1;
  if (n > max) n = max;
  if (!isCicil(item)) n = max;
  document.querySelectorAll('.bayar-input[data-index="' + idx + '"]').forEach(el => { el.value = n; });
  updatePaySummary();
}

function getSelectedTagihan() {
  const seen = new Set();
  const selected = [];
  document.querySelectorAll('.tagihan-checkbox:checked').forEach(cb => {
    const idx = String(cb.dataset.index);
    if (seen.has(idx) || cb.disabled) return;
    seen.add(idx);
    const item = tagihanAktif[idx];
    if (!item) return;
    selected.push({ ...item, _idx: idx, bayar: getBayarAmount(idx) });
  });
  return selected;
}

function syncCheckboxPair(source) {
  document.querySelectorAll('.tagihan-checkbox[data-index="' + source.dataset.index + '"]').forEach(cb => {
    cb.checked = source.checked;
  });
  const idx = source.dataset.index;
  const item = tagihanAktif[idx];
  setBayarForIndex(idx, source.checked ? sisaTagihan(item) : 0, source.checked);
  const all = Array.from(document.querySelectorAll('#tagihanTableBody .tagihan-checkbox')).filter(cb => !cb.disabled);
  const checked = all.filter(cb => cb.checked);
  const allOn = all.length > 0 && all.length === checked.length;
  ['selectAll', 'selectAllMobile'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.checked = allOn;
  });
}

function toggleSelectAll(master) {
  document.querySelectorAll('.tagihan-checkbox').forEach(cb => {
    if (cb.disabled) return;
    cb.checked = master.checked;
    const idx = cb.dataset.index;
    const item = tagihanAktif[idx];
    setBayarForIndex(idx, master.checked ? sisaTagihan(item) : 0, master.checked);
  });
  ['selectAll', 'selectAllMobile'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.checked = master.checked;
  });
  updatePaySummary();
}

function updatePaySummary() {
  const selected = getSelectedTagihan();
  const total = selected.reduce((s, i) => s + (parseInt(i.bayar, 10) || 0), 0);
  const box = document.getElementById('paySummary');
  const text = document.getElementById('paySummaryText');
  const tot = document.getElementById('paySummaryTotal');
  if (!box) return;
  if (!selected.length) {
    box.classList.remove('open');
    return;
  }
  box.classList.add('open');
  text.textContent = selected.length + ' tagihan dipilih';
  tot.textContent = formatRp(total);
}

document.addEventListener('change', e => {
  if (e.target.classList.contains('tagihan-checkbox')) {
    syncCheckboxPair(e.target);
    updatePaySummary();
  }
});

document.addEventListener('input', e => {
  if (e.target.classList.contains('bayar-input')) clampBayarInput(e.target);
});

function swalTheme() {
  const dark = document.documentElement.classList.contains('dark');
  return {
    confirmButtonColor: '#15803d',
    background: dark ? '#15241b' : '#fff',
    color: dark ? '#eef6f0' : '#13261a'
  };
}

function showPaymentModal() {
  if (viewOnly) {
    Swal.fire({ icon: 'info', title: 'Hanya melihat tagihan', text: 'Pembayaran tidak tersedia.', ...swalTheme() });
    return;
  }
  const selected = getSelectedTagihan();
  if (!selected.length) {
    Swal.fire({ icon: 'warning', title: 'Belum ada tagihan', text: 'Pilih minimal satu tagihan untuk dibayar.', ...swalTheme() });
    return;
  }

  const missingAa = selected.some(i => !i.AA);
  if (missingAa || !siswaBayar.id) {
    Swal.fire({ icon: 'error', title: 'Data tidak lengkap', text: 'Data tagihan tidak bisa diproses. Silakan cek ulang tagihan.', ...swalTheme() });
    return;
  }

  for (const i of selected) {
    const sisa = sisaTagihan(i);
    const bayar = parseInt(i.bayar, 10) || 0;
    if (bayar <= 0) {
      Swal.fire({ icon: 'warning', title: 'Nominal belum diisi', text: 'Isi nominal bayar untuk setiap tagihan yang dipilih.', ...swalTheme() });
      return;
    }
    if (bayar > sisa) {
      Swal.fire({ icon: 'warning', title: 'Nominal melebihi sisa', text: 'Nominal bayar tidak boleh lebih dari sisa tagihan.', ...swalTheme() });
      return;
    }
    if (!isCicil(i) && bayar !== sisa) {
      Swal.fire({ icon: 'warning', title: 'Tidak bisa dicicil', text: 'Tagihan yang tidak bisa dicicil harus dibayar sesuai sisa tagihan.', ...swalTheme() });
      return;
    }
  }

  const total = selected.reduce((s, i) => s + (parseInt(i.bayar, 10) || 0), 0);
  let rows = '';
  selected.forEach(i => {
    const cicil = isCicil(i);
    const sisa = sisaTagihan(i);
    const sudah = sudahBayarOf(i);
    const readonly = cicil ? '' : 'readonly';
    rows += `<tr>
      <td>${billName(i.nama_tagihan)}</td>
      <td>${esc(i.tahun_akademik_tagihan || siswaBayar.tahun_akademik || '-')}</td>
      <td>${esc(formatPeriode(i.periode))}</td>
      <td class="cicil"><span class="badge ${cicil ? 'badge-cicil' : 'badge-no-cicil'}">${cicil ? 'Ya' : 'Tidak'}</span></td>
      <td class="num">${formatRp(i.total_tagihan)}</td>
      <td class="num">${formatRp(sudah)}</td>
      <td class="bayar-col"><input type="number" class="pay-cell bayar-input modal-bayar-input" data-index="${i._idx}" min="1" max="${sisa}" value="${i.bayar}" ${readonly} inputmode="numeric"></td>
    </tr>`;
  });

  document.getElementById('paymentBody').innerHTML = `
    <div class="pay-info">
      <div><span class="pi-lbl">Nama</span><div class="pi-val">${esc(siswaBayar.nama) || '-'}</div></div>
      <div><span class="pi-lbl">Kelas</span><div class="pi-val">${esc(siswaBayar.kelas) || '-'}</div></div>
      <div><span class="pi-lbl">NIS</span><div class="pi-val">${esc(siswaBayar.no_cust || siswaBayar.num2nd) || '-'}</div></div>
      <div><span class="pi-lbl">Nomor VA</span><div class="pi-val">${esc(formatNovaDisplay(siswaBayar.va_number || siswaBayar.no_cust)) || '-'}</div></div>
    </div>
    <div class="pay-tbl-wrap">
      <table class="pay-tbl">
        <thead>
          <tr>
            <th>Nama tagihan</th>
            <th>Tahun aka</th>
            <th>Periode</th>
            <th>Cicil</th>
            <th>Nominal</th>
            <th>Sudah dibayar</th>
            <th>Bayar</th>
          </tr>
        </thead>
        <tbody>${rows}</tbody>
      </table>
    </div>
    <div class="pay-total"><span>Total pembayaran</span><span id="modalPayTotal">${formatRp(total)}</span></div>
    <div id="vaResult"></div>`;

  document.getElementById('paymentFoot').innerHTML = `
    <div class="pay-actions" id="payActions">
      <button type="button" class="btn-ghost" onclick="closePaymentModal()">Tutup</button>
      <button type="button" class="btn-pay-confirm" id="btnBuatVa" onclick="prosesPembayaran()">+ Buat Nomor VA</button>
    </div>`;

  document.querySelectorAll('.modal-bayar-input').forEach(inp => {
    inp.addEventListener('input', () => {
      clampBayarInput(inp);
      const tot = getSelectedTagihan().reduce((s, i) => s + (parseInt(i.bayar, 10) || 0), 0);
      const el = document.getElementById('modalPayTotal');
      if (el) el.textContent = formatRp(tot);
    });
  });

  document.getElementById('paymentModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closePaymentModal() {
  const modal = document.getElementById('paymentModal');
  if (modal) modal.classList.remove('open');
  document.body.style.overflow = '';
}

async function prosesPembayaran() {
  if (viewOnly) {
    Swal.fire({ icon: 'info', title: 'Hanya melihat tagihan', text: 'Pembayaran tidak tersedia.', ...swalTheme() });
    return;
  }
  const selected = getSelectedTagihan();
  const btn = document.getElementById('btnBuatVa');
  if (!selected.length) {
    Swal.fire({ icon: 'warning', title: 'Belum ada tagihan', text: 'Pilih minimal satu tagihan untuk dibayar.', ...swalTheme() });
    return;
  }

  for (const i of selected) {
    const sisa = sisaTagihan(i);
    const bayar = parseInt(i.bayar, 10) || 0;
    if (bayar <= 0 || bayar > sisa || (!isCicil(i) && bayar !== sisa)) {
      Swal.fire({ icon: 'warning', title: 'Nominal tidak valid', text: 'Periksa nominal bayar. Yang tidak bisa dicicil harus lunas sisa tagihan, yang bisa dicicil tidak boleh melebihi sisa.', ...swalTheme() });
      return;
    }
  }

  const items = selected.map(i => ({ AA: i.AA, amount: parseInt(i.bayar, 10) }));
  const ids = items.map(i => i.AA);
  const amounts = items.map(i => i.amount);
  const total = amounts.reduce((s, n) => s + n, 0);
  const nocust = siswaBayar.no_cust || siswaBayar.num2nd || '';

  if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }

  try {
    const res = await fetch(generateVaUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({
        custid: siswaBayar.id,
        nocust: nocust,
        namacust: siswaBayar.nama,
        array_tagihan: ids.join(','),
        billam: amounts.join(','),
        total: total,
        items: items
      })
    });

    console.log('generate-va request', {
      custid: siswaBayar.id,
      nocust,
      namacust: siswaBayar.nama,
      array_tagihan: ids.join(','),
      amounts,
      total,
      siswaBayar
    });

    const data = await res.json();
    console.log('generate-va response', res.status, data);
    const rawVa = data?.data?.va_number ?? data?.va_number;
    const vaOk = rawVa !== false && rawVa !== null && rawVa !== undefined && String(rawVa) !== 'false' && String(rawVa).trim() !== '';
    const va = (data?.status && vaOk) ? formatNovaDisplay(nocust || rawVa) : '';

    if (data?.status && va && va !== '-') {
      document.getElementById('vaResult').innerHTML = `
        <div class="va-box">
          <h4>Nomor Virtual Account</h4>
          <div class="va-number" id="vaNumberText">${esc(va)}</div>
          <button type="button" class="btn-copy" onclick="copyVa()">Salin nomor VA</button>
          <p class="va-meta">Total: <b>${formatRp(total)}</b></p>
          <p class="va-help">Bayar ke nomor VA di atas (kode bank 777726 Open Payment).</p>
        </div>`;
      const actions = document.getElementById('payActions');
      if (actions) {
        actions.innerHTML = `<button type="button" class="btn-ghost" onclick="closePaymentModal()">Tutup</button>`;
      }
    } else {
      Swal.fire({ icon: 'error', title: 'Gagal membuat VA', text: data?.message || 'Silakan coba lagi.', ...swalTheme() });
      if (btn) { btn.disabled = false; btn.textContent = '+ Buat Nomor VA'; }
    }
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'Terjadi kesalahan', text: 'Gagal memproses pembayaran. Coba beberapa saat lagi.', ...swalTheme() });
    if (btn) { btn.disabled = false; btn.textContent = '+ Buat Nomor VA'; }
  }
}

function copyNova(elId) {
  const va = (document.getElementById(elId || 'novaText')?.textContent || '').replace(/\s+/g, '');
  if (!va || va === '-') return;
  const done = () => Swal.fire({ icon: 'success', title: 'Tersalin', text: 'Nomor VA sudah disalin.', timer: 1400, showConfirmButton: false, ...swalTheme() });
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(va).then(done).catch(() => fallbackCopy(va, done));
  } else {
    fallbackCopy(va, done);
  }
}

function copyVa() {
  copyNova('vaNumberText');
}

function fallbackCopy(va, done) {
  const t = document.createElement('textarea');
  t.value = va;
  document.body.appendChild(t);
  t.select();
  document.execCommand('copy');
  t.remove();
  done();
}

/* PWA */
const installBtn = document.getElementById('installBtn');
const btnInstallNow = document.getElementById('btnInstallNow');
const installModalText = document.getElementById('installModalText');
const PWA_INSTALLED_KEY = 'tagihan_rj_pwa_installed';

function isStandalonePwa() {
  return window.matchMedia('(display-mode: standalone)').matches
    || window.navigator.standalone === true;
}

function markPwaInstalled() {
  try { localStorage.setItem(PWA_INSTALLED_KEY, '1'); } catch (e) {}
  window.__pwaAlreadyInstalled = true;
}

function isPwaMarkedInstalled() {
  if (window.__pwaAlreadyInstalled) return true;
  try { return localStorage.getItem(PWA_INSTALLED_KEY) === '1'; } catch (e) { return false; }
}

function setInstallBtnInstalledUi() {
  if (!installBtn) return;
  installBtn.classList.remove('is-hidden');
  installBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg><span class="theme-label">Buka app</span>';
  installBtn.title = 'Aplikasi sudah terpasang';
  installBtn.setAttribute('aria-label', 'Buka aplikasi');
  installBtn.dataset.state = 'installed';
}

function setInstallBtnDefaultUi() {
  if (!installBtn) return;
  installBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg><span class="theme-label">Install</span>';
  installBtn.title = 'Install aplikasi';
  installBtn.setAttribute('aria-label', 'Install aplikasi');
  installBtn.dataset.state = 'install';
}

function showAlreadyInstalledModal() {
  if (installModalText) {
    installModalText.innerHTML = 'Aplikasi <b>sudah terpasang</b>. Di Chrome, klik tombol biru <b>Buka di aplikasi</b> di bilah alamat (kanan atas) untuk membukanya.';
  }
  if (btnInstallNow) {
    btnInstallNow.disabled = false;
    btnInstallNow.dataset.mode = 'close';
    btnInstallNow.textContent = 'Mengerti';
  }
}

function openInstallModal() {
  const modal = document.getElementById('installModal');
  if (!modal) return;
  syncInstallModalState();
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeInstallModal() {
  const modal = document.getElementById('installModal');
  if (!modal) return;
  modal.classList.remove('open');
  document.body.style.overflow = '';
}

function openPanduanModal() {
  const modal = document.getElementById('panduanModal');
  if (!modal) return;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closePanduanModal() {
  const modal = document.getElementById('panduanModal');
  if (!modal) return;
  modal.classList.remove('open');
  if (!document.querySelector('.modal-bg.open')) document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
  if (e.key !== 'Escape') return;
  closePanduanModal();
});

function syncInstallModalState() {
  if (!btnInstallNow) return;
  btnInstallNow.dataset.mode = 'install';
  if (isStandalonePwa()) {
    markPwaInstalled();
    if (installModalText) installModalText.textContent = 'Anda sudah membuka aplikasi dalam mode terpasang.';
    btnInstallNow.disabled = false;
    btnInstallNow.dataset.mode = 'close';
    btnInstallNow.textContent = 'Tutup';
    return;
  }
  if (isPwaMarkedInstalled() && !window.__pwaDeferredPrompt) {
    showAlreadyInstalledModal();
    setInstallBtnInstalledUi();
    return;
  }
  if (window.__pwaDeferredPrompt || window.__pwaInstallReady) {
    if (installModalText) installModalText.textContent = 'Pasang aplikasi ke perangkat Anda agar lebih cepat dibuka dan mudah diakses.';
    btnInstallNow.disabled = false;
    btnInstallNow.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Install sekarang';
  } else {
    if (installModalText) installModalText.textContent = 'Menyiapkan installer...';
    btnInstallNow.disabled = true;
    btnInstallNow.textContent = 'Menyiapkan...';
  }
}

async function runPwaInstall() {
  const promptEvent = window.__pwaDeferredPrompt;
  if (!promptEvent) {
    syncInstallModalState();
    return false;
  }
  if (btnInstallNow) {
    btnInstallNow.disabled = true;
    btnInstallNow.textContent = 'Menginstall...';
  }
  try {
    promptEvent.prompt();
    const choice = await promptEvent.userChoice;
    window.__pwaDeferredPrompt = null;
    window.__pwaInstallReady = false;
    if (choice && choice.outcome === 'accepted') {
      markPwaInstalled();
      closeInstallModal();
      setInstallBtnInstalledUi();
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Aplikasi sedang diinstall.',
          timer: 1800,
          showConfirmButton: false,
          ...swalTheme()
        });
      }
      return true;
    }
    syncInstallModalState();
    return false;
  } catch (err) {
    syncInstallModalState();
    return false;
  }
}

window.addEventListener('pwa-install-ready', () => {
  try { localStorage.removeItem(PWA_INSTALLED_KEY); } catch (e) {}
  window.__pwaAlreadyInstalled = false;
  setInstallBtnDefaultUi();
  syncInstallModalState();
});

window.addEventListener('appinstalled', () => {
  window.__pwaDeferredPrompt = null;
  window.__pwaInstallReady = false;
  markPwaInstalled();
  setInstallBtnInstalledUi();
  closeInstallModal();
});

if (installBtn) {
  installBtn.addEventListener('click', () => {
    openInstallModal();

    // Sudah terpasang: cukup arahkan ke tombol Chrome "Buka di aplikasi"
    if (isPwaMarkedInstalled() && !window.__pwaDeferredPrompt) {
      showAlreadyInstalledModal();
      return;
    }

    if (window.__pwaDeferredPrompt) {
      syncInstallModalState();
      return;
    }

    let tries = 0;
    const timer = setInterval(() => {
      tries += 1;
      if (window.__pwaDeferredPrompt) {
        syncInstallModalState();
        clearInterval(timer);
        return;
      }
      if (tries >= 16) {
        clearInterval(timer);
        // Tidak ada event install = biasanya app sudah terpasang (Chrome tampil "Buka di aplikasi")
        markPwaInstalled();
        setInstallBtnInstalledUi();
        showAlreadyInstalledModal();
      } else {
        syncInstallModalState();
      }
    }, 250);
  });
}

if (btnInstallNow) {
  btnInstallNow.addEventListener('click', () => {
    if (btnInstallNow.dataset.mode === 'close') {
      closeInstallModal();
      return;
    }
    if (btnInstallNow.dataset.mode === 'reload') {
      location.reload();
      return;
    }
    runPwaInstall();
  });
}

if (isStandalonePwa()) {
  markPwaInstalled();
  setInstallBtnInstalledUi();
}
if (isPwaMarkedInstalled() && !window.__pwaDeferredPrompt) {
  setInstallBtnInstalledUi();
}
if (navigator.getInstalledRelatedApps) {
  navigator.getInstalledRelatedApps().then((apps) => {
    if (apps && apps.length) {
      markPwaInstalled();
      setInstallBtnInstalledUi();
    }
  }).catch(() => {});
}
syncInstallModalState();
</script>
</body>
</html>

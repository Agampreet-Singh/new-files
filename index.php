<?php
/**
 * Acme Technologies — single-file website
 * Pages are routed with ?page=home|about|services|work|careers|contact
 */
session_start();

/* ---------- Config ---------- */
const SITE_NAME    = 'Acme Technologies';
const CONTACT_TO   = 'hello@acmetech.example';   // change to your inbox
const CONTACT_PHONE = '+91 98765 43210';
const CONTACT_ADDR = 'Cyber City, Gurugram, Haryana 122002';

/* ---------- Routing ---------- */
$pages = [
    'home'     => 'Home',
    'about'    => 'About',
    'services' => 'Services',
    'work'     => 'Work',
    'careers'  => 'Careers',
    'contact'  => 'Contact',
];
$page = isset($_GET['page']) ? strtolower(trim($_GET['page'])) : 'home';
if (!array_key_exists($page, $pages)) {
    http_response_code(404);
    $page = '404';
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url($p) { return '?page=' . urlencode($p); }

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/* ---------- Contact form handling (POST → Redirect → GET) ---------- */
$errors = [];
$old = ['name' => '', 'email' => '', 'company' => '', 'service' => '', 'message' => ''];

if ($page === 'contact' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) { $old[$k] = trim($_POST[$k] ?? ''); }

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errors['form'] = 'Your session expired. Please try again.';
    }
    if (!empty($_POST['website'])) {               // honeypot
        $errors['form'] = 'Submission rejected.';
    }
    if ($old['name'] === '' || mb_strlen($old['name']) < 2)       $errors['name'] = 'Please enter your name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL))        $errors['email'] = 'Please enter a valid email address.';
    if (mb_strlen($old['message']) < 10)                          $errors['message'] = 'Tell us a little more (at least 10 characters).';
    if (mb_strlen($old['message']) > 3000)                        $errors['message'] = 'Message is too long (max 3000 characters).';

    if (!$errors) {
        $subject = 'New enquiry from ' . preg_replace('/[\r\n]+/', ' ', $old['name']);
        $body = "Name: {$old['name']}\nEmail: {$old['email']}\nCompany: {$old['company']}\n"
              . "Service: {$old['service']}\n\nMessage:\n{$old['message']}\n";
        $headers = 'Reply-To: ' . preg_replace('/[\r\n]+/', '', $old['email']);
        @mail(CONTACT_TO, $subject, $body, $headers);

        $_SESSION['flash'] = 'Thanks, ' . $old['name'] . '! Your message is in. We usually reply within one business day.';
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Location: ' . url('contact') . '#form');
        exit;
    }
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ---------- Content ---------- */
$services = [
    ['id' => 'cloud',    'icon' => 'cloud',   'title' => 'Cloud & DevOps',          'text' => 'Migration, architecture and 24/7 operations on AWS, Azure and GCP with infrastructure-as-code and CI/CD pipelines.',          'points' => ['Cloud migration & modernisation', 'Kubernetes & containers', 'Cost optimisation (FinOps)']],
    ['id' => 'security', 'icon' => 'shield',  'title' => 'Cybersecurity',           'text' => 'Protect what matters with assessments, managed detection & response, and compliance readiness for ISO 27001 and SOC 2.',      'points' => ['VAPT & security audits', 'Managed SOC / MDR', 'Compliance & policy']],
    ['id' => 'software', 'icon' => 'code',    'title' => 'Custom Software',         'text' => 'Web, mobile and enterprise applications engineered for scale — from discovery and UX to launch and long-term support.',       'points' => ['Web & mobile apps', 'API & integrations', 'Legacy modernisation']],
    ['id' => 'ai',       'icon' => 'cpu',     'title' => 'Data & AI',               'text' => 'Turn data into decisions with modern data platforms, dashboards, and practical AI that automates real work.',                 'points' => ['Data engineering', 'BI & analytics', 'AI assistants & automation']],
    ['id' => 'infra',    'icon' => 'server',  'title' => 'Managed IT & Networks',   'text' => 'Reliable networks, endpoints and servers, monitored around the clock so your team never has to think about IT.',               'points' => ['Network design & SD-WAN', 'Endpoint management', 'Backup & disaster recovery']],
    ['id' => 'support',  'icon' => 'headset', 'title' => 'IT Support & Helpdesk',   'text' => 'Friendly, fast support for your people — remote and on-site, with clear SLAs and a single number to call.',                   'points' => ['24/7 helpdesk', 'On-site engineers', 'SLA-backed response']],
];

$stats = [
    ['n' => 12,  'suffix' => '+', 'label' => 'Years in business'],
    ['n' => 450, 'suffix' => '+', 'label' => 'Projects delivered'],
    ['n' => 180, 'suffix' => '',  'label' => 'Engineers & specialists'],
    ['n' => 99,  'suffix' => '%', 'label' => 'Client retention'],
];

$projects = [
    ['cat' => 'cloud',    'title' => 'Retail platform on Kubernetes', 'client' => 'Nova Retail',      'result' => '62% lower hosting cost',   'grad' => '#6366f1,#22d3ee'],
    ['cat' => 'software', 'title' => 'Patient booking app',          'client' => 'CareWell Clinics',  'result' => '1.2M bookings in year one', 'grad' => '#ec4899,#8b5cf6'],
    ['cat' => 'security', 'title' => 'SOC 2 readiness programme',     'client' => 'FinEdge Payments',  'result' => 'Certified in 14 weeks',    'grad' => '#f59e0b,#ef4444'],
    ['cat' => 'ai',       'title' => 'Demand forecasting engine',     'client' => 'AgroFresh',         'result' => '31% less inventory waste', 'grad' => '#10b981,#06b6d4'],
    ['cat' => 'cloud',    'title' => 'Zero-downtime Azure migration', 'client' => 'Meridian Logistics','result' => '300 servers moved',        'grad' => '#3b82f6,#a855f7'],
    ['cat' => 'software', 'title' => 'Field service mobile suite',    'client' => 'PowerGrid Services','result' => '2× technician throughput', 'grad' => '#14b8a6,#6366f1'],
];

$testimonials = [
    ['q' => 'Acme took our creaking infrastructure and turned it into something we never worry about. Their team feels like ours.', 'n' => 'Priya Sharma', 'r' => 'CTO, Nova Retail'],
    ['q' => 'They found issues three previous audits missed and fixed them before our certification deadline. Outstanding.',          'n' => 'Daniel Brooks', 'r' => 'CISO, FinEdge Payments'],
    ['q' => 'From the first workshop to launch day, communication was clear and delivery was on time. Rare and refreshing.',           'n' => 'Ananya Rao',   'r' => 'Head of Product, CareWell'],
];

$team = [
    ['n' => 'Rohan Mehta',   'r' => 'Founder & CEO',         'i' => 'RM'],
    ['n' => 'Sara Kapoor',   'r' => 'Chief Technology Officer','i' => 'SK'],
    ['n' => 'Arjun Nair',    'r' => 'Head of Cybersecurity', 'i' => 'AN'],
    ['n' => 'Meera Iyer',    'r' => 'Director, Client Success','i' => 'MI'],
];

$jobs = [
    ['t' => 'Senior DevOps Engineer',      'l' => 'Gurugram / Hybrid', 'type' => 'Full-time', 'd' => 'Design and run cloud platforms with Terraform, Kubernetes and GitHub Actions. 5+ years experience.'],
    ['t' => 'Security Analyst (SOC L2)',   'l' => 'Gurugram',          'type' => 'Full-time', 'd' => 'Investigate alerts, hunt threats and improve detections in our 24/7 SOC. SIEM experience required.'],
    ['t' => 'Full-Stack Developer',        'l' => 'Remote (India)',    'type' => 'Full-time', 'd' => 'Build client applications with React, Node.js/PHP and PostgreSQL. 3+ years experience.'],
    ['t' => 'IT Support Engineer',         'l' => 'Gurugram',          'type' => 'Full-time', 'd' => 'Be the friendly expert for our clients — troubleshooting, onboarding and on-site visits.'],
];

$tech = ['AWS', 'Microsoft Azure', 'Google Cloud', 'Kubernetes', 'Terraform', 'Docker', 'React', 'Node.js', 'PHP', 'Python', 'PostgreSQL', 'Cisco', 'Fortinet', 'Splunk'];

function icon($name, $size = 24) {
    $p = [
        'cloud'   => '<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>',
        'shield'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
        'code'    => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'cpu'     => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 14h3M1 9h3M1 14h3"/>',
        'server'  => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><path d="M6 6h.01M6 18h.01"/>',
        'headset' => '<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>',
        'arrow'   => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'check'   => '<polyline points="20 6 9 17 4 12"/>',
        'mail'    => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'phone'   => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'map'     => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'users'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'zap'     => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'target'  => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        'heart'   => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
        'plus'    => '<path d="M12 5v14M5 12h14"/>',
        'clock'   => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    ];
    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($p[$name] ?? '').'</svg>';
}

$titles = [
    'home' => 'IT Services that move your business forward',
    'about' => 'About us', 'services' => 'Our services', 'work' => 'Our work',
    'careers' => 'Careers', 'contact' => 'Contact us', '404' => 'Page not found',
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($titles[$page]) ?> | <?= SITE_NAME ?></title>
<meta name="description" content="Acme Technologies provides cloud, cybersecurity, custom software, data & AI, managed IT and helpdesk services.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#070b1a; --bg2:#0c1226; --card:#111933; --card2:#16203f;
  --line:rgba(148,163,255,.14); --text:#e6e9f5; --muted:#9aa3c2;
  --a1:#6366f1; --a2:#22d3ee; --a3:#a855f7;
  --grad:linear-gradient(135deg,var(--a1),var(--a2));
  --radius:18px; --shadow:0 20px 60px -20px rgba(0,0,0,.6);
  --ease:cubic-bezier(.22,1,.36,1);
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:Inter,system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.65;overflow-x:hidden;animation:pageIn .6s var(--ease) both}
body.leaving{animation:pageOut .28s ease forwards}
@keyframes pageIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
@keyframes pageOut{to{opacity:0;transform:translateY(-8px)}}
h1,h2,h3,h4{font-family:Sora,sans-serif;line-height:1.2;letter-spacing:-.02em}
a{color:inherit;text-decoration:none}
img,svg{display:block}
.container{width:min(1180px,100% - 40px);margin-inline:auto}
.section{padding:110px 0;position:relative}
.section.alt{background:var(--bg2)}
.eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:.8rem;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:var(--a2);margin-bottom:14px}
.eyebrow::before{content:"";width:22px;height:2px;background:var(--grad);border-radius:2px}
.title{font-size:clamp(2rem,4vw,3rem);margin-bottom:18px}
.lead{color:var(--muted);font-size:1.08rem;max-width:640px}
.center{text-align:center}.center .lead{margin-inline:auto}
.grad-text{background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:10px;padding:14px 26px;border-radius:999px;font-weight:600;font-size:.95rem;transition:transform .3s var(--ease),box-shadow .3s,background .3s;cursor:pointer;border:0;font-family:inherit}
.btn svg{transition:transform .3s var(--ease)}
.btn:hover svg{transform:translateX(4px)}
.btn-primary{background:var(--grad);color:#fff;box-shadow:0 10px 30px -10px rgba(99,102,241,.7)}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 18px 40px -12px rgba(34,211,238,.6)}
.btn-ghost{border:1px solid var(--line);color:var(--text);background:rgba(255,255,255,.03)}
.btn-ghost:hover{background:rgba(255,255,255,.08);transform:translateY(-3px)}

/* Header */
.header{position:fixed;inset:0 0 auto;z-index:50;transition:background .3s,box-shadow .3s,padding .3s;padding:20px 0}
.header.scrolled{background:rgba(7,11,26,.82);backdrop-filter:blur(14px);box-shadow:0 1px 0 var(--line);padding:12px 0}
.nav{display:flex;align-items:center;justify-content:space-between;gap:20px}
.logo{display:flex;align-items:center;gap:10px;font-family:Sora;font-weight:800;font-size:1.25rem}
.logo-mark{width:38px;height:38px;border-radius:11px;background:var(--grad);display:grid;place-items:center;color:#fff;font-size:1.1rem;box-shadow:0 8px 24px -8px var(--a1);animation:spinIn 1s var(--ease) both}
@keyframes spinIn{from{transform:rotate(-180deg) scale(.4);opacity:0}}
.menu{display:flex;gap:6px;list-style:none}
.menu a{padding:9px 16px;border-radius:999px;color:var(--muted);font-weight:500;font-size:.95rem;position:relative;transition:color .25s}
.menu a::after{content:"";position:absolute;left:16px;right:16px;bottom:4px;height:2px;background:var(--grad);border-radius:2px;transform:scaleX(0);transition:transform .35s var(--ease)}
.menu a:hover,.menu a.active{color:var(--text)}
.menu a:hover::after,.menu a.active::after{transform:scaleX(1)}
.nav-cta{padding:11px 20px}
.burger{display:none;width:44px;height:44px;border-radius:12px;border:1px solid var(--line);background:transparent;color:var(--text);cursor:pointer;position:relative}
.burger span{position:absolute;left:12px;right:12px;height:2px;background:currentColor;border-radius:2px;transition:transform .3s var(--ease),opacity .3s}
.burger span:nth-child(1){top:15px}.burger span:nth-child(2){top:21px}.burger span:nth-child(3){top:27px}
.nav-open .burger span:nth-child(1){transform:translateY(6px) rotate(45deg)}
.nav-open .burger span:nth-child(2){opacity:0}
.nav-open .burger span:nth-child(3){transform:translateY(-6px) rotate(-45deg)}

/* Hero */
.hero{min-height:100vh;display:flex;align-items:center;padding:140px 0 90px;position:relative;overflow:hidden}
.hero-bg{position:absolute;inset:0;z-index:-1;overflow:hidden}
.grid-bg{position:absolute;inset:0;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse at 50% 40%,#000 20%,transparent 70%);animation:gridMove 20s linear infinite}
@keyframes gridMove{to{background-position:60px 60px}}
.blob{position:absolute;border-radius:50%;filter:blur(90px);opacity:.55;animation:float 14s ease-in-out infinite}
.blob.b1{width:520px;height:520px;background:var(--a1);top:-120px;left:-120px}
.blob.b2{width:440px;height:440px;background:var(--a2);bottom:-160px;right:-80px;animation-delay:-5s}
.blob.b3{width:320px;height:320px;background:var(--a3);top:40%;left:45%;animation-delay:-9s;opacity:.35}
@keyframes float{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(60px,-40px) scale(1.1)}66%{transform:translate(-40px,50px) scale(.95)}}
.hero-grid{display:grid;grid-template-columns:1.1fr .9fr;gap:60px;align-items:center}
.badge{display:inline-flex;align-items:center;gap:10px;padding:8px 16px 8px 8px;border:1px solid var(--line);border-radius:999px;background:rgba(255,255,255,.04);font-size:.88rem;color:var(--muted);margin-bottom:26px}
.badge b{background:var(--grad);color:#fff;padding:3px 10px;border-radius:999px;font-size:.75rem}
.hero h1{font-size:clamp(2.6rem,5.6vw,4.4rem);font-weight:800;margin-bottom:24px}
.hero p{color:var(--muted);font-size:1.15rem;max-width:560px;margin-bottom:36px}
.hero-actions{display:flex;gap:14px;flex-wrap:wrap}
.typed{display:inline-block;min-width:1ch}
.typed::after{content:"|";margin-left:2px;color:var(--a2);animation:blink 1s steps(1) infinite}
@keyframes blink{50%{opacity:0}}
.hero-visual{position:relative;height:460px}
.glass{position:absolute;background:rgba(17,25,51,.72);backdrop-filter:blur(16px);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:22px}
.glass.main{inset:30px 20px 60px 40px;display:flex;flex-direction:column;gap:16px}
.dash-head{display:flex;justify-content:space-between;align-items:center;font-size:.85rem;color:var(--muted)}
.dots{display:flex;gap:6px}.dots i{width:10px;height:10px;border-radius:50%;background:#ef4444}.dots i:nth-child(2){background:#f59e0b}.dots i:nth-child(3){background:#10b981}
.bars{flex:1;display:flex;align-items:flex-end;gap:10px;padding-top:10px}
.bars span{flex:1;background:var(--grad);border-radius:8px 8px 4px 4px;transform-origin:bottom;animation:grow 1.4s var(--ease) both}
@keyframes grow{from{transform:scaleY(0)}}
.uptime{display:flex;justify-content:space-between;font-size:.9rem}
.uptime strong{font-family:Sora;font-size:1.5rem}
.glass.chip{display:flex;align-items:center;gap:12px;padding:14px 18px;font-size:.9rem;animation:bob 6s ease-in-out infinite}
.glass.chip .ic{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:var(--grad);color:#fff}
.glass.chip small{display:block;color:var(--muted);font-size:.78rem}
.chip.c1{top:0;right:0}.chip.c2{bottom:10px;left:0;animation-delay:-3s}
@keyframes bob{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}

/* Page hero */
.page-hero{padding:180px 0 90px;position:relative;overflow:hidden;text-align:center}
.page-hero h1{font-size:clamp(2.4rem,5vw,3.8rem);margin-bottom:18px}
.page-hero p{color:var(--muted);max-width:640px;margin:0 auto;font-size:1.1rem}
.crumbs{display:inline-flex;gap:8px;color:var(--muted);font-size:.9rem;margin-bottom:18px}
.crumbs a:hover{color:var(--a2)}

/* Marquee */
.marquee{border-block:1px solid var(--line);padding:26px 0;overflow:hidden;mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent)}
.marquee-track{display:flex;gap:60px;width:max-content;animation:scroll 35s linear infinite}
.marquee:hover .marquee-track{animation-play-state:paused}
.marquee span{font-family:Sora;font-weight:600;font-size:1.15rem;color:var(--muted);white-space:nowrap;opacity:.75}
@keyframes scroll{to{transform:translateX(-50%)}}

/* Cards */
.grid{display:grid;gap:24px}
.g3{grid-template-columns:repeat(3,1fr)}.g4{grid-template-columns:repeat(4,1fr)}.g2{grid-template-columns:repeat(2,1fr)}
.card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);padding:32px;position:relative;overflow:hidden;transition:transform .45s var(--ease),border-color .3s,box-shadow .45s}
.card::before{content:"";position:absolute;inset:0;background:radial-gradient(400px circle at var(--mx,50%) var(--my,0%),rgba(99,102,241,.18),transparent 45%);opacity:0;transition:opacity .4s}
.card:hover{transform:translateY(-8px);border-color:rgba(99,102,241,.45);box-shadow:var(--shadow)}
.card:hover::before{opacity:1}
.card>*{position:relative}
.card .ic{width:56px;height:56px;border-radius:16px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(34,211,238,.2));color:var(--a2);margin-bottom:22px;transition:transform .5s var(--ease),background .3s,color .3s}
.card:hover .ic{transform:rotate(-8deg) scale(1.08);background:var(--grad);color:#fff}
.card h3{font-size:1.25rem;margin-bottom:10px}
.card p{color:var(--muted);font-size:.97rem}
.card .more{display:inline-flex;align-items:center;gap:6px;margin-top:18px;color:var(--a2);font-weight:600;font-size:.92rem}
.card .more svg{transition:transform .3s var(--ease)}
.card:hover .more svg{transform:translateX(5px)}
.ticks{list-style:none;margin-top:18px;display:grid;gap:8px}
.ticks li{display:flex;gap:10px;align-items:center;color:var(--text);font-size:.94rem}
.ticks svg{color:var(--a2);flex:none}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;background:linear-gradient(135deg,rgba(99,102,241,.14),rgba(34,211,238,.08));border:1px solid var(--line);border-radius:24px;padding:46px}
.stat{text-align:center}
.stat strong{display:block;font-family:Sora;font-size:clamp(2.2rem,4vw,3rem);font-weight:800}
.stat span{color:var(--muted);font-size:.95rem}

/* Split */
.split{display:grid;grid-template-columns:1fr 1fr;gap:70px;align-items:center}
.visual-box{aspect-ratio:1/1;border-radius:28px;background:var(--card);border:1px solid var(--line);position:relative;overflow:hidden;display:grid;place-items:center}
.orbit{position:absolute;border:1px dashed rgba(148,163,255,.25);border-radius:50%;animation:rot 30s linear infinite}
.orbit.o1{width:80%;height:80%}.orbit.o2{width:55%;height:55%;animation-direction:reverse;animation-duration:20s}
.orbit i{position:absolute;top:-22px;left:50%;width:44px;height:44px;margin-left:-22px;border-radius:14px;background:var(--card2);border:1px solid var(--line);display:grid;place-items:center;color:var(--a2)}
.orbit i:nth-child(2){top:auto;bottom:-22px}
@keyframes rot{to{transform:rotate(360deg)}}
.core{width:110px;height:110px;border-radius:30px;background:var(--grad);display:grid;place-items:center;font-family:Sora;font-weight:800;font-size:2.4rem;color:#fff;box-shadow:0 0 80px rgba(99,102,241,.6);animation:pulse 3s ease-in-out infinite}
@keyframes pulse{50%{box-shadow:0 0 120px rgba(34,211,238,.7);transform:scale(1.05)}}

/* Process */
.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:24px;counter-reset:s;position:relative}
.step{padding:30px;border-radius:var(--radius);background:var(--card);border:1px solid var(--line);position:relative}
.step::before{counter-increment:s;content:"0" counter(s);font-family:Sora;font-weight:800;font-size:2.6rem;background:var(--grad);-webkit-background-clip:text;background-clip:text;color:transparent;display:block;margin-bottom:10px}
.step h4{margin-bottom:8px}.step p{color:var(--muted);font-size:.94rem}

/* Testimonials */
.slider{position:relative;max-width:820px;margin:50px auto 0;min-height:260px}
.slide{position:absolute;inset:0;text-align:center;opacity:0;transform:translateY(20px) scale(.98);transition:opacity .7s var(--ease),transform .7s var(--ease);pointer-events:none}
.slide.on{opacity:1;transform:none;pointer-events:auto}
.slide blockquote{font-family:Sora;font-size:clamp(1.2rem,2.4vw,1.6rem);font-weight:500;line-height:1.5;margin-bottom:26px}
.slide blockquote::before{content:"\201C";display:block;font-size:4rem;line-height:.6;color:var(--a1);margin-bottom:14px}
.slide cite{font-style:normal;color:var(--muted)}.slide cite b{color:var(--text);display:block}
.slider-dots{display:flex;justify-content:center;gap:10px;margin-top:20px}
.slider-dots button{width:10px;height:10px;border-radius:999px;border:0;background:var(--line);cursor:pointer;transition:width .4s var(--ease),background .3s}
.slider-dots button.on{width:32px;background:var(--grad)}

/* CTA */
.cta{border-radius:28px;padding:70px 50px;text-align:center;background:var(--grad);position:relative;overflow:hidden}
.cta::before,.cta::after{content:"";position:absolute;width:300px;height:300px;border-radius:50%;background:rgba(255,255,255,.12);animation:float 12s ease-in-out infinite}
.cta::before{top:-120px;left:-80px}.cta::after{bottom:-140px;right:-60px;animation-delay:-6s}
.cta>*{position:relative}
.cta h2{font-size:clamp(1.9rem,4vw,2.8rem);color:#fff;margin-bottom:14px}
.cta p{color:rgba(255,255,255,.88);margin-bottom:30px;font-size:1.08rem}
.cta .btn{background:#fff;color:#1e1b4b}
.cta .btn:hover{transform:translateY(-3px);box-shadow:0 18px 40px -12px rgba(0,0,0,.5)}

/* Team */
.member{text-align:center}
.avatar{width:110px;height:110px;border-radius:50%;margin:0 auto 18px;background:var(--grad);display:grid;place-items:center;font-family:Sora;font-weight:700;font-size:1.8rem;color:#fff;position:relative;transition:transform .5s var(--ease)}
.avatar::after{content:"";position:absolute;inset:-6px;border-radius:50%;border:2px dashed rgba(34,211,238,.4);animation:rot 14s linear infinite}
.member:hover .avatar{transform:scale(1.07)}
.member span{color:var(--muted);font-size:.92rem}

/* Timeline */
.timeline{position:relative;max-width:760px;margin:50px auto 0;padding-left:34px}
.timeline::before{content:"";position:absolute;left:8px;top:6px;bottom:6px;width:2px;background:linear-gradient(var(--a1),var(--a2))}
.tl{position:relative;margin-bottom:34px}
.tl::before{content:"";position:absolute;left:-32px;top:6px;width:16px;height:16px;border-radius:50%;background:var(--bg);border:3px solid var(--a2);box-shadow:0 0 0 6px rgba(34,211,238,.12)}
.tl b{font-family:Sora;color:var(--a2)}.tl h4{margin:4px 0}.tl p{color:var(--muted)}

/* Work */
.filters{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin:40px 0}
.filters button{padding:10px 20px;border-radius:999px;border:1px solid var(--line);background:transparent;color:var(--muted);font:inherit;font-weight:500;cursor:pointer;transition:all .3s var(--ease)}
.filters button:hover{color:var(--text);border-color:rgba(99,102,241,.5)}
.filters button.on{background:var(--grad);color:#fff;border-color:transparent}
.project{border-radius:var(--radius);overflow:hidden;background:var(--card);border:1px solid var(--line);transition:transform .5s var(--ease),opacity .4s,box-shadow .5s}
.project.hide{display:none}
.project:hover{transform:translateY(-8px);box-shadow:var(--shadow)}
.project .thumb{height:200px;position:relative;overflow:hidden}
.project .thumb::before{content:"";position:absolute;inset:0;background:inherit;transition:transform .8s var(--ease)}
.project .thumb::after{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.12) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.12) 1px,transparent 1px);background-size:28px 28px;transition:transform .8s var(--ease)}
.project:hover .thumb::after{transform:scale(1.15) rotate(2deg)}
.project .tag{position:absolute;top:16px;left:16px;z-index:1;background:rgba(0,0,0,.35);backdrop-filter:blur(6px);padding:5px 12px;border-radius:999px;font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em}
.project .body{padding:24px}
.project h3{font-size:1.15rem;margin-bottom:6px}
.project .client{color:var(--muted);font-size:.9rem}
.project .result{margin-top:14px;display:inline-flex;align-items:center;gap:8px;color:var(--a2);font-weight:600;font-size:.92rem}

/* Careers */
.job{border:1px solid var(--line);border-radius:var(--radius);background:var(--card);margin-bottom:14px;overflow:hidden;transition:border-color .3s}
.job.open{border-color:rgba(99,102,241,.5)}
.job-head{width:100%;display:flex;justify-content:space-between;align-items:center;gap:16px;padding:24px 28px;background:none;border:0;color:inherit;font:inherit;text-align:left;cursor:pointer}
.job-head h3{font-size:1.12rem}
.job-meta{display:flex;gap:16px;color:var(--muted);font-size:.88rem;margin-top:4px;flex-wrap:wrap}
.job-head .plus{flex:none;width:38px;height:38px;border-radius:50%;border:1px solid var(--line);display:grid;place-items:center;transition:transform .4s var(--ease),background .3s}
.job.open .plus{transform:rotate(45deg);background:var(--grad);border-color:transparent}
.job-body{display:grid;grid-template-rows:0fr;transition:grid-template-rows .45s var(--ease)}
.job.open .job-body{grid-template-rows:1fr}
.job-body>div{overflow:hidden}
.job-body p{padding:0 28px 18px;color:var(--muted)}
.job-body .btn{margin:0 28px 26px}

/* Contact */
.contact-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:40px}
.info-item{display:flex;gap:16px;padding:22px;border-radius:var(--radius);background:var(--card);border:1px solid var(--line);margin-bottom:16px;transition:transform .4s var(--ease)}
.info-item:hover{transform:translateX(6px)}
.info-item .ic{width:48px;height:48px;flex:none;border-radius:14px;display:grid;place-items:center;background:var(--grad);color:#fff}
.info-item small{color:var(--muted);display:block}
.form{background:var(--card);border:1px solid var(--line);border-radius:24px;padding:38px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:18px}
.field{margin-bottom:18px;position:relative}
.field label{display:block;font-size:.88rem;font-weight:500;margin-bottom:8px;color:var(--muted)}
.field input,.field select,.field textarea{width:100%;padding:14px 16px;border-radius:12px;border:1px solid var(--line);background:var(--bg2);color:var(--text);font:inherit;transition:border-color .3s,box-shadow .3s}
.field textarea{min-height:150px;resize:vertical}
.field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--a1);box-shadow:0 0 0 4px rgba(99,102,241,.18)}
.field.err input,.field.err textarea{border-color:#f87171}
.field .msg{color:#f87171;font-size:.84rem;margin-top:6px}
.hp{position:absolute;left:-9999px}
.alert{display:flex;gap:12px;align-items:flex-start;padding:16px 18px;border-radius:14px;margin-bottom:22px;animation:pageIn .6s var(--ease)}
.alert.ok{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.4);color:#6ee7b7}
.alert.bad{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.4);color:#fca5a5}
.map{margin-top:16px;height:200px;border-radius:var(--radius);border:1px solid var(--line);background:radial-gradient(circle at 50% 50%,rgba(34,211,238,.25),transparent 30%),var(--card);position:relative;overflow:hidden}
.map::before{content:"";position:absolute;inset:0;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:24px 24px}
.pin{position:absolute;left:50%;top:50%;transform:translate(-50%,-100%);color:var(--a2);animation:bob 2.4s ease-in-out infinite}
.pin::after{content:"";position:absolute;left:50%;bottom:-6px;width:16px;height:16px;margin-left:-8px;border-radius:50%;border:2px solid var(--a2);animation:ping 2s ease-out infinite}
@keyframes ping{from{transform:scale(.4);opacity:1}to{transform:scale(3);opacity:0}}

/* 404 */
.nf{min-height:100vh;display:grid;place-items:center;text-align:center;padding:120px 20px}
.nf h1{font-size:clamp(6rem,18vw,11rem);font-weight:800;line-height:1;animation:glitch 3s infinite}
@keyframes glitch{0%,92%,100%{transform:none;text-shadow:none}93%{transform:translate(-3px,2px);text-shadow:3px 0 var(--a2),-3px 0 var(--a3)}96%{transform:translate(3px,-2px);text-shadow:-3px 0 var(--a2),3px 0 var(--a3)}}

/* Footer */
.footer{border-top:1px solid var(--line);padding:70px 0 30px;background:var(--bg2)}
.foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1.2fr;gap:40px;margin-bottom:50px}
.footer h4{font-size:1rem;margin-bottom:16px}
.footer p,.footer li{color:var(--muted);font-size:.94rem}
.footer ul{list-style:none;display:grid;gap:10px}
.footer ul a{transition:color .25s,padding .25s}
.footer ul a:hover{color:var(--a2);padding-left:4px}
.newsletter{display:flex;gap:8px;margin-top:14px}
.newsletter input{flex:1;min-width:0;padding:12px 14px;border-radius:999px;border:1px solid var(--line);background:var(--card);color:var(--text);font:inherit}
.newsletter button{padding:12px 18px}
.copy{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;border-top:1px solid var(--line);padding-top:26px;color:var(--muted);font-size:.88rem}

/* Back to top + progress */
.progress{position:fixed;top:0;left:0;height:3px;background:var(--grad);z-index:60;width:0;transition:width .1s}
.totop{position:fixed;right:24px;bottom:24px;width:50px;height:50px;border-radius:50%;background:var(--grad);color:#fff;border:0;display:grid;place-items:center;cursor:pointer;z-index:40;opacity:0;transform:translateY(20px) scale(.8);transition:all .4s var(--ease);box-shadow:0 10px 30px -10px var(--a1)}
.totop.show{opacity:1;transform:none}
.totop svg{transform:rotate(-90deg)}

/* Reveal animations */
.reveal{opacity:0;transform:translateY(40px);transition:opacity .9s var(--ease),transform .9s var(--ease);transition-delay:var(--d,0s)}
.reveal.left{transform:translateX(-50px)}.reveal.right{transform:translateX(50px)}.reveal.zoom{transform:scale(.9)}
.reveal.in{opacity:1;transform:none}

/* Responsive */
@media (max-width:1024px){
  .g4,.steps,.stats{grid-template-columns:repeat(2,1fr)}
  .g3{grid-template-columns:repeat(2,1fr)}
  .foot-grid{grid-template-columns:1fr 1fr}
}
@media (max-width:860px){
  .burger{display:block}
  .nav-cta{display:none}
  .menu{position:fixed;inset:72px 16px auto;flex-direction:column;background:rgba(12,18,38,.97);backdrop-filter:blur(16px);border:1px solid var(--line);border-radius:20px;padding:14px;opacity:0;transform:translateY(-12px);pointer-events:none;transition:all .35s var(--ease)}
  .nav-open .menu{opacity:1;transform:none;pointer-events:auto}
  .menu a{display:block;padding:14px 16px}
  .menu a::after{display:none}
  .menu a.active{background:rgba(99,102,241,.15)}
  .hero-grid,.split,.contact-grid{grid-template-columns:1fr}
  .hero-visual{height:380px}
  .section{padding:80px 0}
}
@media (max-width:600px){
  .g2,.g3,.g4,.steps,.row,.foot-grid{grid-template-columns:1fr}
  .stats{grid-template-columns:1fr 1fr;padding:30px 18px}
  .cta{padding:50px 24px}
  .form{padding:26px 20px}
  .glass.main{inset:40px 0 70px 0}
}
@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation:none!important;transition:none!important}
  .reveal{opacity:1;transform:none}
}
</style>
</head>
<body>
<div class="progress" id="progress"></div>

<header class="header" id="header">
  <div class="container nav">
    <a href="<?= url('home') ?>" class="logo"><span class="logo-mark">A</span>Acme<span class="grad-text">Tech</span></a>
    <ul class="menu" id="menu">
      <?php foreach ($pages as $slug => $label): ?>
        <li><a href="<?= url($slug) ?>" class="<?= $page === $slug ? 'active' : '' ?>"><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <a href="<?= url('contact') ?>" class="btn btn-primary nav-cta">Get a quote <?= icon('arrow', 16) ?></a>
    <button class="burger" id="burger" aria-label="Toggle menu" aria-expanded="false"><span></span><span></span><span></span></button>
  </div>
</header>

<main>
<?php
/* ======================= PAGE PARTIALS ======================= */
function page_hero($crumb, $title, $text) { ?>
  <section class="page-hero">
    <div class="hero-bg"><div class="grid-bg"></div><div class="blob b1"></div><div class="blob b2"></div></div>
    <div class="container">
      <div class="crumbs reveal"><a href="<?= url('home') ?>">Home</a> / <span><?= e($crumb) ?></span></div>
      <h1 class="reveal" style="--d:.1s"><?= $title ?></h1>
      <p class="reveal" style="--d:.2s"><?= e($text) ?></p>
    </div>
  </section>
<?php }

function cta_block() { ?>
  <section class="section">
    <div class="container">
      <div class="cta reveal zoom">
        <h2>Ready to build something great?</h2>
        <p>Book a free 30-minute consultation with our engineers. No sales pitch — just honest advice.</p>
        <a href="<?= url('contact') ?>" class="btn">Talk to an expert <?= icon('arrow', 18) ?></a>
      </div>
    </div>
  </section>
<?php }

switch ($page):

/* ------------------------- HOME ------------------------- */
case 'home': ?>
  <section class="hero">
    <div class="hero-bg"><div class="grid-bg"></div><div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div></div>
    <div class="container hero-grid">
      <div>
        <div class="badge reveal"><b>NEW</b> AI automation services now live</div>
        <h1 class="reveal" style="--d:.1s">IT services that make your business <span class="grad-text typed" id="typed" data-words="faster.|safer.|smarter.|scalable."></span></h1>
        <p class="reveal" style="--d:.2s">Acme Technologies designs, builds and runs the technology behind ambitious companies — cloud, security, software and support, all under one roof.</p>
        <div class="hero-actions reveal" style="--d:.3s">
          <a href="<?= url('services') ?>" class="btn btn-primary">Explore services <?= icon('arrow', 18) ?></a>
          <a href="<?= url('work') ?>" class="btn btn-ghost">See our work</a>
        </div>
      </div>
      <div class="hero-visual reveal right" style="--d:.3s">
        <div class="glass main">
          <div class="dash-head"><div class="dots"><i></i><i></i><i></i></div><span>Infrastructure health</span></div>
          <div class="bars">
            <?php foreach ([45,70,55,85,60,95,75,88] as $i => $h): ?>
              <span style="height:<?= $h ?>%;animation-delay:<?= .4 + $i * .08 ?>s"></span>
            <?php endforeach; ?>
          </div>
          <div class="uptime"><div><small style="color:var(--muted)">Uptime (90 days)</small><br><strong>99.99%</strong></div><div style="text-align:right"><small style="color:var(--muted)">Threats blocked</small><br><strong>12,840</strong></div></div>
        </div>
        <div class="glass chip c1"><span class="ic"><?= icon('shield', 20) ?></span><div><b>Secured</b><small>SOC monitoring 24/7</small></div></div>
        <div class="glass chip c2"><span class="ic"><?= icon('zap', 20) ?></span><div><b>Deployed</b><small>v2.4 shipped in 3 min</small></div></div>
      </div>
    </div>
  </section>

  <div class="marquee"><div class="marquee-track">
    <?php for ($r = 0; $r < 2; $r++) foreach ($tech as $t): ?><span><?= e($t) ?></span><?php endforeach; ?>
  </div></div>

  <section class="section">
    <div class="container">
      <div class="center">
        <span class="eyebrow reveal">What we do</span>
        <h2 class="title reveal" style="--d:.1s">End-to-end IT services, <span class="grad-text">one trusted partner</span></h2>
        <p class="lead reveal" style="--d:.2s">From strategy to round-the-clock support, our specialists cover every layer of your technology stack.</p>
      </div>
      <div class="grid g3" style="margin-top:56px">
        <?php foreach ($services as $i => $s): ?>
          <a href="<?= url('services') ?>#<?= $s['id'] ?>" class="card reveal" style="--d:<?= $i * .08 ?>s">
            <div class="ic"><?= icon($s['icon'], 26) ?></div>
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['text']) ?></p>
            <span class="more">Learn more <?= icon('arrow', 16) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="stats reveal zoom">
        <?php foreach ($stats as $s): ?>
          <div class="stat"><strong><span class="count" data-to="<?= $s['n'] ?>">0</span><?= e($s['suffix']) ?></strong><span><?= e($s['label']) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section alt">
    <div class="container split">
      <div class="reveal left">
        <div class="visual-box">
          <div class="orbit o1"><i><?= icon('cloud', 20) ?></i><i><?= icon('shield', 20) ?></i></div>
          <div class="orbit o2"><i><?= icon('code', 20) ?></i><i><?= icon('cpu', 20) ?></i></div>
          <div class="core">A</div>
        </div>
      </div>
      <div>
        <span class="eyebrow reveal">Why Acme</span>
        <h2 class="title reveal" style="--d:.1s">Engineering excellence with a human touch</h2>
        <p class="lead reveal" style="--d:.2s">We combine certified expertise with genuine partnership. You get senior engineers, transparent pricing and a team that picks up the phone.</p>
        <ul class="ticks reveal" style="--d:.3s">
          <li><?= icon('check', 18) ?> ISO 27001 certified delivery processes</li>
          <li><?= icon('check', 18) ?> AWS, Azure & Google Cloud partner engineers</li>
          <li><?= icon('check', 18) ?> 15-minute response SLA for critical issues</li>
          <li><?= icon('check', 18) ?> Fixed-price projects or flexible monthly plans</li>
        </ul>
        <a href="<?= url('about') ?>" class="btn btn-primary reveal" style="--d:.4s;margin-top:30px">About Acme <?= icon('arrow', 18) ?></a>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="center">
        <span class="eyebrow reveal">Client stories</span>
        <h2 class="title reveal" style="--d:.1s">Trusted by teams who can't afford downtime</h2>
      </div>
      <div class="slider reveal" style="--d:.2s" id="slider">
        <?php foreach ($testimonials as $i => $t): ?>
          <div class="slide <?= $i === 0 ? 'on' : '' ?>">
            <blockquote><?= e($t['q']) ?></blockquote>
            <cite><b><?= e($t['n']) ?></b><?= e($t['r']) ?></cite>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="slider-dots" id="sliderDots">
        <?php foreach ($testimonials as $i => $_): ?><button class="<?= $i === 0 ? 'on' : '' ?>" aria-label="Testimonial <?= $i + 1 ?>"></button><?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php cta_block(); break;

/* ------------------------- ABOUT ------------------------- */
case 'about':
  page_hero('About', 'We make technology <span class="grad-text">work for people</span>', 'Since 2014, Acme Technologies has helped startups and enterprises across India and beyond build, secure and scale their IT.'); ?>
  <section class="section">
    <div class="container split">
      <div>
        <span class="eyebrow reveal">Our story</span>
        <h2 class="title reveal" style="--d:.1s">From a two-person helpdesk to a 180-strong IT partner</h2>
        <p class="lead reveal" style="--d:.2s">Acme started in a small Gurugram office fixing laptops and networks for local businesses. Our clients kept asking us to do more — so we did.</p>
        <p class="lead reveal" style="--d:.3s;margin-top:16px">Today we run cloud platforms, security operations and custom software for companies in retail, healthcare, finance and logistics — with the same obsession for responsiveness we started with.</p>
      </div>
      <div class="grid g2">
        <?php foreach ([['target','Our mission','Make enterprise-grade IT simple, secure and affordable for every business.'],['zap','Our vision','A world where technology quietly enables people instead of getting in their way.'],['heart','Our values','Honesty, ownership, craftsmanship and kindness — in every ticket and every line of code.'],['users','Our people','180 engineers, analysts and designers who love solving hard problems.']] as $i => $v): ?>
          <div class="card reveal" style="--d:<?= $i * .1 ?>s;padding:26px">
            <div class="ic"><?= icon($v[0], 24) ?></div><h3 style="font-size:1.08rem"><?= e($v[1]) ?></h3><p><?= e($v[2]) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="container">
      <div class="stats reveal zoom">
        <?php foreach ($stats as $s): ?>
          <div class="stat"><strong><span class="count" data-to="<?= $s['n'] ?>">0</span><?= e($s['suffix']) ?></strong><span><?= e($s['label']) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section alt">
    <div class="container">
      <div class="center"><span class="eyebrow reveal">Milestones</span><h2 class="title reveal" style="--d:.1s">Our journey so far</h2></div>
      <div class="timeline">
        <?php foreach ([['2014','Founded in Gurugram','Started as a managed IT and helpdesk provider for local SMEs.'],['2017','Cloud practice launched','Became an AWS and Azure partner and completed our 100th migration.'],['2020','24/7 Security Operations Centre','Opened our SOC to deliver managed detection & response.'],['2023','ISO 27001 certified','Formalised our security-first delivery across every team.'],['2026','Data & AI division','Helping clients put practical AI and automation into production.']] as $i => $m): ?>
          <div class="tl reveal left" style="--d:<?= $i * .1 ?>s"><b><?= $m[0] ?></b><h4><?= e($m[1]) ?></h4><p><?= e($m[2]) ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="center"><span class="eyebrow reveal">Leadership</span><h2 class="title reveal" style="--d:.1s">The people behind Acme</h2></div>
      <div class="grid g4" style="margin-top:50px">
        <?php foreach ($team as $i => $m): ?>
          <div class="card member reveal" style="--d:<?= $i * .1 ?>s"><div class="avatar"><?= e($m['i']) ?></div><h3 style="font-size:1.1rem"><?= e($m['n']) ?></h3><span><?= e($m['r']) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php cta_block(); break;

/* ------------------------- SERVICES ------------------------- */
case 'services':
  page_hero('Services', 'IT services built <span class="grad-text">around your goals</span>', 'Pick a single service or let us manage your entire technology estate. Either way, you get senior expertise and clear SLAs.'); ?>
  <section class="section">
    <div class="container">
      <div class="grid g3">
        <?php foreach ($services as $i => $s): ?>
          <div class="card reveal" id="<?= $s['id'] ?>" style="--d:<?= ($i % 3) * .1 ?>s;scroll-margin-top:110px">
            <div class="ic"><?= icon($s['icon'], 26) ?></div>
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['text']) ?></p>
            <ul class="ticks"><?php foreach ($s['points'] as $pt): ?><li><?= icon('check', 16) ?> <?= e($pt) ?></li><?php endforeach; ?></ul>
            <a href="<?= url('contact') ?>&service=<?= urlencode($s['title']) ?>#form" class="more">Enquire <?= icon('arrow', 16) ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section alt">
    <div class="container">
      <div class="center">
        <span class="eyebrow reveal">How we work</span>
        <h2 class="title reveal" style="--d:.1s">A proven four-step process</h2>
        <p class="lead reveal" style="--d:.2s">Clear milestones, weekly demos and no surprises on the invoice.</p>
      </div>
      <div class="steps" style="margin-top:50px">
        <?php foreach ([['Discover','We map your goals, systems and risks in a short, focused workshop.'],['Design','Architects propose a solution with timelines and a fixed or capped budget.'],['Deliver','Agile sprints with weekly demos keep you in control at every stage.'],['Support','We monitor, optimise and support — 24/7, with SLA-backed response.']] as $i => $st): ?>
          <div class="step reveal" style="--d:<?= $i * .12 ?>s"><h4><?= e($st[0]) ?></h4><p><?= e($st[1]) ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="center"><span class="eyebrow reveal">Engagement models</span><h2 class="title reveal" style="--d:.1s">Flexible ways to work together</h2></div>
      <div class="grid g3" style="margin-top:50px">
        <?php foreach ([['clock','Project-based','Fixed scope, fixed price. Ideal for migrations, audits and new builds.'],['users','Dedicated team','A full-time squad of engineers who work as an extension of your team.'],['headset','Managed services','Monthly plans covering monitoring, support and continuous improvement.']] as $i => $m): ?>
          <div class="card reveal" style="--d:<?= $i * .1 ?>s;text-align:center"><div class="ic" style="margin-inline:auto"><?= icon($m[0], 26) ?></div><h3><?= e($m[1]) ?></h3><p><?= e($m[2]) ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php cta_block(); break;

/* ------------------------- WORK ------------------------- */
case 'work':
  page_hero('Work', 'Results we\'re <span class="grad-text">proud of</span>', 'A selection of recent projects across cloud, security, software and AI.'); ?>
  <section class="section" style="padding-top:20px">
    <div class="container">
      <div class="filters reveal" id="filters">
        <button class="on" data-f="all">All</button>
        <button data-f="cloud">Cloud</button>
        <button data-f="security">Security</button>
        <button data-f="software">Software</button>
        <button data-f="ai">Data & AI</button>
      </div>
      <div class="grid g3" id="projects">
        <?php foreach ($projects as $i => $p): ?>
          <article class="project reveal" data-cat="<?= $p['cat'] ?>" style="--d:<?= ($i % 3) * .1 ?>s">
            <div class="thumb" style="background:linear-gradient(135deg,<?= $p['grad'] ?>)"><span class="tag"><?= e($p['cat']) ?></span></div>
            <div class="body">
              <h3><?= e($p['title']) ?></h3>
              <div class="client"><?= e($p['client']) ?></div>
              <div class="result"><?= icon('zap', 16) ?> <?= e($p['result']) ?></div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php cta_block(); break;

/* ------------------------- CAREERS ------------------------- */
case 'careers':
  page_hero('Careers', 'Do the best work <span class="grad-text">of your career</span>', 'Join a team that values curiosity, ownership and kindness — and invests in your growth.'); ?>
  <section class="section">
    <div class="container">
      <div class="grid g4">
        <?php foreach ([['heart','Health first','Comprehensive health cover for you and your family.'],['zap','Learning budget','₹1,00,000 a year for courses, certifications and conferences.'],['clock','Flexible work','Hybrid schedules and remote-friendly roles.'],['users','Great people','Mentorship from senior engineers from day one.']] as $i => $b): ?>
          <div class="card reveal" style="--d:<?= $i * .1 ?>s"><div class="ic"><?= icon($b[0], 24) ?></div><h3 style="font-size:1.1rem"><?= e($b[1]) ?></h3><p><?= e($b[2]) ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <section class="section alt">
    <div class="container" style="max-width:860px">
      <div class="center" style="margin-bottom:40px"><span class="eyebrow reveal">Open roles</span><h2 class="title reveal" style="--d:.1s">Current openings</h2></div>
      <?php foreach ($jobs as $i => $j): ?>
        <div class="job reveal" style="--d:<?= $i * .08 ?>s">
          <button class="job-head" aria-expanded="false">
            <div><h3><?= e($j['t']) ?></h3><div class="job-meta"><span><?= e($j['l']) ?></span><span><?= e($j['type']) ?></span></div></div>
            <span class="plus"><?= icon('plus', 18) ?></span>
          </button>
          <div class="job-body"><div>
            <p><?= e($j['d']) ?></p>
            <a href="mailto:careers@acmetech.example?subject=<?= rawurlencode('Application: ' . $j['t']) ?>" class="btn btn-primary">Apply now <?= icon('arrow', 16) ?></a>
          </div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php cta_block(); break;

/* ------------------------- CONTACT ------------------------- */
case 'contact':
  $preService = $old['service'] ?: ($_GET['service'] ?? '');
  page_hero('Contact', 'Let\'s talk about <span class="grad-text">your project</span>', 'Tell us what you need and an engineer — not a salesperson — will get back to you within one business day.'); ?>
  <section class="section" id="form" style="padding-top:20px">
    <div class="container contact-grid">
      <div>
        <div class="info-item reveal left"><span class="ic"><?= icon('mail', 22) ?></span><div><small>Email us</small><a href="mailto:<?= CONTACT_TO ?>"><b><?= CONTACT_TO ?></b></a></div></div>
        <div class="info-item reveal left" style="--d:.1s"><span class="ic"><?= icon('phone', 22) ?></span><div><small>Call us</small><a href="tel:<?= preg_replace('/\s+/', '', CONTACT_PHONE) ?>"><b><?= CONTACT_PHONE ?></b></a></div></div>
        <div class="info-item reveal left" style="--d:.2s"><span class="ic"><?= icon('map', 22) ?></span><div><small>Visit us</small><b><?= CONTACT_ADDR ?></b></div></div>
        <div class="info-item reveal left" style="--d:.3s"><span class="ic"><?= icon('clock', 22) ?></span><div><small>Office hours</small><b>Mon–Fri, 9:00–18:00 IST</b><br><small>Support available 24/7</small></div></div>
        <div class="map reveal zoom" style="--d:.4s"><span class="pin"><?= icon('map', 34) ?></span></div>
      </div>

      <form class="form reveal right" method="post" action="<?= url('contact') ?>#form" novalidate>
        <h2 style="font-size:1.6rem;margin-bottom:6px">Send us a message</h2>
        <p style="color:var(--muted);margin-bottom:24px">Fields marked * are required.</p>

        <?php if ($flash): ?><div class="alert ok"><?= icon('check', 20) ?><span><?= e($flash) ?></span></div><?php endif; ?>
        <?php if (!empty($errors['form'])): ?><div class="alert bad"><span><?= e($errors['form']) ?></span></div><?php elseif ($errors): ?><div class="alert bad"><span>Please fix the highlighted fields below.</span></div><?php endif; ?>

        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

        <div class="row">
          <div class="field <?= isset($errors['name']) ? 'err' : '' ?>">
            <label for="name">Full name *</label>
            <input id="name" name="name" value="<?= e($old['name']) ?>" required>
            <?php if (isset($errors['name'])): ?><div class="msg"><?= e($errors['name']) ?></div><?php endif; ?>
          </div>
          <div class="field <?= isset($errors['email']) ? 'err' : '' ?>">
            <label for="email">Work email *</label>
            <input id="email" type="email" name="email" value="<?= e($old['email']) ?>" required>
            <?php if (isset($errors['email'])): ?><div class="msg"><?= e($errors['email']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="row">
          <div class="field">
            <label for="company">Company</label>
            <input id="company" name="company" value="<?= e($old['company']) ?>">
          </div>
          <div class="field">
            <label for="service">Service of interest</label>
            <select id="service" name="service">
              <option value="">Not sure yet</option>
              <?php foreach ($services as $s): ?><option <?= $preService === $s['title'] ? 'selected' : '' ?>><?= e($s['title']) ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="field <?= isset($errors['message']) ? 'err' : '' ?>">
          <label for="message">How can we help? *</label>
          <textarea id="message" name="message" required><?= e($old['message']) ?></textarea>
          <?php if (isset($errors['message'])): ?><div class="msg"><?= e($errors['message']) ?></div><?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Send message <?= icon('arrow', 18) ?></button>
      </form>
    </div>
  </section>
  <?php break;

/* ------------------------- 404 ------------------------- */
default: ?>
  <section class="nf">
    <div class="hero-bg"><div class="grid-bg"></div><div class="blob b1"></div><div class="blob b2"></div></div>
    <div>
      <h1 class="grad-text">404</h1>
      <h2 style="margin:10px 0 14px">This page took a coffee break</h2>
      <p style="color:var(--muted);margin-bottom:30px">The page you're looking for doesn't exist or has moved.</p>
      <a href="<?= url('home') ?>" class="btn btn-primary">Back to home <?= icon('arrow', 18) ?></a>
    </div>
  </section>
<?php endswitch; ?>
</main>

<footer class="footer">
  <div class="container">
    <div class="foot-grid">
      <div>
        <a href="<?= url('home') ?>" class="logo" style="margin-bottom:16px"><span class="logo-mark">A</span>Acme<span class="grad-text">Tech</span></a>
        <p>Cloud, cybersecurity, software and managed IT services for ambitious businesses.</p>
      </div>
      <div>
        <h4>Company</h4>
        <ul><?php foreach (['about','work','careers','contact'] as $p): ?><li><a href="<?= url($p) ?>"><?= e($pages[$p]) ?></a></li><?php endforeach; ?></ul>
      </div>
      <div>
        <h4>Services</h4>
        <ul><?php foreach (array_slice($services, 0, 5) as $s): ?><li><a href="<?= url('services') ?>#<?= $s['id'] ?>"><?= e($s['title']) ?></a></li><?php endforeach; ?></ul>
      </div>
      <div>
        <h4>Stay in the loop</h4>
        <p>Monthly insights on cloud, security and AI. No spam.</p>
        <form class="newsletter" onsubmit="event.preventDefault();this.innerHTML='<p style=&quot;color:#6ee7b7&quot;>Thanks for subscribing!</p>'">
          <input type="email" placeholder="you@company.com" required aria-label="Email">
          <button class="btn btn-primary" type="submit">Join</button>
        </form>
      </div>
    </div>
    <div class="copy">
      <span>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</span>
      <span><?= CONTACT_ADDR ?></span>
    </div>
  </div>
</footer>

<button class="totop" id="totop" aria-label="Back to top"><?= icon('arrow', 20) ?></button>

<script>
(() => {
  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => [...c.querySelectorAll(s)];

  // Header shadow, progress bar, back-to-top
  const header = $('#header'), bar = $('#progress'), top = $('#totop');
  const onScroll = () => {
    const y = scrollY, h = document.documentElement.scrollHeight - innerHeight;
    header.classList.toggle('scrolled', y > 20);
    bar.style.width = (h > 0 ? (y / h) * 100 : 0) + '%';
    top.classList.toggle('show', y > 500);
  };
  addEventListener('scroll', onScroll, { passive: true }); onScroll();
  top.addEventListener('click', () => scrollTo({ top: 0, behavior: 'smooth' }));

  // Mobile menu
  const burger = $('#burger');
  burger.addEventListener('click', () => {
    const open = document.body.classList.toggle('nav-open');
    burger.setAttribute('aria-expanded', open);
  });

  // Page transition on internal links
  $$('a[href^="?page="]').forEach(a => a.addEventListener('click', ev => {
    if (ev.metaKey || ev.ctrlKey || ev.shiftKey || a.target) return;
    const href = a.getAttribute('href');
    const samePage = href.split('#')[0] === location.search && href.includes('#');
    if (samePage) { document.body.classList.remove('nav-open'); return; }
    ev.preventDefault();
    document.body.classList.add('leaving');
    setTimeout(() => location.href = href, 260);
  }));
  addEventListener('pageshow', e => { if (e.persisted) document.body.classList.remove('leaving'); });

  // Scroll reveal
  const io = new IntersectionObserver(entries => entries.forEach(en => {
    if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
  }), { threshold: .12, rootMargin: '0px 0px -40px 0px' });
  $$('.reveal').forEach(el => io.observe(el));

  // Animated counters
  const cio = new IntersectionObserver(entries => entries.forEach(en => {
    if (!en.isIntersecting) return;
    const el = en.target, to = +el.dataset.to, start = performance.now(), dur = 1800;
    const tick = now => {
      const p = Math.min((now - start) / dur, 1), eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(to * eased).toLocaleString();
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick); cio.unobserve(el);
  }), { threshold: .5 });
  $$('.count').forEach(el => cio.observe(el));

  // Card spotlight follows cursor
  $$('.card').forEach(c => c.addEventListener('pointermove', e => {
    const r = c.getBoundingClientRect();
    c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
    c.style.setProperty('--my', (e.clientY - r.top) + 'px');
  }));

  // Typing effect
  const typed = $('#typed');
  if (typed) {
    const words = typed.dataset.words.split('|'); let w = 0, i = 0, del = false;
    const loop = () => {
      const word = words[w];
      typed.textContent = word.slice(0, i);
      if (!del && i < word.length) { i++; setTimeout(loop, 90); }
      else if (!del) { del = true; setTimeout(loop, 1600); }
      else if (i > 0) { i--; setTimeout(loop, 45); }
      else { del = false; w = (w + 1) % words.length; setTimeout(loop, 300); }
    };
    loop();
  }

  // Testimonial slider
  const slides = $$('#slider .slide'), dots = $$('#sliderDots button');
  if (slides.length) {
    let cur = 0, timer;
    const go = n => {
      slides[cur].classList.remove('on'); dots[cur].classList.remove('on');
      cur = (n + slides.length) % slides.length;
      slides[cur].classList.add('on'); dots[cur].classList.add('on');
    };
    const auto = () => { clearInterval(timer); timer = setInterval(() => go(cur + 1), 5500); };
    dots.forEach((d, i) => d.addEventListener('click', () => { go(i); auto(); }));
    auto();
  }

  // Portfolio filters
  const filters = $('#filters');
  if (filters) filters.addEventListener('click', e => {
    const b = e.target.closest('button'); if (!b) return;
    $$('button', filters).forEach(x => x.classList.toggle('on', x === b));
    const f = b.dataset.f;
    $$('#projects .project').forEach((p, i) => {
      const show = f === 'all' || p.dataset.cat === f;
      p.classList.toggle('hide', !show);
      if (show) { p.classList.remove('in'); p.style.setProperty('--d', (i % 3) * .08 + 's'); requestAnimationFrame(() => requestAnimationFrame(() => p.classList.add('in'))); }
    });
  });

  // Careers accordion
  $$('.job-head').forEach(h => h.addEventListener('click', () => {
    const job = h.parentElement, open = !job.classList.contains('open');
    $$('.job.open').forEach(j => { j.classList.remove('open'); $('.job-head', j).setAttribute('aria-expanded', 'false'); });
    job.classList.toggle('open', open); h.setAttribute('aria-expanded', open);
  }));
})();
</script>
</body>
</html>

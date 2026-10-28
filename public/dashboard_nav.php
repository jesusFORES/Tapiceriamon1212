<?php
// Simple nav include
require_once __DIR__ . '/../src/auth.php';
start_secure_session();
$user = current_user();
?>
<button class="app-sidebar-toggle" type="button" aria-label="Abrir menú" aria-expanded="false">
  <span></span>
  <span></span>
  <span></span>
</button>

<div class="app-sidebar-backdrop" aria-hidden="true"></div>

<nav class="app-sidebar navbar navbar-light shadow-sm">
  <div class="d-flex flex-column h-100 w-100">
    <a class="navbar-brand" href="/dashboard.php" aria-label="Tapimon inicio">
      <img class="brand-visual" src="/Gemini_Generated_Image_ap7t6tap7t6tap7t_transparent.png" alt="Tapimon logo" />
    </a>

    <div class="nav flex-column px-3 gap-2">
      <?php if (has_permission('view_presupuestos') || has_permission('view_own_presupuestos')): ?>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'presupuestos.php' ? 'active' : ''; ?>" href="/presupuestos.php">Presupuestos</a>
      <?php endif; ?>
      <?php if (has_permission('manage_clients')): ?>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'clientes.php' ? 'active' : ''; ?>" href="/clientes.php">Clientes</a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : ''; ?>" href="/register.php">Registrar</a>
      <?php endif; ?>
      <?php if (has_permission('manage_users')): ?>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : ''; ?>" href="/usuarios.php">Usuarios</a>
      <?php endif; ?>
      <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php">Dashboard</a>
    </div>

    <div class="mt-auto px-3 pb-3 pt-2">
      <div class="app-user-row d-flex align-items-center justify-content-between gap-2">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($user['full_name'] ?? '') ?></div>
          <div class="small text-muted"><?= htmlspecialchars($user['role'] ?? '') ?></div>
        </div>
        <a class="btn btn-outline-secondary btn-sm app-logout-btn" href="/logout.php" title="Cerrar sesión" aria-label="Cerrar sesión">Salir</a>
      </div>
    </div>
  </div>
</nav>

<style>
  body {
    padding-left: 260px;
    background: linear-gradient(180deg, #f5f7fb 0%, #eef3f9 100%);
  }

  .card,
  .table,
  .list-group,
  .alert,
  .form-control,
  .btn,
  .app-sidebar {
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06), 0 2px 8px rgba(15, 23, 42, 0.04);
  }

  .app-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    background: #ffffff;
    border-right: 1px solid rgba(15, 23, 42, 0.08);
    z-index: 1030;
    padding: 0;
  }

  .app-sidebar-toggle {
    display: none;
  }

  .app-sidebar-backdrop {
    display: none;
  }

  .app-sidebar .navbar-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    margin: 0.35rem 0 1rem;
    padding: 0.2rem 0.4rem 0;
    text-align: center;
  }

  .brand-visual {
    display: block;
    width: 100%;
    max-width: 215px;
    height: auto;
    object-fit: contain;
    background: transparent;
    filter: drop-shadow(0 10px 18px rgba(18, 72, 84, 0.12));
  }

  .app-sidebar .nav-link {
    color: #374151;
    border-radius: 10px;
    padding: 0.7rem 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
  }

  .app-sidebar .nav-link:hover,
  .app-sidebar .nav-link.active {
    background: #eef2ff;
    color: #312e81;
  }

  .app-user-row {
    min-height: 52px;
    margin-top: 0;
    position: relative;
    top: -2.2rem;
    border-top: 0;
    overflow: visible;
  }

  .app-logout-btn {
    white-space: nowrap;
    padding: 0.46rem 0.9rem;
    line-height: 1.2;
    border-radius: 9px;
    font-weight: 700;
    transform: translateY(-1px);
    border: 1.5px solid #dc2626;
    color: #b91c1c;
    background: rgba(254, 242, 242, 0.9);
    box-shadow: 0 0 0 1px rgba(220, 38, 38, 0.05);
  }

  .app-logout-btn:hover {
    background: #fee2e2;
    color: #991b1b;
    border-color: #b91c1c;
  }

  .card {
    border: 1px solid rgba(148, 163, 184, 0.18);
    border-radius: 16px;
    background: rgba(255,255,255,0.94);
  }

  .table {
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(148, 163, 184, 0.15);
  }

  .table thead th {
    background: #f8fafc;
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
    font-weight: 600;
  }

  .list-group-item {
    border: 1px solid rgba(148, 163, 184, 0.16);
    border-radius: 12px !important;
    margin-bottom: 0.5rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
  }

  .alert {
    border: none;
    border-radius: 12px;
  }

  .form-control,
  .btn {
    border-radius: 10px;
  }

  @media (max-width: 991.98px) {
    body {
      padding-left: 0;
    }

    .app-sidebar .navbar-brand {
      margin: 0.2rem 0 0.7rem;
      padding-top: 0.25rem;
    }

    .brand-visual {
      max-width: 150px;
    }

    .app-sidebar-toggle {
      position: fixed;
      bottom: 1rem;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1050;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 0.28rem;
      width: 3.2rem;
      height: 3.2rem;
      padding: 0.7rem;
      border: 1px solid rgba(15, 23, 42, 0.08);
      border-radius: 50%;
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(10px);
      box-shadow: 0 12px 26px rgba(15, 23, 42, 0.14);
      cursor: pointer;
      margin-bottom: 0;
    }

    .app-sidebar-toggle span {
      display: block;
      width: 100%;
      height: 2px;
      background: #374151;
      border-radius: 999px;
      transition: transform 0.25s ease, opacity 0.25s ease;
    }

    body.sidebar-open .app-sidebar-toggle span:nth-child(1) {
      transform: translateY(6px) rotate(45deg);
    }

    body.sidebar-open .app-sidebar-toggle span:nth-child(2) {
      opacity: 0;
    }

    body.sidebar-open .app-sidebar-toggle span:nth-child(3) {
      transform: translateY(-6px) rotate(-45deg);
    }

    .app-sidebar-backdrop {
      position: fixed;
      inset: 0;
      display: block;
      background: rgba(15, 23, 42, 0.45);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
      z-index: 1029;
    }

    body.sidebar-open .app-sidebar-backdrop {
      opacity: 1;
      pointer-events: auto;
    }

    .app-sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: min(82vw, 300px);
      height: 100vh;
      border-right: 1px solid rgba(15, 23, 42, 0.08);
      transform: translateX(-105%);
      transition: transform 0.25s ease;
      z-index: 1030;
    }

    body.sidebar-open .app-sidebar {
      transform: translateX(0);
    }

    .app-sidebar .nav {
      flex-direction: column;
      flex-wrap: nowrap;
      padding-bottom: 1rem;
    }
  }
</style>

<script>
  (() => {
    const toggle = document.querySelector('.app-sidebar-toggle');
    const sidebar = document.querySelector('.app-sidebar');
    const backdrop = document.querySelector('.app-sidebar-backdrop');

    if (!toggle || !sidebar || !backdrop) {
      return;
    }

    const setSidebarState = (isOpen) => {
      document.body.classList.toggle('sidebar-open', isOpen);
      toggle.setAttribute('aria-expanded', String(isOpen));
      toggle.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
    };

    toggle.addEventListener('click', () => {
      const isOpen = document.body.classList.contains('sidebar-open');
      setSidebarState(!isOpen);
    });

    backdrop.addEventListener('click', () => setSidebarState(false));

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
        setSidebarState(false);
      }
    });
  })();
</script>

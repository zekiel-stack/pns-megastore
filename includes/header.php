<?php
/**
 * PNS Mega Store — Common Header & Sidebar
 *
 * Include at the top of every page.
 * Set $page_title and $current_page before including.
 */

if (!isset($page_title))   $page_title   = 'PNS Mega Store';
if (!isset($current_page)) $current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PNS Mega Store — Inventory Management System">
  <title><?php echo htmlspecialchars($page_title); ?> | PNS Mega Store</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Mobile Header -->
<div class="mobile-header">
  <button class="menu-toggle" onclick="toggleSidebar()">
    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
    </svg>
  </button>
  <span style="font-weight:700;color:var(--text-heading);font-size:0.95rem;">PNS Mega Store</span>
  <span></span>
</div>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="brand-icon">P</div>
      <div class="brand-text">
        <span class="brand-name">PNS Mega Store</span>
        <span class="brand-sub">Inventory System</span>
      </div>
    </div>

    <nav class="sidebar-nav">
      <span class="nav-label">Main</span>

      <a href="index.php" class="nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z
               M3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z
               M13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z
               M13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
        </svg>
        Dashboard
      </a>

      <a href="inventory.php" class="nav-link <?php echo $current_page === 'inventory' ? 'active' : ''; ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5
               M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375
               c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
        </svg>
        Inventory
      </a>

      <a href="add_item.php" class="nav-link <?php echo $current_page === 'add_item' ? 'active' : ''; ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Add Item
      </a>

      <span class="nav-label" style="margin-top:0.5rem;">Insights</span>

      <a href="analytics.php" class="nav-link <?php echo $current_page === 'analytics' ? 'active' : ''; ?>">
        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25
               A1.125 1.125 0 013 19.875v-6.75z
               M9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25
               a1.125 1.125 0 01-1.125-1.125V8.625z
               M16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25
               a1.125 1.125 0 01-1.125-1.125V4.125z"/>
        </svg>
        Analytics
      </a>
    </nav>

    <div class="sidebar-footer">
      &copy; <?php echo date('Y'); ?> PNS Mega Store
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

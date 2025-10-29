<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-logo">
  <i class="bi bi-server text-primary"></i> UltraServe
  </div>

  <a href="dashboard.php?page=dashboard" class="<?= $page=='dashboard'?'active':'' ?>"><i class="bi bi-house-door me-2"></i> Dashboard</a>
  <a href="dashboard.php?page=hosting_domain" class="<?= $page=='hosting_domain'?'active':'' ?>"><i class="bi bi-globe me-2"></i> Hosting & Domain</a>

  <a href="hosting-domain-form.php"><i class="bi bi-plus-circle me-2"></i> Add Hosting & Domain</a>
  <a href="import.php "><i class="bi bi-file-earmark-arrow-up me-2"></i> Import</a>
  <a href="export.php "><i class="bi bi-file-earmark-arrow-down me-2"></i> Export</a>
  <a href="dashboard.php?page=calendar"><i class="bi bi-calendar3 me-2"></i> Calendar View</a>

  <div class="dropdown">
  <a class="dropdown-toggle <?= in_array($page, [
        'upgrade_plan',
        'upgrade_billing',
        'upgrade_history'
      ]) ? 'active' : '' ?>"
     href="#"
     role="button"
     id="upgradeDropdown"
     data-bs-toggle="dropdown"
     aria-expanded="false">
    <i class="bi bi-rocket-takeoff me-2  "></i> Upgrade
  </a>

  <ul class="dropdown-menu" aria-labelledby="upgradeDropdown">
    <li>
      <a class="dropdown-item "
         href="upgrade.php">
        <i class="bi bi-graph-up-arrow me-2"></i> Upgrade Plan
      </a>
    </li>

    <li>
      <a class="dropdown-item <?= $page=='payment_methods'?'active':'' ?>"
         href="dashboard.php?page=payment_methods">
        <i class="bi bi-currency-rupee"></i> Payment Methods
      </a>
    </li>

    <li>
      <a class="dropdown-item  "
         href="upgrade-history.php">
        <i class="bi bi-clock-history"></i> Upgrade History
      </a>
    </li>
  </ul>
</div>


  <a href="dashboard.php?page=theme"><i class="bi bi-palette me-2"></i> Theme & Layout</a>
  <a href="dashboard.php?page=help"><i class="bi bi-question-circle me-2"></i> Help Center</a>
  <a href="dashboard.php?page=system_info"><i class="bi bi-info-circle me-2"></i> System Info</a>

  <a href="dashboard.php?page=profile" class="<?= $page=='profile'?'active':'' ?>"><i class="bi bi-person me-2"></i> Profile</a>

  <div class="dropdown">
    <a class="dropdown-toggle <?= in_array($page, ['settings_general', 'settings_branding', 'settings_backup', 'settings_logs', 'settings_retention'])?'active':'' ?>"
       href="#"
       role="button"
       id="settingsDropdown"
       data-bs-toggle="dropdown"
       aria-expanded="false">
      <i class="bi bi-gear me-2"></i> Settings

    </a>
    <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
      <li>
        <a class="dropdown-item <?= $page=='settings_general'?'active':'' ?>" href="dashboard.php?page=settings_general">
          <i class="bi bi-sliders me-2"></i> General Settings
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_branding'?'active':'' ?>" href="dashboard.php?page=settings_branding">
          <i class="bi bi-palette-fill me-2"></i> Logo & Branding
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_backup'?'active':'' ?>" href="dashboard.php?page=settings_backup">
          <i class="bi bi-cloud-arrow-up me-2"></i> Auto-Backup Settings
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_logs'?'active':'' ?>" href="dashboard.php?page=settings_logs">
          <i class="bi bi-journal-text me-2"></i> System Logs
        </a>
      </li>
      <li>
        <a class="dropdown-item <?= $page=='settings_retention'?'active':'' ?>" href="dashboard.php?page=settings_retention">
          <i class="bi bi-database me-2"></i> Data Retention Policy
        </a>
      </li>
    </ul>
  </div>

  <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
    <i class="bi bi-box-arrow-right me-2"></i> Logout
  </a>

</div>
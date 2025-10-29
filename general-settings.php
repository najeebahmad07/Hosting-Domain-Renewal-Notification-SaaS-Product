<!-- General Settings -->
<?php if($page=='settings_general'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-sliders me-2"></i> General Settings</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="general">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Timezone</label>
          <select class="form-select" name="timezone">
            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
            <option value="America/New_York">America/New_York (EST)</option>
            <option value="Europe/London">Europe/London (GMT)</option>
            <option value="Asia/Dubai">Asia/Dubai (GST)</option>
            <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Currency</label>
          <select class="form-select" name="currency">
            <option value="INR">INR - Indian Rupee (₹)</option>
            <option value="USD">USD - US Dollar ($)</option>
            <option value="EUR">EUR - Euro (€)</option>
            <option value="GBP">GBP - British Pound (£)</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Date Format</label>
        <select class="form-select" name="date_format">
          <option value="Y-m-d">YYYY-MM-DD (2024-01-15)</option>
          <option value="d-m-Y">DD-MM-YYYY (15-01-2024)</option>
          <option value="m/d/Y">MM/DD/YYYY (01/15/2024)</option>
          <option value="d M Y">DD Mon YYYY (15 Jan 2024)</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Settings</button>
    </form>
  </div>
</div>
<?php } ?>

<!-- Logo & Branding -->
<?php if($page=='settings_branding'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-palette-fill me-2"></i> Logo & Branding Customization</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php" enctype="multipart/form-data">
      <input type="hidden" name="settings_type" value="branding">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Company Name</label>
        <input type="text" class="form-control" name="company_name" placeholder="UltraServe" value="UltraServe">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Upload Logo</label>
        <input type="file" class="form-control" name="logo" accept="image/*">
        <small class="text-muted">Recommended size: 200x50px (PNG, JPG, SVG)</small>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Primary Color</label>
          <input type="color" class="form-control form-control-color w-100" name="primary_color" value="#3b82f6">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Secondary Color</label>
          <input type="color" class="form-control form-control-color w-100" name="secondary_color" value="#8b5cf6">
        </div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Branding</button>
    </form>
  </div>
</div>
<?php } ?>

<!-- Auto-Backup Settings -->
<?php if($page=='settings_backup'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-cloud-arrow-up me-2"></i> Auto-Backup Settings</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="backup">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="enableBackup" name="enable_backup" checked>
          <label class="form-check-label fw-semibold" for="enableBackup">Enable Auto-Backup</label>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Backup Frequency</label>
          <select class="form-select" name="backup_frequency">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Backup Time</label>
          <input type="time" class="form-control" name="backup_time" value="02:00">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Backup Location</label>
        <input type="text" class="form-control" name="backup_location" placeholder="/var/backups/" value="/backups/">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Keep Backups For</label>
        <select class="form-select" name="backup_retention">
          <option value="7">7 Days</option>
          <option value="30" selected>30 Days</option>
          <option value="90">90 Days</option>
          <option value="365">1 Year</option>
        </select>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Backup Settings</button>
        <button type="button" class="btn btn-success"><i class="bi bi-cloud-upload me-2"></i> Backup Now</button>
      </div>
    </form>
  </div>
</div>
<?php } ?>

<!-- System Logs -->
<?php if($page=='settings_logs'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-journal-text me-2"></i> System Logs</div>
    <button class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i> Clear All Logs</button>
  </div>
  <div style="overflow-x: auto;">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Admin</th>
          <th>Action</th>
          <th>Table</th>
          <th>Record ID</th>
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $logs_result = mysqli_query($conn, "SELECT * FROM activity_logs WHERE admin_id = $admin_id ORDER BY created_at DESC LIMIT 100");
        if(mysqli_num_rows($logs_result) > 0){
          while($log = mysqli_fetch_assoc($logs_result)){
        ?>
        <tr>
          <td><?= $log['id'] ?></td>
          <td><?= htmlspecialchars($log['admin_name']) ?></td>
          <td><span class="badge bg-info"><?= $log['action'] ?></span></td>
          <td><?= $log['table_name'] ?></td>
          <td><?= $log['record_id'] ?></td>
          <td><?= $log['created_at'] ?></td>
        </tr>
        <?php
          }
        } else {
        ?>
        <tr><td colspan="6" class="text-center">No logs found</td></tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

<!-- Data Retention Policy -->
<?php if($page=='settings_retention') ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-database me-2"></i> Data Retention Policy</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="retention">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Records Older Than</label>
        <select class="form-select" name="retention_period">
          <option value="never">Never Delete</option>
          <option value="180">6 Months</option>
          <option value="365" selected>1 Year</option>
          <option value="730">2 Years</option>
          <option value="1825">5 Years</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Activity Logs Older Than</label>
        <select class="form-select" name="logs_retention">
          <option value="30">30 Days</option>
          <option value="90" selected>90 Days</option>
          <option value="180">6 Months</option>
          <option value="365">1 Year</option>
        </select>
      </div>

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="autoDelete" name="auto_delete" checked>
          <label class="form-check-label fw-semibold" for="autoDelete">Enable Automatic Deletion</label>

          <!-- Data Retention Policy -->
<?php if($page=='settings_retention'){ ?>
<div class="table-container">
  <div class="table-header">
    <div class="table-title"><i class="bi bi-database me-2"></i> Data Retention Policy</div>
  </div>
  <div style="padding: 20px 24px;">
    <form method="POST" action="save_settings.php">
      <input type="hidden" name="settings_type" value="retention">
      <input type="hidden" name="admin_id" value="<?= $admin_id ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Expired Domains Older Than</label>
        <select class="form-select" name="retention_period">
          <option value="never">Never Delete</option>
          <option value="90">3 Months after expiry</option>
          <option value="180">6 Months after expiry</option>
          <option value="365" selected>1 Year after expiry</option>
          <option value="730">2 Years after expiry</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Delete Activity Logs Older Than</label>
        <select class="form-select" name="logs_retention">
          <option value="30">30 Days</option>
          <option value="90" selected>90 Days</option>
          <option value="180">6 Months</option>
          <option value="365">1 Year</option>
        </select>
      </div>

      <div class="mb-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="autoDelete" name="auto_delete" checked>
          <label class="form-check-label fw-semibold" for="autoDelete">Enable Automatic Deletion</label>
        </div>
        <small class="text-muted">Automatically delete records based on retention policy</small>
      </div>

      <div class="mb-4">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="deleteExpired" name="delete_expired">
          <label class="form-check-label fw-semibold" for="deleteExpired">Auto-delete Expired Domains</label>
        </div>
        <small class="text-muted">Automatically remove expired domain records</small>
      </div>

      <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Warning:</strong> Deleted data cannot be recovered. Make sure you have backups enabled.
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i> Save Retention Policy</button>
    </form>
  </div>
</div>
<?php } ?>
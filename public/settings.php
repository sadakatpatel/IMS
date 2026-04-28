<?php
session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Helper.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$auth = new AuthController();
$auth->checkSessionTimeout();
$auth->requireAdmin();

$db = Database::getInstance();

// Get current settings
$settings = $db->fetchAll("SELECT * FROM settings");
$settingsArray = [];
foreach ($settings as $setting) {
    $settingsArray[$setting['setting_key']] = $setting['setting_value'];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'csrf_token') {
            $value = Helper::sanitize($value);
            
            // Check if setting exists
            $exists = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$key], 's');
            
            if ($exists) {
                $db->execute("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$value, $key], 'ss');
            } else {
                $db->execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value], 'ss');
            }
        }
    }
    
    $message = 'Settings updated successfully';
}

$pageTitle = 'Settings';
?>

<?php include __DIR__ . '/../views/layouts/header.php'; ?>

<div class="page-header mb-4">
    <h1 class="h2"><i class="fas fa-cog"></i> Settings</h1>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Settings Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general">General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="financial-tab" data-bs-toggle="tab" href="#financial">Financial</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="system-tab" data-bs-toggle="tab" href="#system">System</a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <!-- General Settings -->
    <div class="tab-pane fade show active" id="general">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> General Settings</h5>
            </div>
            <form method="POST" action="">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="company_name" 
                                       value="<?php echo $settingsArray['company_name'] ?? 'Inventory Management System'; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Company Email</label>
                                <input type="email" class="form-control" name="company_email" 
                                       value="<?php echo $settingsArray['company_email'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Company Phone</label>
                                <input type="tel" class="form-control" name="company_phone" 
                                       value="<?php echo $settingsArray['company_phone'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Company Address</label>
                                <input type="text" class="form-control" name="company_address" 
                                       value="<?php echo $settingsArray['company_address'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Company Logo</label>
                        <input type="text" class="form-control" name="company_logo" 
                               placeholder="Path to logo image" 
                               value="<?php echo $settingsArray['company_logo'] ?? ''; ?>">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Financial Settings -->
    <div class="tab-pane fade" id="financial">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Financial Settings</h5>
            </div>
            <form method="POST" action="">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="USD" <?php echo (isset($settingsArray['currency']) && $settingsArray['currency'] === 'USD') ? 'selected' : ''; ?>>USD ($)</option>
                                    <option value="EUR" <?php echo (isset($settingsArray['currency']) && $settingsArray['currency'] === 'EUR') ? 'selected' : ''; ?>>EUR (€)</option>
                                    <option value="GBP" <?php echo (isset($settingsArray['currency']) && $settingsArray['currency'] === 'GBP') ? 'selected' : ''; ?>>GBP (£)</option>
                                    <option value="INR" <?php echo (isset($settingsArray['currency']) && $settingsArray['currency'] === 'INR') ? 'selected' : ''; ?>>INR (₹)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control" name="tax_rate" step="0.01" 
                                       value="<?php echo $settingsArray['tax_rate'] ?? '10'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Default Payment Terms</label>
                                <input type="text" class="form-control" name="payment_terms" 
                                       placeholder="e.g., Net 30" 
                                       value="<?php echo $settingsArray['payment_terms'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fiscal Year End</label>
                                <input type="text" class="form-control" name="fiscal_year_end" 
                                       placeholder="e.g., 12-31" 
                                       value="<?php echo $settingsArray['fiscal_year_end'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- System Settings -->
    <div class="tab-pane fade" id="system">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-cogs"></i> System Settings</h5>
            </div>
            <form method="POST" action="">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" class="form-control" name="low_stock_threshold" 
                                       value="<?php echo $settingsArray['low_stock_threshold'] ?? '10'; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Session Timeout (minutes)</label>
                                <input type="number" class="form-control" name="session_timeout" 
                                       value="<?php echo $settingsArray['session_timeout'] ?? '30'; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Enable Notifications</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_notifications" 
                                   value="1" id="notifCheck"
                                   <?php echo (isset($settingsArray['enable_notifications']) && $settingsArray['enable_notifications'] === '1') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="notifCheck">
                                Enable email notifications for low stock and orders
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Backup Frequency</label>
                        <select class="form-select" name="backup_frequency">
                            <option value="daily" <?php echo (isset($settingsArray['backup_frequency']) && $settingsArray['backup_frequency'] === 'daily') ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo (isset($settingsArray['backup_frequency']) && $settingsArray['backup_frequency'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                            <option value="monthly" <?php echo (isset($settingsArray['backup_frequency']) && $settingsArray['backup_frequency'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        </select>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>System Information:</strong><br>
                        PHP Version: <?php echo phpversion(); ?><br>
                        MySQL Version: <?php echo mysqli_get_server_info($db->getConnection()); ?><br>
                        Timezone: <?php echo date_default_timezone_get(); ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- System Actions -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-database"></i> Database Actions</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Manage your database backups</p>
                <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Download current database backup">
                    <i class="fas fa-download"></i> Backup Database
                </a>
                <a href="#" class="btn btn-sm btn-info">
                    <i class="fas fa-upload"></i> Restore Backup
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Irreversible actions</p>
                <button class="btn btn-sm btn-danger" onclick="if(confirm('This will clear all activity logs. Continue?')) alert('Logs cleared')">
                    <i class="fas fa-trash"></i> Clear Activity Logs
                </button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../views/layouts/footer.php'; ?>

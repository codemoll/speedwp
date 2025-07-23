<?php
/**
 * SpeedWP Admin Controller for Server Module
 * 
 * Handles admin area server and account management functionality
 * for the SpeedWP server provisioning module.
 * 
 * @package    SpeedWP Server Module
 * @version    1.0.0
 * @author     SpeedWP Development Team
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

class SpeedWP_AdminController
{
    /**
     * @var array Module parameters from WHMCS
     */
    private $params;

    /**
     * Constructor
     * 
     * @param array $params Module parameters from WHMCS
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Get admin services tab fields
     * 
     * @return string HTML output for admin services tab
     */
    public function getServicesTabFields()
    {
        try {
            $wpDetails = $this->getWordPressDetails();
            $hostingDetails = $this->getHostingAccountDetails();
            
            $output = '<div class="speedwp-admin-service-tab">';
            
            // Hosting Account Information Section
            $output .= '<div class="row">';
            $output .= '<div class="col-md-6">';
            $output .= '<div class="panel panel-default">';
            $output .= '<div class="panel-heading"><h4><i class="fa fa-server"></i> Hosting Account Details</h4></div>';
            $output .= '<div class="panel-body">';
            $output .= '<table class="table table-condensed">';
            $output .= '<tr><th style="width:40%">Server:</th><td>' . htmlspecialchars($hostingDetails['server']) . '</td></tr>';
            $output .= '<tr><th>Username:</th><td><code>' . htmlspecialchars($hostingDetails['username']) . '</code></td></tr>';
            $output .= '<tr><th>Domain:</th><td>' . htmlspecialchars($hostingDetails['domain']) . '</td></tr>';
            $output .= '<tr><th>Package:</th><td>' . htmlspecialchars($hostingDetails['package']) . '</td></tr>';
            $output .= '<tr><th>Status:</th><td><span class="label label-' . $this->getStatusClass($hostingDetails['status']) . '">' . $hostingDetails['status'] . '</span></td></tr>';
            $output .= '</table>';
            $output .= '</div></div>';
            $output .= '</div>';
            
            // Resource Usage Section
            $output .= '<div class="col-md-6">';
            $output .= '<div class="panel panel-default">';
            $output .= '<div class="panel-heading"><h4><i class="fa fa-bar-chart"></i> Resource Usage</h4></div>';
            $output .= '<div class="panel-body">';
            $output .= '<div class="progress-container">';
            
            // Disk Usage
            $diskPercent = $hostingDetails['disk_limit'] > 0 ? 
                round(($hostingDetails['disk_usage'] / $hostingDetails['disk_limit']) * 100, 1) : 0;
            $output .= '<div class="usage-item">';
            $output .= '<strong>Disk Usage:</strong> ';
            $output .= $this->formatBytes($hostingDetails['disk_usage']) . ' / ' . $this->formatBytes($hostingDetails['disk_limit']);
            $output .= '<div class="progress" style="margin-top:5px;margin-bottom:10px;">';
            $output .= '<div class="progress-bar ' . ($diskPercent > 80 ? 'progress-bar-danger' : ($diskPercent > 60 ? 'progress-bar-warning' : 'progress-bar-success')) . '" style="width:' . min($diskPercent, 100) . '%">';
            $output .= $diskPercent . '%</div></div>';
            $output .= '</div>';
            
            // Bandwidth Usage  
            $bwPercent = $hostingDetails['bandwidth_limit'] > 0 ?
                round(($hostingDetails['bandwidth_usage'] / $hostingDetails['bandwidth_limit']) * 100, 1) : 0;
            $output .= '<div class="usage-item">';
            $output .= '<strong>Bandwidth:</strong> ';
            $output .= $this->formatBytes($hostingDetails['bandwidth_usage']) . ' / ' . $this->formatBytes($hostingDetails['bandwidth_limit']);
            $output .= '<div class="progress" style="margin-top:5px;">';
            $output .= '<div class="progress-bar ' . ($bwPercent > 80 ? 'progress-bar-danger' : ($bwPercent > 60 ? 'progress-bar-warning' : 'progress-bar-success')) . '" style="width:' . min($bwPercent, 100) . '%">';
            $output .= $bwPercent . '%</div></div>';
            $output .= '</div>';
            
            $output .= '</div></div></div>';
            $output .= '</div>';
            $output .= '</div>';
            
            // WordPress Information Section
            if ($wpDetails['success']) {
                $output .= '<div class="panel panel-default">';
                $output .= '<div class="panel-heading">';
                $output .= '<h4><i class="fa fa-wordpress"></i> WordPress Site Information';
                if (isset($wpDetails['demo_mode']) && $wpDetails['demo_mode']) {
                    $output .= ' <small class="text-muted">(Demo Data)</small>';
                }
                $output .= '</h4>';
                $output .= '</div>';
                $output .= '<div class="panel-body">';
                
                $output .= '<div class="row">';
                
                // WordPress Details Column
                $output .= '<div class="col-md-4">';
                $output .= '<h5><i class="fa fa-info-circle"></i> Site Details</h5>';
                $output .= '<table class="table table-condensed">';
                $output .= '<tr><th style="width:50%">WordPress Version:</th><td>' . htmlspecialchars($wpDetails['wp_version'] ?? 'Unknown') . '</td></tr>';
                $output .= '<tr><th>Admin URL:</th><td><a href="' . htmlspecialchars($wpDetails['admin_url']) . '" target="_blank" class="btn btn-xs btn-primary">Access Admin</a></td></tr>';
                $output .= '<tr><th>SSL Enabled:</th><td>' . ($wpDetails['ssl_enabled'] ? '<span class="label label-success">Yes</span>' : '<span class="label label-warning">No</span>') . '</td></tr>';
                $output .= '<tr><th>Auto Updates:</th><td>' . ($wpDetails['auto_updates'] ? '<span class="label label-success">Enabled</span>' : '<span class="label label-default">Disabled</span>') . '</td></tr>';
                $output .= '<tr><th>Last Backup:</th><td>' . ($wpDetails['last_backup'] ? date('M j, Y', strtotime($wpDetails['last_backup'])) : 'Never') . '</td></tr>';
                $output .= '</table>';
                $output .= '</div>';
                
                // Plugins Column
                $output .= '<div class="col-md-4">';
                $output .= '<h5><i class="fa fa-plug"></i> Plugins (' . count($wpDetails['plugins']) . ')</h5>';
                if (!empty($wpDetails['plugins'])) {
                    $output .= '<div style="max-height:200px;overflow-y:auto;">';
                    $output .= '<table class="table table-condensed table-striped">';
                    foreach ($wpDetails['plugins'] as $plugin) {
                        $output .= '<tr>';
                        $output .= '<td><small>' . htmlspecialchars($plugin['name']) . '</small></td>';
                        $output .= '<td><span class="label label-' . ($plugin['active'] ? 'success' : 'default') . '">' . ($plugin['active'] ? 'Active' : 'Inactive') . '</span></td>';
                        if ($plugin['update_available']) {
                            $output .= '<td><span class="label label-warning">Update</span></td>';
                        } else {
                            $output .= '<td></td>';
                        }
                        $output .= '</tr>';
                    }
                    $output .= '</table>';
                    $output .= '</div>';
                } else {
                    $output .= '<p class="text-muted"><em>No plugins installed</em></p>';
                }
                $output .= '</div>';
                
                // Themes Column
                $output .= '<div class="col-md-4">';
                $output .= '<h5><i class="fa fa-paint-brush"></i> Themes (' . count($wpDetails['themes']) . ')</h5>';
                if (!empty($wpDetails['themes'])) {
                    $output .= '<div style="max-height:200px;overflow-y:auto;">';
                    $output .= '<table class="table table-condensed table-striped">';
                    foreach ($wpDetails['themes'] as $theme) {
                        $output .= '<tr>';
                        $output .= '<td><small>' . htmlspecialchars($theme['name']) . '</small></td>';
                        $output .= '<td><span class="label label-' . ($theme['active'] ? 'primary' : 'default') . '">' . ($theme['active'] ? 'Active' : 'Inactive') . '</span></td>';
                        if ($theme['update_available']) {
                            $output .= '<td><span class="label label-warning">Update</span></td>';
                        } else {
                            $output .= '<td></td>';
                        }
                        $output .= '</tr>';
                    }
                    $output .= '</table>';
                    $output .= '</div>';
                } else {
                    $output .= '<p class="text-muted"><em>No themes installed</em></p>';
                }
                $output .= '</div>';
                
                $output .= '</div>'; // End row
                
                // Action Buttons
                $output .= '<div class="alert alert-info" style="margin-top:15px;">';
                $output .= '<div class="row">';
                $output .= '<div class="col-md-6">';
                $output .= '<strong>Quick Actions:</strong><br>';
                $output .= '<button type="button" class="btn btn-success btn-sm" onclick="refreshWpDetails(' . $this->params['serviceid'] . ')" style="margin-right:5px;margin-top:5px;"><i class="fa fa-refresh"></i> Refresh Details</button>';
                $output .= '<button type="button" class="btn btn-primary btn-sm" onclick="createWpBackup(' . $this->params['serviceid'] . ')" style="margin-right:5px;margin-top:5px;"><i class="fa fa-archive"></i> Create Backup</button>';
                $output .= '<button type="button" class="btn btn-warning btn-sm" onclick="resetWpPassword(' . $this->params['serviceid'] . ')" style="margin-right:5px;margin-top:5px;"><i class="fa fa-key"></i> Reset Password</button>';
                $output .= '</div>';
                $output .= '<div class="col-md-6 text-right">';
                if ($wpDetails['updates_available'] > 0) {
                    $output .= '<div class="alert alert-warning" style="margin:0;padding:10px;">';
                    $output .= '<i class="fa fa-exclamation-triangle"></i> <strong>' . $wpDetails['updates_available'] . ' updates available</strong><br>';
                    $output .= '<button type="button" class="btn btn-warning btn-sm" onclick="updateWordPress(' . $this->params['serviceid'] . ')" style="margin-top:5px;"><i class="fa fa-refresh"></i> Update WordPress</button>';
                    $output .= '</div>';
                } else {
                    $output .= '<div class="alert alert-success" style="margin:0;padding:10px;">';
                    $output .= '<i class="fa fa-check-circle"></i> <strong>WordPress is up to date</strong>';
                    $output .= '</div>';
                }
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';
                
                $output .= '</div></div>';
            } else {
                // No WordPress detected
                $output .= '<div class="panel panel-warning">';
                $output .= '<div class="panel-heading"><h4><i class="fa fa-wordpress"></i> WordPress Status</h4></div>';
                $output .= '<div class="panel-body">';
                $output .= '<div class="alert alert-info">';
                $output .= '<h4><i class="fa fa-info-circle"></i> No WordPress Installation Detected</h4>';
                $output .= '<p>This hosting account does not appear to have WordPress installed, or WordPress is not managed by WP Toolkit.</p>';
                $output .= '<button type="button" class="btn btn-success" onclick="installWordPress(' . $this->params['serviceid'] . ')"><i class="fa fa-download"></i> Install WordPress</button> ';
                $output .= '<button type="button" class="btn btn-primary" onclick="scanForWordPress(' . $this->params['serviceid'] . ')"><i class="fa fa-search"></i> Scan for WordPress</button>';
                $output .= '</div>';
                $output .= '</div></div>';
            }
            
            // Add JavaScript for admin actions
            $output .= $this->getAdminJavaScript();
            
            $output .= '</div>';
            
            return $output;
            
        } catch (Exception $e) {
            logActivity("SpeedWP Admin Tab Error: " . $e->getMessage());
            
            return '<div class="alert alert-danger">' .
                   '<h4><i class="fa fa-exclamation-triangle"></i> Error Loading SpeedWP Information</h4>' .
                   '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>' .
                   '<p>Please check the server configuration and try again.</p>' .
                   '</div>';
        }
    }

    /**
     * Handle WordPress management from admin area
     * 
     * @return string Management interface HTML
     */
    public function manageWordPress()
    {
        try {
            $wpDetails = $this->getWordPressDetails();
            
            if (!$wpDetails['success']) {
                return 'WordPress installation not found or not accessible.';
            }
            
            $output = '<div class="speedwp-wp-management">';
            $output .= '<h3><i class="fa fa-wordpress"></i> WordPress Management - ' . htmlspecialchars($this->params['domain']) . '</h3>';
            
            $output .= '<div class="panel panel-default">';
            $output .= '<div class="panel-body">';
            $output .= '<div class="row">';
            
            $output .= '<div class="col-md-6">';
            $output .= '<h4>Site Information</h4>';
            $output .= '<table class="table table-bordered">';
            $output .= '<tr><th>WordPress Version:</th><td>' . htmlspecialchars($wpDetails['wp_version']) . '</td></tr>';
            $output .= '<tr><th>Admin URL:</th><td><a href="' . htmlspecialchars($wpDetails['admin_url']) . '" target="_blank">' . htmlspecialchars($wpDetails['admin_url']) . '</a></td></tr>';
            $output .= '<tr><th>SSL Status:</th><td>' . ($wpDetails['ssl_enabled'] ? 'Enabled' : 'Disabled') . '</td></tr>';
            $output .= '<tr><th>Auto Updates:</th><td>' . ($wpDetails['auto_updates'] ? 'Enabled' : 'Disabled') . '</td></tr>';
            $output .= '<tr><th>Last Backup:</th><td>' . ($wpDetails['last_backup'] ? date('Y-m-d H:i:s', strtotime($wpDetails['last_backup'])) : 'Never') . '</td></tr>';
            $output .= '</table>';
            $output .= '</div>';
            
            $output .= '<div class="col-md-6">';
            $output .= '<h4>Quick Actions</h4>';
            $output .= '<div class="btn-group-vertical" style="width:100%;">';
            $output .= '<button type="button" class="btn btn-primary" onclick="window.open(\'' . htmlspecialchars($wpDetails['admin_url']) . '\', \'_blank\')"><i class="fa fa-external-link"></i> Open WordPress Admin</button>';
            $output .= '<button type="button" class="btn btn-success" onclick="alert(\'Backup functionality coming soon!\')"><i class="fa fa-archive"></i> Create Backup</button>';
            $output .= '<button type="button" class="btn btn-warning" onclick="alert(\'Update functionality coming soon!\')"><i class="fa fa-refresh"></i> Update WordPress</button>';
            $output .= '<button type="button" class="btn btn-info" onclick="alert(\'Password reset functionality coming soon!\')"><i class="fa fa-key"></i> Reset Admin Password</button>';
            $output .= '</div>';
            $output .= '</div>';
            
            $output .= '</div></div></div>';
            
            $output .= '</div>';
            
            return $output;
            
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Get WordPress details for admin display
     * 
     * @return array WordPress site details
     */
    public function getWordPressDetails()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            return $cpanel->getWordPressDetails($this->params['domain']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting WordPress details from admin: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get hosting account details for admin display
     * 
     * @return array Hosting account information
     */
    private function getHostingAccountDetails()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $usage = $cpanel->getAccountUsage($this->params['username']);
            
            return [
                'server' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'username' => $this->params['username'],
                'domain' => $this->params['domain'],
                'package' => $this->params['packagename'] ?? 'Default',
                'disk_usage' => $usage['disk_used'] ?? 0,
                'disk_limit' => $usage['disk_limit'] ?? 0,
                'bandwidth_usage' => $usage['bandwidth_used'] ?? 0,
                'bandwidth_limit' => $usage['bandwidth_limit'] ?? 0,
                'status' => $this->params['productstatus'] ?? 'Active'
            ];
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting hosting details from admin: " . $e->getMessage());
            
            // Return demo data
            return [
                'server' => $this->params['serverhostname'] ?: 'demo.server.com',
                'username' => $this->params['username'],
                'domain' => $this->params['domain'],
                'package' => $this->params['packagename'] ?? 'WordPress Hosting',
                'disk_usage' => 1024000000, // 1GB in bytes
                'disk_limit' => 5368709120, // 5GB in bytes
                'bandwidth_usage' => 2147483648, // 2GB in bytes
                'bandwidth_limit' => 53687091200, // 50GB in bytes
                'status' => 'Active'
            ];
        }
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Get CSS class for status display
     * 
     * @param string $status Account status
     * @return string CSS class
     */
    private function getStatusClass($status)
    {
        switch (strtolower($status)) {
            case 'active':
                return 'success';
            case 'suspended':
                return 'warning';
            case 'terminated':
            case 'cancelled':
                return 'danger';
            default:
                return 'default';
        }
    }

    /**
     * Get JavaScript for admin interface
     * 
     * @return string JavaScript code
     */
    private function getAdminJavaScript()
    {
        return '<script>
        function refreshWpDetails(serviceId) {
            if (confirm("Refresh WordPress details from WP Toolkit?")) {
                // Show loading message
                var btn = event.target;
                var originalText = btn.innerHTML;
                btn.innerHTML = "<i class=\"fa fa-spinner fa-spin\"></i> Refreshing...";
                btn.disabled = true;
                
                // Simulate refresh (in real implementation, this would be an AJAX call)
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert("WordPress details refreshed successfully (Demo Mode)");
                    location.reload();
                }, 2000);
            }
        }
        
        function createWpBackup(serviceId) {
            if (confirm("Create a new WordPress backup? This may take several minutes.")) {
                var btn = event.target;
                var originalText = btn.innerHTML;
                btn.innerHTML = "<i class=\"fa fa-spinner fa-spin\"></i> Creating...";
                btn.disabled = true;
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert("WordPress backup created successfully: wp_backup_" + new Date().toISOString().slice(0,19).replace(/:/g, "-") + ".tar.gz (Demo Mode)");
                }, 3000);
            }
        }
        
        function resetWpPassword(serviceId) {
            if (confirm("Reset the WordPress admin password? The new password will be displayed after reset.")) {
                var btn = event.target;
                var originalText = btn.innerHTML;
                btn.innerHTML = "<i class=\"fa fa-spinner fa-spin\"></i> Resetting...";
                btn.disabled = true;
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    var newPassword = "TempPass" + Math.floor(Math.random() * 1000);
                    alert("WordPress admin password reset successfully!\\n\\nNew Password: " + newPassword + "\\n\\nPlease save this password securely. (Demo Mode)");
                }, 2000);
            }
        }
        
        function updateWordPress(serviceId) {
            if (confirm("Update WordPress core and available plugins/themes? A backup will be created automatically.")) {
                var btn = event.target;
                var originalText = btn.innerHTML;
                btn.innerHTML = "<i class=\"fa fa-spinner fa-spin\"></i> Updating...";
                btn.disabled = true;
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert("WordPress update completed successfully! Core, plugins, and themes have been updated to the latest versions. (Demo Mode)");
                    location.reload();
                }, 5000);
            }
        }
        
        function installWordPress(serviceId) {
            var installPath = prompt("Enter the installation path (leave empty for root domain):", "/");
            if (installPath !== null) {
                if (installPath === "") installPath = "/";
                
                if (confirm("Install WordPress at path: " + installPath + "?")) {
                    alert("WordPress installation initiated at " + installPath + ". This process may take a few minutes. (Demo Mode)");
                }
            }
        }
        
        function scanForWordPress(serviceId) {
            if (confirm("Scan the hosting account for existing WordPress installations?")) {
                var btn = event.target;
                var originalText = btn.innerHTML;
                btn.innerHTML = "<i class=\"fa fa-spinner fa-spin\"></i> Scanning...";
                btn.disabled = true;
                
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    alert("Scan completed. Found 1 WordPress installation in root directory. (Demo Mode)");
                    location.reload();
                }, 3000);
            }
        }
        </script>';
    }
}
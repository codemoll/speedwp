<?php
/**
 * SpeedWP Admin Controller for Server Module
 * 
 * Handles admin area server and account management functionality
 * for the SpeedWP server provisioning module.
 * 
 * SECURITY NOTE: All arithmetic operations are protected against non-numeric 
 * values (e.g., 'unlimited', 'N/A', null) to prevent PHP 8+ TypeError exceptions.
 * Helper methods sanitize data before calculations.
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
            
            // Disk Usage - Safely calculate percentage with numeric validation
            $diskPercent = $this->calculateUsagePercentage(
                $hostingDetails['disk_usage'], 
                $hostingDetails['disk_limit']
            );
            $output .= '<div class="usage-item">';
            $output .= '<strong>Disk Usage:</strong> ';
            $output .= $this->formatBytesForDisplay($hostingDetails['disk_usage']) . ' / ' . 
                      $this->formatBytesForDisplay($hostingDetails['disk_limit']);
            $output .= '<div class="progress" style="margin-top:5px;margin-bottom:10px;">';
            $output .= '<div class="progress-bar ' . ($diskPercent > 80 ? 'progress-bar-danger' : ($diskPercent > 60 ? 'progress-bar-warning' : 'progress-bar-success')) . '" style="width:' . min($diskPercent, 100) . '%">';
            $output .= $diskPercent . '%</div></div>';
            $output .= '</div>';
            
            // Bandwidth Usage - Safely calculate percentage with numeric validation
            $bwPercent = $this->calculateUsagePercentage(
                $hostingDetails['bandwidth_usage'], 
                $hostingDetails['bandwidth_limit']
            );
            $output .= '<div class="usage-item">';
            $output .= '<strong>Bandwidth:</strong> ';
            $output .= $this->formatBytesForDisplay($hostingDetails['bandwidth_usage']) . ' / ' . 
                      $this->formatBytesForDisplay($hostingDetails['bandwidth_limit']);
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
                // No WordPress detected for this specific domain - check for other installations
                $allInstallations = $this->getAllWordPressInstallations();
                
                $output .= '<div class="panel panel-warning">';
                $output .= '<div class="panel-heading"><h4><i class="fa fa-wordpress"></i> WordPress Status</h4></div>';
                $output .= '<div class="panel-body">';
                
                if ($allInstallations['success'] && $allInstallations['count'] > 0) {
                    // Show other WordPress installations found on this account
                    $output .= '<div class="alert alert-info">';
                    $output .= '<h4><i class="fa fa-info-circle"></i> No WordPress Installation for Primary Domain</h4>';
                    $output .= '<p>No WordPress installation was found for the primary domain <strong>' . htmlspecialchars($this->params['domain']) . '</strong>.</p>';
                    $output .= '<p>However, <strong>' . $allInstallations['count'] . '</strong> WordPress installation(s) were found on this cPanel account:</p>';
                    $output .= '</div>';
                    
                    $output .= '<div class="table-responsive">';
                    $output .= '<table class="table table-striped">';
                    $output .= '<thead><tr><th>Domain</th><th>Path</th><th>Version</th><th>Status</th><th>Actions</th></tr></thead>';
                    $output .= '<tbody>';
                    
                    foreach ($allInstallations['installations'] as $installation) {
                        $output .= '<tr>';
                        $output .= '<td><strong>' . htmlspecialchars($installation['domain']) . '</strong></td>';
                        $output .= '<td><code>' . htmlspecialchars($installation['path']) . '</code></td>';
                        $output .= '<td>' . htmlspecialchars($installation['wp_version']) . '</td>';
                        $output .= '<td><span class="label label-' . ($installation['status'] === 'active' ? 'success' : 'warning') . '">' . ucfirst($installation['status']) . '</span></td>';
                        $output .= '<td>';
                        if (!empty($installation['admin_url'])) {
                            $output .= '<a href="' . htmlspecialchars($installation['admin_url']) . '" target="_blank" class="btn btn-xs btn-primary"><i class="fa fa-external-link"></i> Admin</a> ';
                        }
                        $output .= '<button type="button" class="btn btn-xs btn-info" onclick="linkWordPressInstallation(\'' . htmlspecialchars($installation['installation_id']) . '\', \'' . htmlspecialchars($installation['domain']) . '\')"><i class="fa fa-link"></i> Link</button>';
                        $output .= '</td>';
                        $output .= '</tr>';
                    }
                    
                    $output .= '</tbody></table>';
                    $output .= '</div>';
                    
                    $output .= '<div class="alert alert-warning" style="margin-top:15px;">';
                    $output .= '<p><strong>Note:</strong> The installations listed above exist on this cPanel account but are not associated with the primary domain. You can:</p>';
                    $output .= '<ul>';
                    $output .= '<li>Click "Link" to associate an installation with this service</li>';
                    $output .= '<li>Install a new WordPress instance for the primary domain</li>';
                    $output .= '<li>Access existing installations directly via their admin URLs</li>';
                    $output .= '</ul>';
                    $output .= '</div>';
                } else {
                    // No WordPress installations found at all
                    $output .= '<div class="alert alert-info">';
                    $output .= '<h4><i class="fa fa-info-circle"></i> No WordPress Installation Detected</h4>';
                    $output .= '<p>This hosting account does not appear to have WordPress installed, or WordPress is not managed by WP Toolkit.</p>';
                    if ($allInstallations['success']) {
                        $output .= '<p>WP Toolkit API is accessible but no installations were found.</p>';
                    } else {
                        $output .= '<p><strong>Error:</strong> ' . htmlspecialchars($allInstallations['message']) . '</p>';
                    }
                    $output .= '</div>';
                }
                
                $output .= '<div style="margin-top:15px;">';
                $output .= '<button type="button" class="btn btn-success" onclick="installWordPress(' . $this->params['serviceid'] . ')"><i class="fa fa-download"></i> Install WordPress</button> ';
                $output .= '<button type="button" class="btn btn-primary" onclick="scanForWordPress(' . $this->params['serviceid'] . ')"><i class="fa fa-search"></i> Rescan for WordPress</button>';
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
     * Get WordPress details for admin display (Updated to use discovery)
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
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4'],
                'debug' => $this->params['configoption14'] === 'on'
            ]);
            
            // Use discovery method with cPanel username for better detection
            return $cpanel->getWordPressDetails($this->params['domain'], $this->params['username']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting WordPress details from admin: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all WordPress installations for this cPanel account
     * 
     * @return array All WordPress installations
     */
    public function getAllWordPressInstallations()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4'],
                'debug' => $this->params['configoption14'] === 'on'
            ]);
            
            return $cpanel->getAllWordPressInstallations($this->params['username']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting all WordPress installations: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'installations' => [],
                'count' => 0
            ];
        }
    }

    /**
     * Get hosting account details for admin display
     * 
     * @return array Hosting account information with sanitized numeric values
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
            
            // Sanitize usage values to prevent arithmetic errors with non-numeric data
            return [
                'server' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'username' => $this->params['username'],
                'domain' => $this->params['domain'],
                'package' => $this->params['packagename'] ?? 'Default',
                'disk_usage' => $this->sanitizeNumericValue($usage['disk_used'] ?? 0),
                'disk_limit' => $this->sanitizeNumericValue($usage['disk_limit'] ?? 0),
                'bandwidth_usage' => $this->sanitizeNumericValue($usage['bandwidth_used'] ?? 0),
                'bandwidth_limit' => $this->sanitizeNumericValue($usage['bandwidth_limit'] ?? 0),
                'status' => $this->params['productstatus'] ?? 'Active'
            ];
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting hosting details from admin: " . $e->getMessage());
            
            // Return demo data with safe numeric values
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
     * Safely calculate usage percentage with numeric validation
     * 
     * Prevents division by zero and handles non-numeric values like 'unlimited', 'N/A'
     * 
     * @param mixed $usage Current usage value
     * @param mixed $limit Limit value 
     * @return float Percentage (0-100) or 0 if calculation not possible
     */
    private function calculateUsagePercentage($usage, $limit)
    {
        // Sanitize and validate numeric values
        $numericUsage = $this->sanitizeNumericValue($usage);
        $numericLimit = $this->sanitizeNumericValue($limit);
        
        // Return 0 if limit is zero, unlimited, or invalid
        if ($numericLimit <= 0) {
            return 0;
        }
        
        // Calculate percentage, ensuring it doesn't exceed 100%
        $percentage = ($numericUsage / $numericLimit) * 100;
        return round(min($percentage, 100), 1);
    }
    
    /**
     * Sanitize and convert value to numeric for calculations
     * 
     * Handles string values like 'unlimited', 'N/A', null, empty strings
     * 
     * @param mixed $value Value to sanitize
     * @return int|float Numeric value or 0 if not convertible
     */
    private function sanitizeNumericValue($value)
    {
        // Handle null or empty values
        if ($value === null || $value === '') {
            return 0;
        }
        
        // Handle string values that indicate unlimited or N/A
        if (is_string($value)) {
            $lowerValue = strtolower(trim($value));
            if (in_array($lowerValue, ['unlimited', 'n/a', 'na', '-', '∞'])) {
                return 0; // Treat unlimited as 0 for calculation purposes
            }
        }
        
        // Convert to numeric, return 0 if not numeric
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return 0;
    }
    
    /**
     * Format bytes to human readable format with special handling for non-numeric values
     * 
     * @param mixed $bytes Byte value (could be numeric or string like 'unlimited')
     * @return string Formatted size or appropriate label
     */
    private function formatBytesForDisplay($bytes)
    {
        // Handle null or empty values
        if ($bytes === null || $bytes === '') {
            return 'N/A';
        }
        
        // Handle string values that indicate unlimited or special cases
        if (is_string($bytes)) {
            $lowerValue = strtolower(trim($bytes));
            if (in_array($lowerValue, ['unlimited', '∞'])) {
                return 'Unlimited';
            }
            if (in_array($lowerValue, ['n/a', 'na', '-'])) {
                return 'N/A';
            }
        }
        
        // Ensure numeric value before formatting
        $numericBytes = $this->sanitizeNumericValue($bytes);
        if ($numericBytes <= 0) {
            return 'N/A';
        }
        
        return $this->formatBytes($numericBytes);
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
<?php
/**
 * SpeedWP Client Area Controller for Server Module
 * 
 * Handles client area WordPress management interface and functionality
 * for the SpeedWP server provisioning module.
 * 
 * SECURITY NOTE: Data sanitization methods prevent arithmetic operations
 * on non-numeric values like 'unlimited', 'N/A', null to avoid PHP 8+ TypeError.
 * 
 * @package    SpeedWP Server Module
 * @version    1.0.0
 * @author     SpeedWP Development Team
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

class SpeedWP_ClientAreaController
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
     * Handle AJAX requests from client area
     * 
     * @return void (outputs JSON and exits)
     */
    public function handleAjax()
    {
        header('Content-Type: application/json');
        
        try {
            $action = $_POST['action'] ?? '';
            $response = ['success' => false, 'message' => 'Invalid action'];
            
            switch ($action) {
                case 'refresh_wp_details':
                    $response = $this->refreshWordPressDetails();
                    break;
                    
                case 'create_backup':
                    $response = $this->createBackup();
                    break;
                    
                case 'reset_wp_password':
                    $response = $this->resetWordPressPassword();
                    break;
                    
                case 'toggle_auto_updates':
                    $response = $this->toggleAutoUpdates();
                    break;
                    
                case 'update_wordpress':
                    $response = $this->updateWordPress();
                    break;
                    
                case 'get_auto_login':
                    $response = $this->getAutoLogin();
                    break;
                    
                case 'get_ftp_details':
                    $response = $this->getFtpDetails();
                    break;
                    
                case 'manage_plugins':
                    $response = $this->managePlugins();
                    break;
                    
                case 'manage_themes':
                    $response = $this->manageThemes();
                    break;
                    
                case 'download_backup':
                    $response = $this->downloadBackup();
                    break;
                    
                default:
                    $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
                    break;
            }
            
            echo json_encode($response);
            exit;
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Get client area dashboard data
     * 
     * @return array Template data for client area
     */
    public function getDashboard()
    {
        try {
            // Get WordPress site details
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $wpDetails = $cpanel->getWordPressDetails($this->params['domain']);
            $hostingDetails = $this->getHostingAccountDetails();
            $ftpDetails = $cpanel->getFtpDetails($this->params['username'], $this->params['domain']);
            
            return [
                'templatefile' => 'dashboard',
                'vars' => [
                    'domain' => $this->params['domain'],
                    'username' => $this->params['username'],
                    'wp_details' => $wpDetails,
                    'hosting_details' => $hostingDetails,
                    'ftp_details' => $ftpDetails,
                    'service_id' => $this->params['serviceid'],
                    'client_area_url' => $this->params['whmcsurl'] . 'clientarea.php?action=productdetails&id=' . $this->params['serviceid'],
                    'show_wordpress_section' => $wpDetails['success'],
                    'demo_mode' => $wpDetails['demo_mode'] ?? false
                ]
            ];
            
        } catch (Exception $e) {
            logActivity("SpeedWP Client Area Error: " . $e->getMessage());
            
            return [
                'templatefile' => 'error', 
                'vars' => [
                    'error' => 'Unable to load WordPress management interface',
                    'details' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Refresh WordPress details via AJAX
     * 
     * @return array AJAX response
     */
    private function refreshWordPressDetails()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $wpDetails = $cpanel->getWordPressDetails($this->params['domain']);
            
            if ($wpDetails['success']) {
                return [
                    'success' => true,
                    'message' => 'WordPress details refreshed successfully',
                    'data' => $wpDetails
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to refresh WordPress details'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error refreshing WordPress details: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create WordPress backup via AJAX
     * 
     * @return array AJAX response
     */
    private function createBackup()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $result = $cpanel->createWordPressBackup($this->params['domain']);
            
            if ($result['success']) {
                logActivity("SpeedWP: Client-initiated backup created for {$this->params['domain']} - {$result['backup_name']}");
                
                return [
                    'success' => true,
                    'message' => 'Backup created successfully: ' . $result['backup_name'],
                    'backup_name' => $result['backup_name'],
                    'backup_size' => $result['backup_size'] ?? 'Unknown',
                    'created_at' => $result['created_at'] ?? date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Backup creation failed: ' . $result['message']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating backup: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reset WordPress admin password via AJAX
     * 
     * @return array AJAX response
     */
    private function resetWordPressPassword()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $newPassword = $cpanel->generatePassword(12);
            $result = $cpanel->resetWordPressPassword($this->params['domain'], $newPassword);
            
            if ($result['success']) {
                logActivity("SpeedWP: Client-initiated WordPress password reset for {$this->params['domain']}");
                
                return [
                    'success' => true,
                    'message' => 'WordPress admin password reset successfully',
                    'new_password' => $newPassword
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Password reset failed: ' . $result['message']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error resetting password: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle WordPress auto-updates via AJAX
     * 
     * @return array AJAX response
     */
    private function toggleAutoUpdates()
    {
        try {
            $enabled = $_POST['enabled'] === 'true';
            
            // This would normally call WP Toolkit API to toggle auto-updates
            logActivity("SpeedWP: Client toggled auto-updates for {$this->params['domain']} - " . ($enabled ? 'Enabled' : 'Disabled'));
            
            return [
                'success' => true,
                'message' => 'Auto-updates ' . ($enabled ? 'enabled' : 'disabled') . ' successfully (Demo Mode)',
                'enabled' => $enabled
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error toggling auto-updates: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update WordPress core via AJAX
     * 
     * @return array AJAX response
     */
    private function updateWordPress()
    {
        try {
            // This would normally call WP Toolkit API to update WordPress
            logActivity("SpeedWP: Client-initiated WordPress update for {$this->params['domain']}");
            
            return [
                'success' => true,
                'message' => 'WordPress update initiated successfully (Demo Mode)',
                'estimated_time' => '5-10 minutes'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating WordPress: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get hosting account details
     * 
     * @return array Hosting account information
     */
    /**
     * Get hosting account details for client area display
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
                'package' => $this->params['packagename'],
                'disk_usage' => $this->sanitizeNumericValue($usage['disk_used'] ?? 0),
                'disk_limit' => $this->sanitizeNumericValue($usage['disk_limit'] ?? 0),
                'bandwidth_usage' => $this->sanitizeNumericValue($usage['bandwidth_used'] ?? 0),
                'bandwidth_limit' => $this->sanitizeNumericValue($usage['bandwidth_limit'] ?? 0),
                'status' => $this->params['productstatus'] ?? 'Active',
                'cpanel_url' => 'https://' . ($this->params['serverhostname'] ?: $this->params['configoption1']) . ':2083',
                'webmail_url' => 'https://' . $this->params['domain'] . '/webmail'
            ];
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting hosting details: " . $e->getMessage());
            
            // Return basic demo data with safe numeric values
            return [
                'server' => $this->params['serverhostname'] ?: 'demo.server.com',
                'username' => $this->params['username'],
                'domain' => $this->params['domain'],
                'package' => $this->params['packagename'] ?: 'WordPress Hosting',
                'disk_usage' => 512,    // Safe numeric values for demo
                'disk_limit' => 5000,
                'bandwidth_usage' => 2048,
                'bandwidth_limit' => 50000,
                'status' => 'Active',
                'cpanel_url' => 'https://' . ($this->params['serverhostname'] ?: 'demo.server.com') . ':2083',
                'webmail_url' => 'https://' . $this->params['domain'] . '/webmail'
            ];
        }
    }
    
    /**
     * Get WordPress auto-login URL via AJAX
     * 
     * @return array AJAX response
     */
    private function getAutoLogin()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $result = $cpanel->generateWordPressAutoLogin($this->params['domain']);
            
            if ($result['success']) {
                logActivity("SpeedWP: Client-initiated auto-login generated for {$this->params['domain']}");
                
                return [
                    'success' => true,
                    'message' => 'Auto-login URL generated successfully',
                    'login_url' => $result['login_url'],
                    'expires_at' => $result['expires_at'],
                    'demo_mode' => $result['demo_mode'] ?? false
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Auto-login generation failed: ' . ($result['message'] ?? 'Unknown error')
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating auto-login: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get FTP details via AJAX
     * 
     * @return array AJAX response
     */
    private function getFtpDetails()
    {
        try {
            require_once __DIR__ . '/CpanelApi.php';
            $cpanel = new SpeedWP_CpanelApi([
                'host' => $this->params['serverhostname'] ?: $this->params['configoption1'],
                'port' => $this->params['configoption2'] ?: 2087,
                'username' => $this->params['serverusername'] ?: $this->params['configoption3'],
                'password' => $this->params['serverpassword'] ?: $this->params['configoption4']
            ]);
            
            $result = $cpanel->getFtpDetails($this->params['username'], $this->params['domain']);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'FTP details retrieved successfully',
                    'ftp_details' => $result
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to retrieve FTP details'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving FTP details: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Manage WordPress plugins via AJAX
     * 
     * @return array AJAX response
     */
    private function managePlugins()
    {
        try {
            $action = $_POST['plugin_action'] ?? '';
            $plugin = $_POST['plugin_name'] ?? '';
            
            if (!$action || !$plugin) {
                throw new Exception('Plugin action and name are required');
            }
            
            // This would normally call WP Toolkit API to manage plugins
            logActivity("SpeedWP: Client-initiated plugin management for {$this->params['domain']} - {$action} on {$plugin}");
            
            return [
                'success' => true,
                'message' => "Plugin {$action} completed successfully for {$plugin} (Demo Mode)",
                'plugin' => $plugin,
                'action' => $action
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error managing plugin: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Manage WordPress themes via AJAX
     * 
     * @return array AJAX response
     */
    private function manageThemes()
    {
        try {
            $action = $_POST['theme_action'] ?? '';
            $theme = $_POST['theme_name'] ?? '';
            
            if (!$action || !$theme) {
                throw new Exception('Theme action and name are required');
            }
            
            // This would normally call WP Toolkit API to manage themes
            logActivity("SpeedWP: Client-initiated theme management for {$this->params['domain']} - {$action} on {$theme}");
            
            return [
                'success' => true,
                'message' => "Theme {$action} completed successfully for {$theme} (Demo Mode)",
                'theme' => $theme,
                'action' => $action
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error managing theme: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Download WordPress backup via AJAX
     * 
     * @return array AJAX response
     */
    private function downloadBackup()
    {
        try {
            $backupName = $_POST['backup_name'] ?? '';
            
            if (!$backupName) {
                throw new Exception('Backup name is required');
            }
            
            // This would normally generate a secure download link for the backup
            logActivity("SpeedWP: Client-initiated backup download for {$this->params['domain']} - {$backupName}");
            
            // Generate a demo download URL (in real implementation, this would be a secure temporary URL)
            $downloadUrl = $this->params['whmcsurl'] . 'downloads.php?type=backup&file=' . urlencode($backupName) . '&service=' . $this->params['serviceid'];
            
            return [
                'success' => true,
                'message' => 'Download link generated successfully',
                'download_url' => $downloadUrl,
                'backup_name' => $backupName,
                'expires_in' => '24 hours',
                'demo_mode' => true
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error generating download link: ' . $e->getMessage()
            ];
        }
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
}
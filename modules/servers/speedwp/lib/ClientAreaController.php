<?php
/**
 * SpeedWP Client Area Controller for Server Module
 * 
 * Handles client area WordPress management interface and functionality
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
            
            return [
                'templatefile' => 'dashboard',
                'vars' => [
                    'domain' => $this->params['domain'],
                    'username' => $this->params['username'],
                    'wp_details' => $wpDetails,
                    'hosting_details' => $hostingDetails,
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
                'package' => $this->params['packagename'],
                'disk_usage' => $usage['disk_used'] ?? 0,
                'disk_limit' => $usage['disk_limit'] ?? 0,
                'bandwidth_usage' => $usage['bandwidth_used'] ?? 0,
                'bandwidth_limit' => $usage['bandwidth_limit'] ?? 0,
                'status' => $this->params['productstatus'] ?? 'Active',
                'cpanel_url' => 'https://' . ($this->params['serverhostname'] ?: $this->params['configoption1']) . ':2083',
                'webmail_url' => 'https://' . $this->params['domain'] . '/webmail'
            ];
            
        } catch (Exception $e) {
            logActivity("SpeedWP: Error getting hosting details: " . $e->getMessage());
            
            // Return basic demo data
            return [
                'server' => $this->params['serverhostname'] ?: 'demo.server.com',
                'username' => $this->params['username'],
                'domain' => $this->params['domain'],
                'package' => $this->params['packagename'] ?: 'WordPress Hosting',
                'disk_usage' => 512,
                'disk_limit' => 5000,
                'bandwidth_usage' => 2048,
                'bandwidth_limit' => 50000,
                'status' => 'Active',
                'cpanel_url' => 'https://' . ($this->params['serverhostname'] ?: 'demo.server.com') . ':2083',
                'webmail_url' => 'https://' . $this->params['domain'] . '/webmail'
            ];
        }
    }
}
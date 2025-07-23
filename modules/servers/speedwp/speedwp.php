<?php
/**
 * SpeedWP WHMCS Server Module
 * 
 * A comprehensive server provisioning module that creates cPanel hosting accounts
 * with automatic WordPress installation via WP Toolkit integration.
 * 
 * This module handles the complete hosting account lifecycle including:
 * - cPanel account creation and configuration
 * - WordPress installation via WP Toolkit
 * - Account suspension and unsuspension
 * - Account termination and cleanup
 * - Client area WordPress management interface
 * - Admin area server and account management
 * 
 * @package    SpeedWP Server Module
 * @version    1.0.0
 * @author     SpeedWP Development Team
 * @link       https://github.com/codemoll/speedwp
 * @license    MIT License
 * @copyright  2024 SpeedWP Team
 * 
 * @compatible WHMCS 8.0+, PHP 7.4+, cPanel/WHM with WP Toolkit
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Define server module configuration parameters
 * 
 * This function defines the configuration fields that appear in the WHMCS admin
 * when configuring servers that use this module.
 * 
 * @return array Module configuration array
 */
function speedwp_ConfigOptions()
{
    return [
        // Server connection settings
        'Server IP/Hostname' => [
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter the cPanel server hostname or IP address'
        ],
        'WHM Port' => [
            'Type' => 'text', 
            'Size' => '5',
            'Default' => '2087',
            'Description' => 'WHM HTTPS port (usually 2087)'
        ],
        'WHM Username' => [
            'Type' => 'text',
            'Size' => '20', 
            'Default' => 'root',
            'Description' => 'WHM root username or reseller username'
        ],
        'WHM Password/API Token' => [
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'WHM password or API token (recommended)'
        ],
        
        // WordPress/WP Toolkit settings
        'Auto-Install WordPress' => [
            'Type' => 'yesno',
            'Default' => 'on',
            'Description' => 'Automatically install WordPress using WP Toolkit after account creation'
        ],
        'WordPress Version' => [
            'Type' => 'dropdown',
            'Options' => 'latest,6.4,6.3,6.2',
            'Default' => 'latest',
            'Description' => 'WordPress version to install (latest recommended)'
        ],
        'Default Admin Username' => [
            'Type' => 'text',
            'Size' => '15',
            'Default' => 'admin',
            'Description' => 'Default WordPress admin username (can be overridden per account)'
        ],
        
        // Security and backup settings
        'Enable SSL' => [
            'Type' => 'yesno', 
            'Default' => 'on',
            'Description' => 'Automatically enable SSL for new WordPress sites'
        ],
        'Enable Backups' => [
            'Type' => 'yesno',
            'Default' => 'on', 
            'Description' => 'Enable automatic backups via WP Toolkit'
        ],
        'Backup Frequency' => [
            'Type' => 'dropdown',
            'Options' => 'daily,weekly,monthly',
            'Default' => 'weekly',
            'Description' => 'Automatic backup frequency'
        ]
    ];
}

/**
 * Create a new hosting account with WordPress installation
 * 
 * This function is called by WHMCS when a new hosting account needs to be provisioned.
 * It creates the cPanel account and automatically installs WordPress via WP Toolkit.
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_CreateAccount($params)
{
    try {
        // Log account creation attempt
        logActivity("SpeedWP: Creating account for {$params['domain']} (Client: {$params['clientsdetails']['firstname']} {$params['clientsdetails']['lastname']})");
        
        // Initialize cPanel API connection
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        // Prepare account details
        $accountDetails = [
            'user' => $params['username'],
            'pass' => $params['password'],
            'domain' => $params['domain'],
            'plan' => $params['packagename'] ?: 'default',
            'contactemail' => $params['clientsdetails']['email'],
            'quota' => $params['configoptions']['Disk Space'] ?? 0,
            'hasshell' => 0,
            'maxpop' => $params['configoptions']['Email Accounts'] ?? 'unlimited',
            'maxsub' => $params['configoptions']['Subdomains'] ?? 'unlimited',
            'maxpark' => $params['configoptions']['Parked Domains'] ?? 'unlimited',
            'maxaddon' => $params['configoptions']['Addon Domains'] ?? 'unlimited'
        ];
        
        // Create cPanel account
        $result = $cpanel->createAccount($accountDetails);
        
        if (!$result['success']) {
            throw new Exception("Failed to create cPanel account: " . $result['message']);
        }
        
        // Install WordPress if enabled
        if ($params['configoption5'] === 'on') {
            $wpResult = $cpanel->installWordPress([
                'domain' => $params['domain'],
                'username' => $params['username'],
                'admin_user' => $params['configoption7'] ?: 'admin',
                'admin_pass' => speedwp_generatePassword(12),
                'admin_email' => $params['clientsdetails']['email'],
                'site_title' => $params['domain'] . ' - WordPress Site',
                'version' => $params['configoption6'] ?: 'latest',
                'enable_ssl' => $params['configoption8'] === 'on',
                'enable_backups' => $params['configoption9'] === 'on',
                'backup_frequency' => $params['configoption10'] ?: 'weekly'
            ]);
            
            if ($wpResult['success']) {
                // Store WordPress details in custom fields
                speedwp_updateCustomField($params['serviceid'], 'WordPress Admin URL', $wpResult['admin_url']);
                speedwp_updateCustomField($params['serviceid'], 'WordPress Admin User', $wpResult['admin_user']);
                speedwp_updateCustomField($params['serviceid'], 'WordPress Admin Password', encrypt($wpResult['admin_pass']));
                
                logActivity("SpeedWP: WordPress installed successfully for {$params['domain']} - Admin: {$wpResult['admin_user']}");
            } else {
                logActivity("SpeedWP: WordPress installation failed for {$params['domain']}: " . $wpResult['message']);
                // Don't fail account creation if WordPress install fails
            }
        }
        
        logActivity("SpeedWP: Account created successfully for {$params['domain']}");
        return 'success';
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Account creation failed for {$params['domain']}: " . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
}

/**
 * Suspend a hosting account
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_SuspendAccount($params)
{
    try {
        logActivity("SpeedWP: Suspending account {$params['username']} on {$params['domain']}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->suspendAccount($params['username'], 'Suspended via WHMCS');
        
        if (!$result['success']) {
            throw new Exception("Failed to suspend account: " . $result['message']);
        }
        
        // Also suspend WordPress site if it exists
        $wpResult = $cpanel->suspendWordPressSite($params['domain']);
        if ($wpResult['success']) {
            logActivity("SpeedWP: WordPress site suspended for {$params['domain']}");
        }
        
        logActivity("SpeedWP: Account suspended successfully for {$params['domain']}");
        return 'success';
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Account suspension failed for {$params['domain']}: " . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
}

/**
 * Unsuspend a hosting account
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_UnsuspendAccount($params)
{
    try {
        logActivity("SpeedWP: Unsuspending account {$params['username']} on {$params['domain']}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->unsuspendAccount($params['username']);
        
        if (!$result['success']) {
            throw new Exception("Failed to unsuspend account: " . $result['message']);
        }
        
        // Also unsuspend WordPress site if it exists
        $wpResult = $cpanel->unsuspendWordPressSite($params['domain']);
        if ($wpResult['success']) {
            logActivity("SpeedWP: WordPress site unsuspended for {$params['domain']}");
        }
        
        logActivity("SpeedWP: Account unsuspended successfully for {$params['domain']}");
        return 'success';
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Account unsuspension failed for {$params['domain']}: " . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
}

/**
 * Terminate a hosting account
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_TerminateAccount($params)
{
    try {
        logActivity("SpeedWP: Terminating account {$params['username']} on {$params['domain']}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        // Create final backup before termination if enabled
        if ($params['configoption9'] === 'on') {
            $backupResult = $cpanel->createFinalBackup($params['username']);
            if ($backupResult['success']) {
                logActivity("SpeedWP: Final backup created for {$params['domain']} before termination");
            }
        }
        
        $result = $cpanel->terminateAccount($params['username']);
        
        if (!$result['success']) {
            throw new Exception("Failed to terminate account: " . $result['message']);
        }
        
        logActivity("SpeedWP: Account terminated successfully for {$params['domain']}");
        return 'success';
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Account termination failed for {$params['domain']}: " . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
}

/**
 * Change account password
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_ChangePassword($params)
{
    try {
        logActivity("SpeedWP: Changing password for account {$params['username']} on {$params['domain']}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->changeAccountPassword($params['username'], $params['password']);
        
        if (!$result['success']) {
            throw new Exception("Failed to change password: " . $result['message']);
        }
        
        logActivity("SpeedWP: Password changed successfully for {$params['domain']}");
        return 'success';
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Password change failed for {$params['domain']}: " . $e->getMessage());
        return "Error: " . $e->getMessage();
    }
}

/**
 * Generate client area output
 * 
 * This function generates the HTML that appears in the client area for this service.
 * It provides WordPress management functionality and hosting account information.
 * 
 * @param array $params Module parameters from WHMCS
 * @return array Client area template data
 */
function speedwp_ClientArea($params)
{
    try {
        // Include client area controller
        require_once __DIR__ . '/lib/ClientAreaController.php';
        $controller = new SpeedWP_ClientAreaController($params);
        
        // Handle AJAX requests
        if (isset($_POST['action']) && !empty($_POST['action'])) {
            return $controller->handleAjax();
        }
        
        // Generate client area dashboard
        return $controller->getDashboard();
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Client area error for {$params['domain']}: " . $e->getMessage());
        
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
 * Generate admin area output
 * 
 * This function generates additional fields in the admin area product management.
 * It provides server administrators with WordPress management tools.
 * 
 * @param array $params Module parameters from WHMCS
 * @return string HTML output for admin area
 */
function speedwp_AdminCustomButtonArray($params)
{
    return [
        'Manage WordPress' => 'manageWordPress',
        'Reset WP Password' => 'resetWordPressPassword', 
        'Create Backup' => 'createBackup',
        'View WP Details' => 'viewWordPressDetails'
    ];
}

/**
 * Admin area WordPress management
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_manageWordPress($params)
{
    try {
        require_once __DIR__ . '/lib/AdminController.php';
        $controller = new SpeedWP_AdminController($params);
        return $controller->manageWordPress();
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * Reset WordPress admin password
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_resetWordPressPassword($params)
{
    try {
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $newPassword = speedwp_generatePassword(12);
        $result = $cpanel->resetWordPressPassword($params['domain'], $newPassword);
        
        if ($result['success']) {
            // Update stored password
            speedwp_updateCustomField($params['serviceid'], 'WordPress Admin Password', encrypt($newPassword));
            return "WordPress password reset successfully. New password: " . $newPassword;
        } else {
            return "Error: " . $result['message'];
        }
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * Create WordPress backup
 * 
 * @param array $params Module parameters from WHMCS
 * @return string Success or error message
 */
function speedwp_createBackup($params)
{
    try {
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->createWordPressBackup($params['domain']);
        
        if ($result['success']) {
            return "Backup created successfully: " . $result['backup_name'];
        } else {
            return "Error: " . $result['message'];
        }
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * View WordPress details
 * 
 * @param array $params Module parameters from WHMCS
 * @return string WordPress details HTML
 */
function speedwp_viewWordPressDetails($params)
{
    try {
        require_once __DIR__ . '/lib/AdminController.php';
        $controller = new SpeedWP_AdminController($params);
        return $controller->getWordPressDetails();
        
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

/**
 * Test server connection
 * 
 * @param array $params Module parameters from WHMCS
 * @return array Connection test results
 */
function speedwp_TestConnection($params)
{
    try {
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->testConnection();
        
        if ($result['success']) {
            return [
                'success' => true,
                'msg' => 'Connection successful! Server: ' . $result['server_info']
            ];
        } else {
            return [
                'success' => false,
                'msg' => 'Connection failed: ' . $result['message']
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'msg' => 'Connection error: ' . $e->getMessage()
        ];
    }
}

/**
 * Get server usage statistics
 * 
 * @param array $params Module parameters from WHMCS
 * @return array Usage statistics
 */
function speedwp_UsageUpdate($params)
{
    try {
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => $params['configoption2'] ?: 2087,
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $usage = $cpanel->getAccountUsage($params['username']);
        
        if ($usage['success']) {
            return [
                'diskusage' => $usage['disk_used'],
                'disklimit' => $usage['disk_limit'],
                'bwusage' => $usage['bandwidth_used'],
                'bwlimit' => $usage['bandwidth_limit']
            ];
        }
        
        return [];
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Usage update failed for {$params['domain']}: " . $e->getMessage());
        return [];
    }
}

/**
 * Admin Services Tab Fields
 * 
 * @param array $params Module parameters
 * @return string HTML output
 */
function speedwp_AdminServicesTabFields($params)
{
    try {
        require_once __DIR__ . '/lib/AdminController.php';
        $controller = new SpeedWP_AdminController($params);
        return $controller->getServicesTabFields();
        
    } catch (Exception $e) {
        return '<div class="alert alert-danger">Error loading WordPress details: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}

/**
 * Generate a secure password for SpeedWP module
 * 
 * @param int $length Password length
 * @return string Generated password
 */
function speedwp_generatePassword($length = 12)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

/**
 * Update custom field value for SpeedWP module
 * 
 * @param int $serviceId Service ID
 * @param string $fieldName Field name
 * @param string $value Field value
 * @return bool Success status
 */
function speedwp_updateCustomField($serviceId, $fieldName, $value)
{
    try {
        // Check if WHMCS Capsule is available
        if (!class_exists('Illuminate\Database\Capsule\Manager')) {
            return false;
        }
        
        $customField = Capsule::table('tblcustomfields')
            ->where('type', 'product')
            ->where('fieldname', $fieldName)
            ->first();
            
        if ($customField) {
            Capsule::table('tblcustomfieldsvalues')
                ->updateOrInsert(
                    ['fieldid' => $customField->id, 'relid' => $serviceId],
                    ['value' => $value]
                );
            return true;
        }
        
        return false;
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Custom field update failed: " . $e->getMessage());
        return false;
    }
}
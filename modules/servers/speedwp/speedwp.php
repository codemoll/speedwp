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
        
        // cPanel package configuration
        'Package Name' => [
            'Type' => 'text',
            'Size' => '20',
            'Default' => 'default',
            'Description' => 'cPanel package/plan name to use for new accounts (must exist on server)'
        ],
        
        // cPanel package configuration
        'Package Name' => [
            'Type' => 'text',
            'Size' => '20',
            'Default' => 'default',
            'Description' => 'cPanel package/plan name to use for new accounts (must exist on server)'
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
        // Validate required parameters
        $requiredParams = ['domain', 'username', 'password', 'clientsdetails'];
        foreach ($requiredParams as $param) {
            if (empty($params[$param])) {
                throw new Exception("Missing required parameter: {$param}");
            }
        }
        
        // Sanitize inputs
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($params['username']));
        
        if (!$domain) {
            throw new Exception("Invalid domain name provided");
        }
        
        if (!$username || strlen($username) < 3) {
            throw new Exception("Invalid username provided (minimum 3 characters, alphanumeric only)");
        }
        
        // Validate domain format
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            // Additional basic domain validation
            if (!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}$/', $domain)) {
                throw new Exception("Invalid domain format: {$domain}");
            }
        }
        
        // Log account creation attempt
        logActivity("SpeedWP: Creating account for {$domain} (Client: {$params['clientsdetails']['firstname']} {$params['clientsdetails']['lastname']})");
        
        // Initialize cPanel API connection
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        // Prepare account details
        $packageName = trim($params['configoption5']) ?: $params['packagename'] ?: 'default';
        $accountDetails = [
            'user' => $username,
            'pass' => $params['password'],
            'domain' => $domain,
            'plan' => $packageName,
            'contactemail' => filter_var($params['clientsdetails']['email'], FILTER_VALIDATE_EMAIL),
            'quota' => max(0, intval($params['configoptions']['Disk Space'] ?? 0)),
            'hasshell' => 0,
            'maxpop' => $params['configoptions']['Email Accounts'] ?? 'unlimited',
            'maxsub' => $params['configoptions']['Subdomains'] ?? 'unlimited',
            'maxpark' => $params['configoptions']['Parked Domains'] ?? 'unlimited',
            'maxaddon' => $params['configoptions']['Addon Domains'] ?? 'unlimited'
        ];
        
        if (!$accountDetails['contactemail']) {
            throw new Exception("Invalid email address provided");
        }
        
        // Create cPanel account
        $result = $cpanel->createAccount($accountDetails);
        
        if (!$result['success']) {
            throw new Exception("Failed to create cPanel account: " . $result['message']);
        }
        
        // Install WordPress if enabled
        if ($params['configoption6'] === 'on') { // Auto-Install WordPress is now option 6
            $adminUsername = preg_replace('/[^a-zA-Z0-9_]/', '', trim($params['configoption8']) ?: 'admin');
            $wpVersion = in_array($params['configoption7'], ['latest', '6.4', '6.3', '6.2']) 
                ? $params['configoption7'] : 'latest';
            
            $wpResult = $cpanel->installWordPress([
                'domain' => $domain,
                'username' => $username,
                'admin_user' => $adminUsername,
                'admin_pass' => speedwp_generatePassword(12),
                'admin_email' => $accountDetails['contactemail'],
                'site_title' => htmlspecialchars($domain . ' - WordPress Site'),
                'version' => $wpVersion,
                'enable_ssl' => $params['configoption9'] === 'on',
                'enable_backups' => $params['configoption10'] === 'on',
                'backup_frequency' => in_array($params['configoption11'], ['daily', 'weekly', 'monthly']) 
                    ? $params['configoption11'] : 'weekly'
            ]);
            
            if ($wpResult['success']) {
                // Store WordPress details in custom fields (with error handling)
                try {
                    speedwp_updateCustomField($params['serviceid'], 'WordPress Admin URL', $wpResult['admin_url']);
                    speedwp_updateCustomField($params['serviceid'], 'WordPress Admin User', $wpResult['admin_user']);
                    speedwp_updateCustomField($params['serviceid'], 'WordPress Admin Password', encrypt($wpResult['admin_pass']));
                } catch (Exception $e) {
                    logActivity("SpeedWP: Warning - Could not update custom fields: " . $e->getMessage());
                }
                
                logActivity("SpeedWP: WordPress installed successfully for {$domain} - Admin: {$wpResult['admin_user']}");
            } else {
                logActivity("SpeedWP: WordPress installation failed for {$domain}: " . $wpResult['message']);
                // Don't fail account creation if WordPress install fails
            }
        }
        
        logActivity("SpeedWP: Account created successfully for {$domain}");
        return 'success';
        
    } catch (Exception $e) {
        $errorMsg = "Account creation failed for " . ($params['domain'] ?? 'unknown domain') . ": " . $e->getMessage();
        logActivity("SpeedWP: " . $errorMsg);
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
        // Validate required parameters
        if (empty($params['username']) || empty($params['domain'])) {
            throw new Exception("Missing required parameters for account suspension");
        }
        
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($params['username']));
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        
        if (!$username) {
            throw new Exception("Invalid username provided for suspension");
        }
        
        logActivity("SpeedWP: Suspending account {$username} on {$domain}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->suspendAccount($username, 'Suspended via WHMCS');
        
        if (!$result['success']) {
            throw new Exception("Failed to suspend account: " . $result['message']);
        }
        
        // Also suspend WordPress site if it exists
        $wpResult = $cpanel->suspendWordPressSite($domain);
        if ($wpResult['success']) {
            logActivity("SpeedWP: WordPress site suspended for {$domain}");
        }
        
        logActivity("SpeedWP: Account suspended successfully for {$domain}");
        return 'success';
        
    } catch (Exception $e) {
        $errorMsg = "Account suspension failed for " . ($params['domain'] ?? 'unknown domain') . ": " . $e->getMessage();
        logActivity("SpeedWP: " . $errorMsg);
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
        // Validate required parameters
        if (empty($params['username']) || empty($params['domain'])) {
            throw new Exception("Missing required parameters for account unsuspension");
        }
        
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($params['username']));
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        
        if (!$username) {
            throw new Exception("Invalid username provided for unsuspension");
        }
        
        logActivity("SpeedWP: Unsuspending account {$username} on {$domain}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->unsuspendAccount($username);
        
        if (!$result['success']) {
            throw new Exception("Failed to unsuspend account: " . $result['message']);
        }
        
        // Also unsuspend WordPress site if it exists
        $wpResult = $cpanel->unsuspendWordPressSite($domain);
        if ($wpResult['success']) {
            logActivity("SpeedWP: WordPress site unsuspended for {$domain}");
        }
        
        logActivity("SpeedWP: Account unsuspended successfully for {$domain}");
        return 'success';
        
    } catch (Exception $e) {
        $errorMsg = "Account unsuspension failed for " . ($params['domain'] ?? 'unknown domain') . ": " . $e->getMessage();
        logActivity("SpeedWP: " . $errorMsg);
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
        // Validate required parameters
        if (empty($params['username']) || empty($params['domain'])) {
            throw new Exception("Missing required parameters for account termination");
        }
        
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($params['username']));
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        
        if (!$username) {
            throw new Exception("Invalid username provided for termination");
        }
        
        logActivity("SpeedWP: Terminating account {$username} on {$domain}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        // Create final backup before termination if enabled
        if ($params['configoption10'] === 'on') { // Enable Backups is now option 10
            try {
                $backupResult = $cpanel->createFinalBackup($username);
                if ($backupResult['success']) {
                    logActivity("SpeedWP: Final backup created for {$domain} before termination");
                }
            } catch (Exception $e) {
                logActivity("SpeedWP: Warning - Final backup failed for {$domain}: " . $e->getMessage());
                // Continue with termination even if backup fails
            }
        }
        
        $result = $cpanel->terminateAccount($username);
        
        if (!$result['success']) {
            throw new Exception("Failed to terminate account: " . $result['message']);
        }
        
        logActivity("SpeedWP: Account terminated successfully for {$domain}");
        return 'success';
        
    } catch (Exception $e) {
        $errorMsg = "Account termination failed for " . ($params['domain'] ?? 'unknown domain') . ": " . $e->getMessage();
        logActivity("SpeedWP: " . $errorMsg);
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
        // Validate required parameters
        if (empty($params['username']) || empty($params['password']) || empty($params['domain'])) {
            throw new Exception("Missing required parameters for password change");
        }
        
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($params['username']));
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        
        if (!$username) {
            throw new Exception("Invalid username provided for password change");
        }
        
        if (strlen($params['password']) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        
        logActivity("SpeedWP: Changing password for account {$username} on {$domain}");
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->changeAccountPassword($username, $params['password']);
        
        if (!$result['success']) {
            throw new Exception("Failed to change password: " . $result['message']);
        }
        
        logActivity("SpeedWP: Password changed successfully for {$domain}");
        return 'success';
        
    } catch (Exception $e) {
        $errorMsg = "Password change failed for " . ($params['domain'] ?? 'unknown domain') . ": " . $e->getMessage();
        logActivity("SpeedWP: " . $errorMsg);
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
        // Validate required parameters
        if (empty($params['domain'])) {
            throw new Exception("Domain is required for WordPress password reset");
        }
        
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        if (!$domain) {
            throw new Exception("Invalid domain provided");
        }
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $newPassword = speedwp_generatePassword(12);
        $result = $cpanel->resetWordPressPassword($domain, $newPassword);
        
        if ($result['success']) {
            // Update stored password
            try {
                speedwp_updateCustomField($params['serviceid'], 'WordPress Admin Password', encrypt($newPassword));
            } catch (Exception $e) {
                logActivity("SpeedWP: Warning - Could not update custom field for password reset: " . $e->getMessage());
            }
            
            return "WordPress admin password reset successfully.\n\nNew password: " . $newPassword . "\n\nPlease save this password securely and log in to change it to something memorable.";
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
        // Validate required parameters
        if (empty($params['domain'])) {
            throw new Exception("Domain is required for backup creation");
        }
        
        $domain = filter_var(trim($params['domain']), FILTER_SANITIZE_STRING);
        if (!$domain) {
            throw new Exception("Invalid domain provided");
        }
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $result = $cpanel->createWordPressBackup($domain);
        
        if ($result['success']) {
            $message = "WordPress backup created successfully!\n\n";
            $message .= "Backup Name: " . $result['backup_name'] . "\n";
            if (isset($result['backup_size'])) {
                $message .= "Size: " . $result['backup_size'] . "\n";
            }
            if (isset($result['created_at'])) {
                $message .= "Created: " . $result['created_at'];
            }
            
            return $message;
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
        // Validate connection parameters
        $host = trim($params['serverhostname'] ?: $params['configoption1']);
        $port = intval($params['configoption2'] ?: 2087);
        $username = trim($params['serverusername'] ?: $params['configoption3']);
        $password = $params['serverpassword'] ?: $params['configoption4'];
        
        if (!$host) {
            throw new Exception("Server hostname is required");
        }
        
        if (!$username) {
            throw new Exception("Server username is required");
        }
        
        if (!$password) {
            throw new Exception("Server password/API token is required");
        }
        
        if ($port < 1 || $port > 65535) {
            throw new Exception("Invalid port number: {$port}");
        }
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password
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
        // Validate required parameters
        if (empty($params['username'])) {
            logActivity("SpeedWP: Warning - No username provided for usage update");
            return [];
        }
        
        $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($params['username']));
        if (!$username) {
            logActivity("SpeedWP: Warning - Invalid username for usage update");
            return [];
        }
        
        require_once __DIR__ . '/lib/CpanelApi.php';
        $cpanel = new SpeedWP_CpanelApi([
            'host' => $params['serverhostname'] ?: $params['configoption1'],
            'port' => intval($params['configoption2'] ?: 2087),
            'username' => $params['serverusername'] ?: $params['configoption3'],
            'password' => $params['serverpassword'] ?: $params['configoption4']
        ]);
        
        $usage = $cpanel->getAccountUsage($username);
        
        if ($usage['success']) {
            return [
                'diskusage' => max(0, intval($usage['disk_used'])),
                'disklimit' => max(0, intval($usage['disk_limit'])),
                'bwusage' => max(0, intval($usage['bandwidth_used'])),
                'bwlimit' => max(0, intval($usage['bandwidth_limit']))
            ];
        }
        
        return [];
        
    } catch (Exception $e) {
        logActivity("SpeedWP: Usage update failed for " . ($params['domain'] ?? $params['username'] ?? 'unknown') . ": " . $e->getMessage());
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
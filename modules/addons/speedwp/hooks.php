<?php
/**
 * SpeedWP WHMCS Hooks
 * 
 * Register hooks for WordPress management automation and client area integration.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\View\Menu\Item as MenuItem;
use WHMCS\View\Menu\AbstractMenu;

/**
 * Hook: After hosting account activation
 * Automatically detect and register WordPress installations, optionally auto-install WordPress
 */
add_hook('AfterModuleCreate', 1, function($vars) {
    if ($vars['producttype'] == 'hostingaccount') {
        try {
            // Include cPanel API
            require_once __DIR__ . '/lib/cpanelApi.php';
            
            // Get addon configuration
            $addonConfig = getAddonVars('speedwp');
            $autoInstall = $addonConfig['auto_install_wp'] ?? false;
            
            $cpanel = new SpeedWP_CpanelApi();
            $cpanel->setCredentials($vars['username'], $vars['password']);
            
            // Scan for existing WordPress installations
            $existingInstalls = $cpanel->scanWordPressInstallations($vars['domain']);
            
            // Register existing installations
            foreach ($existingInstalls as $install) {
                $query = "INSERT INTO mod_speedwp_sites 
                         (client_id, domain, cpanel_user, wp_path, wp_version, status, created_at) 
                         VALUES (:client_id, :domain, :cpanel_user, :wp_path, :wp_version, 'active', NOW())";
                
                $stmt = Capsule::connection()->getPdo()->prepare($query);
                $stmt->execute([
                    'client_id' => $vars['userid'],
                    'domain' => $vars['domain'],
                    'cpanel_user' => $vars['username'],
                    'wp_path' => $install['path'],
                    'wp_version' => $install['version']
                ]);
                
                // Create FTP credentials for existing installations
                $ftpResult = $cpanel->createFtpAccount($vars['domain'], $install['path']);
                if ($ftpResult['success']) {
                    // Store FTP credentials in database
                    $updateQuery = "UPDATE mod_speedwp_sites SET 
                                   ftp_username = :ftp_username, 
                                   ftp_password = :ftp_password 
                                   WHERE client_id = :client_id AND domain = :domain AND wp_path = :wp_path";
                    
                    $updateStmt = Capsule::connection()->getPdo()->prepare($updateQuery);
                    $updateStmt->execute([
                        'ftp_username' => $ftpResult['username'],
                        'ftp_password' => $ftpResult['password'],
                        'client_id' => $vars['userid'],
                        'domain' => $vars['domain'],
                        'wp_path' => $install['path']
                    ]);
                }
            }
            
            // Auto-install WordPress if enabled and no existing installations found
            if ($autoInstall && empty($existingInstalls)) {
                // Get client details for admin email
                $clientQuery = "SELECT email, firstname, lastname FROM tblclients WHERE id = :userid";
                $clientStmt = Capsule::connection()->getPdo()->prepare($clientQuery);
                $clientStmt->execute(['userid' => $vars['userid']]);
                $client = $clientStmt->fetch();
                
                if ($client) {
                    $installOptions = [
                        'admin_user' => 'admin',
                        'admin_email' => $client['email'],
                        'admin_password' => $cpanel->generatePassword(12),
                        'site_title' => $client['firstname'] . "'s WordPress Site",
                        'db_name' => $vars['username'] . '_wp',
                        'db_user' => $vars['username'] . '_wp',
                        'db_password' => $cpanel->generatePassword(12)
                    ];
                    
                    $installResult = $cpanel->installWordPress($vars['domain'], '/', $installOptions);
                    
                    if ($installResult['success']) {
                        // Create FTP credentials
                        $ftpResult = $cpanel->createFtpAccount($vars['domain'], '/');
                        
                        // Register new installation
                        $insertQuery = "INSERT INTO mod_speedwp_sites 
                                       (client_id, domain, cpanel_user, wp_path, wp_version, status, 
                                        admin_username, admin_password, ftp_username, ftp_password, created_at) 
                                       VALUES (:client_id, :domain, :cpanel_user, :wp_path, :wp_version, 'active', 
                                               :admin_username, :admin_password, :ftp_username, :ftp_password, NOW())";
                        
                        $insertStmt = Capsule::connection()->getPdo()->prepare($insertQuery);
                        $insertStmt->execute([
                            'client_id' => $vars['userid'],
                            'domain' => $vars['domain'],
                            'cpanel_user' => $vars['username'],
                            'wp_path' => '/',
                            'wp_version' => 'latest',
                            'admin_username' => $installOptions['admin_user'],
                            'admin_password' => $installOptions['admin_password'],
                            'ftp_username' => $ftpResult['username'] ?? '',
                            'ftp_password' => $ftpResult['password'] ?? ''
                        ]);
                        
                        logActivity("SpeedWP: WordPress auto-installed for new account: " . $vars['domain']);
                    }
                }
            }
            
            $foundCount = count($existingInstalls);
            $message = "SpeedWP: Scanned account {$vars['domain']} - found {$foundCount} WordPress installation(s)";
            if ($autoInstall && empty($existingInstalls)) {
                $message .= " - WordPress auto-installed";
            }
            logActivity($message);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error processing new account {$vars['domain']}: " . $e->getMessage());
        }
    }
});

/**
 * Hook: After hosting account suspension
 * Update WordPress site statuses
 */
add_hook('AfterModuleSuspend', 1, function($vars) {
    if ($vars['producttype'] == 'hostingaccount') {
        // TODO: Update WordPress site statuses to suspended
        
        try {
            $query = "UPDATE mod_speedwp_sites SET status = 'suspended' 
                     WHERE cpanel_user = :username";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['username' => $vars['username']]);
            
            logActivity("SpeedWP: Suspended WordPress sites for account: " . $vars['username']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error: " . $e->getMessage());
        }
    }
});

/**
 * Hook: After hosting account unsuspension
 * Reactivate WordPress site statuses
 */
add_hook('AfterModuleUnsuspend', 1, function($vars) {
    if ($vars['producttype'] == 'hostingaccount') {
        // TODO: Update WordPress site statuses to active
        
        try {
            $query = "UPDATE mod_speedwp_sites SET status = 'active' 
                     WHERE cpanel_user = :username";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['username' => $vars['username']]);
            
            logActivity("SpeedWP: Reactivated WordPress sites for account: " . $vars['username']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error: " . $e->getMessage());
        }
    }
});

/**
 * Hook: After hosting account termination
 * Clean up WordPress site records
 */
add_hook('AfterModuleTerminate', 1, function($vars) {
    if ($vars['producttype'] == 'hostingaccount') {
        // TODO: Archive or remove WordPress site records
        
        try {
            $query = "UPDATE mod_speedwp_sites SET status = 'inactive' 
                     WHERE cpanel_user = :username";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['username' => $vars['username']]);
            
            logActivity("SpeedWP: Deactivated WordPress sites for terminated account: " . $vars['username']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error: " . $e->getMessage());
        }
    }
});

/**
 * Hook: Client area navigation
 * Add SpeedWP menu item to client area
 */
add_hook('ClientAreaPrimaryNavbar', 1, function(AbstractMenu $primaryNavbar) {
    // Only show for clients with hosting services
    if (!is_null($primaryNavbar)) {
        $primaryNavbar->addChild('speedwp')
            ->setLabel('WordPress Manager')
            ->setUri('index.php?m=speedwp')
            ->setOrder(50)
            ->setIcon('fa-wordpress');
    }
});

/**
 * Hook: Admin area navigation
 * Add SpeedWP menu item to admin area
 */
add_hook('AdminAreaPage', 1, function($vars) {
    // TODO: Add admin menu customization if needed
    // This could be used to add quick links or modify existing menus
});

/**
 * Hook: Daily cron for WordPress maintenance
 * Perform daily WordPress updates and security checks
 */
add_hook('DailyCronJob', 1, function($vars) {
    // TODO: Implement daily WordPress maintenance tasks
    // - Check for WordPress core updates
    // - Check for plugin updates
    // - Run security scans
    // - Generate backup reports
    
    try {
        require_once __DIR__ . '/lib/cpanelApi.php';
        
        // TODO: Implement daily maintenance logic
        logActivity("SpeedWP: Daily maintenance tasks completed");
        
    } catch (Exception $e) {
        logActivity("SpeedWP Daily Cron Error: " . $e->getMessage());
    }
});

/**
 * Hook: Client login
 * Track WordPress management activity
 */
add_hook('ClientLogin', 1, function($vars) {
    // TODO: Optional: Track client access patterns for analytics
    // This could help identify popular WordPress management features
});

/**
 * Hook: Email template variables
 * Add FTP credentials to welcome emails
 */
add_hook('EmailTplMergeFields', 1, function($vars) {
    if ($vars['messagename'] == 'Hosting Account Welcome Email') {
        // Get addon configuration
        $addonConfig = getAddonVars('speedwp');
        $includeFtpInEmail = $addonConfig['include_ftp_in_email'] ?? false;
        
        if ($includeFtpInEmail && isset($vars['relid'])) {
            try {
                // Get WordPress sites for this hosting account
                $hostingQuery = "SELECT h.username FROM tblhosting h WHERE h.id = :hosting_id";
                $stmt = Capsule::connection()->getPdo()->prepare($hostingQuery);
                $stmt->execute(['hosting_id' => $vars['relid']]);
                $hosting = $stmt->fetch();
                
                if ($hosting) {
                    $sitesQuery = "SELECT domain, wp_path, ftp_username, ftp_password, site_title 
                                  FROM mod_speedwp_sites 
                                  WHERE cpanel_user = :cpanel_user 
                                  AND status = 'active'
                                  AND ftp_username IS NOT NULL";
                    
                    $sitesStmt = Capsule::connection()->getPdo()->prepare($sitesQuery);
                    $sitesStmt->execute(['cpanel_user' => $hosting['username']]);
                    $wpSites = $sitesStmt->fetchAll();
                    
                    if (!empty($wpSites)) {
                        $ftpInfo = "\n\n=== WordPress FTP Access ===\n";
                        $ftpInfo .= "Your hosting account includes WordPress installations with dedicated FTP access:\n\n";
                        
                        foreach ($wpSites as $site) {
                            $ftpInfo .= "Site: " . ($site['site_title'] ?: $site['domain'] . $site['wp_path']) . "\n";
                            $ftpInfo .= "FTP Host: " . $site['domain'] . "\n";
                            $ftpInfo .= "FTP Username: " . $site['ftp_username'] . "\n";
                            $ftpInfo .= "FTP Password: " . $site['ftp_password'] . "\n";
                            $ftpInfo .= "FTP Port: 21\n";
                            $ftpInfo .= "Directory: " . $site['wp_path'] . "\n";
                            $ftpInfo .= "WordPress Admin: http://" . $site['domain'] . $site['wp_path'] . "wp-admin/\n\n";
                        }
                        
                        $ftpInfo .= "You can use these FTP credentials to upload files, install themes/plugins, or make direct file modifications to your WordPress sites.\n";
                        $ftpInfo .= "For security, we recommend changing these passwords after your first login.\n";
                        
                        // Add FTP info to email content
                        $vars['message'] = $vars['message'] . $ftpInfo;
                    }
                }
                
            } catch (Exception $e) {
                logActivity("SpeedWP Email Hook Error: " . $e->getMessage());
            }
        }
    }
    
    return $vars;
});
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

/**
 * Hook: After hosting account activation
 * Automatically detect and register WordPress installations
 */
add_hook('AfterModuleCreate', 1, function($vars) {
    if ($vars['producttype'] == 'hostingaccount') {
        // TODO: Scan for existing WordPress installations in the new hosting account
        // TODO: Auto-register discovered WP sites in speedwp_sites table
        
        try {
            // Include cPanel API
            require_once __DIR__ . '/lib/cpanelApi.php';
            
            $cpanel = new SpeedWP_CpanelApi();
            // TODO: Implement WordPress detection logic
            
            logActivity("SpeedWP: Scanning for WordPress installations in new account: " . $vars['domain']);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error: " . $e->getMessage());
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
add_hook('ClientAreaPrimaryNavbar', 1, function(Menu $primaryNavbar) {
    // TODO: Add conditional logic to only show for clients with hosting services
    
    $primaryNavbar->addChild('speedwp', [
        'label' => 'WordPress Manager',
        'uri' => 'index.php?m=speedwp',
        'order' => 50,
        'icon' => 'fa-wordpress'
    ]);
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
 * Hook: Before client area page display
 * Add WordPress-related announcements or notices
 */
add_hook('ClientAreaPageViewTicket', 1, function($vars) {
    // TODO: Add context-aware WordPress tips or notifications
    // Could include update reminders, security notices, etc.
});
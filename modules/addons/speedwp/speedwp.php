<?php
/**
 * SpeedWP WHMCS Addon Module
 * 
 * WordPress management for hosting clients via WHMCS client area using cPanel backend.
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
 * Define addon module configuration
 * 
 * @return array Module configuration
 */
function speedwp_config()
{
    return [
        'name' => 'SpeedWP - WordPress Manager',
        'description' => 'WordPress management for hosting clients via cPanel integration',
        'version' => '1.0.0',
        'author' => 'SpeedWP Team',
        'language' => 'english',
        'fields' => [
            'cpanel_host' => [
                'FriendlyName' => 'cPanel Host',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter the cPanel hostname or IP address',
            ],
            'cpanel_port' => [
                'FriendlyName' => 'cPanel Port',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '2083',
                'Description' => 'cPanel HTTPS port (usually 2083)',
            ],
            'auto_install_wp' => [
                'FriendlyName' => 'Auto-Install WordPress',
                'Type' => 'yesno',
                'Description' => 'Automatically install WordPress on new hosting accounts',
            ],
            'auto_create_ftp' => [
                'FriendlyName' => 'Auto-Create FTP Accounts',
                'Type' => 'yesno',
                'Description' => 'Automatically create FTP accounts for WordPress sites',
            ],
            'include_ftp_in_email' => [
                'FriendlyName' => 'Include FTP in Welcome Email',
                'Type' => 'yesno',
                'Description' => 'Include FTP credentials in welcome emails',
            ],
            'auto_backup_before_update' => [
                'FriendlyName' => 'Auto-Backup Before Updates',
                'Type' => 'yesno',
                'Description' => 'Automatically create backups before WordPress updates',
            ],
            'backup_retention_days' => [
                'FriendlyName' => 'Backup Retention (Days)',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '30',
                'Description' => 'Number of days to keep backups (0 = keep forever)',
            ],
            'debug_mode' => [
                'FriendlyName' => 'Debug Mode',
                'Type' => 'yesno',
                'Description' => 'Enable debug logging for troubleshooting',
            ],
        ]
    ];
}

/**
 * Activate addon module
 * 
 * @return array Result with success status and message
 */
function speedwp_activate()
{
    // Create necessary database tables for WordPress site management
    try {
        // Main WordPress sites table
        $query = "CREATE TABLE IF NOT EXISTS `mod_speedwp_sites` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `client_id` int(10) NOT NULL,
            `domain` varchar(255) NOT NULL,
            `cpanel_user` varchar(50) NOT NULL,
            `wp_path` varchar(255) NOT NULL DEFAULT '/',
            `wp_version` varchar(20) DEFAULT NULL,
            `status` enum('active','inactive','suspended','updating','installing') DEFAULT 'active',
            `admin_username` varchar(50) DEFAULT NULL,
            `admin_password` text DEFAULT NULL,
            `admin_email` varchar(255) DEFAULT NULL,
            `site_title` varchar(255) DEFAULT NULL,
            `site_url` varchar(255) DEFAULT NULL,
            `admin_url` varchar(255) DEFAULT NULL,
            `ftp_username` varchar(50) DEFAULT NULL,
            `ftp_password` text DEFAULT NULL,
            `database_name` varchar(64) DEFAULT NULL,
            `database_user` varchar(64) DEFAULT NULL,
            `database_password` text DEFAULT NULL,
            `ssl_enabled` tinyint(1) DEFAULT 0,
            `maintenance_mode` tinyint(1) DEFAULT 0,
            `auto_update` tinyint(1) DEFAULT 1,
            `backup_enabled` tinyint(1) DEFAULT 1,
            `last_backup` datetime DEFAULT NULL,
            `last_update_check` datetime DEFAULT NULL,
            `disk_usage` bigint(20) DEFAULT 0,
            `file_count` int(10) DEFAULT 0,
            `plugin_count` int(10) DEFAULT 0,
            `theme_count` int(10) DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `client_id` (`client_id`),
            KEY `domain` (`domain`),
            KEY `cpanel_user` (`cpanel_user`),
            KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        full_query($query);
        
        // WordPress plugins table
        $pluginsQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_plugins` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `site_id` int(10) NOT NULL,
            `plugin_name` varchar(255) NOT NULL,
            `plugin_slug` varchar(255) NOT NULL,
            `version` varchar(20) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'inactive',
            `auto_update` tinyint(1) DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `site_id` (`site_id`),
            KEY `plugin_slug` (`plugin_slug`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        full_query($pluginsQuery);
        
        // WordPress themes table
        $themesQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_themes` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `site_id` int(10) NOT NULL,
            `theme_name` varchar(255) NOT NULL,
            `theme_slug` varchar(255) NOT NULL,
            `version` varchar(20) DEFAULT NULL,
            `status` enum('active','inactive') DEFAULT 'inactive',
            `auto_update` tinyint(1) DEFAULT 0,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `site_id` (`site_id`),
            KEY `theme_slug` (`theme_slug`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        full_query($themesQuery);
        
        // WordPress backups table
        $backupsQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_backups` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `site_id` int(10) NOT NULL,
            `backup_name` varchar(255) NOT NULL,
            `backup_type` enum('full','files','database') DEFAULT 'full',
            `file_path` varchar(500) DEFAULT NULL,
            `file_size` bigint(20) DEFAULT 0,
            `status` enum('creating','completed','failed','deleted') DEFAULT 'creating',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `site_id` (`site_id`),
            KEY `backup_type` (`backup_type`),
            KEY `status` (`status`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        full_query($backupsQuery);
        
        // Activity logs table
        $logsQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_logs` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `site_id` int(10) DEFAULT NULL,
            `client_id` int(10) DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `description` text DEFAULT NULL,
            `status` enum('success','error','warning','info') DEFAULT 'info',
            `details` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `site_id` (`site_id`),
            KEY `client_id` (`client_id`),
            KEY `action` (`action`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        full_query($logsQuery);
        
        return [
            'status' => 'success',
            'description' => 'SpeedWP addon activated successfully. Database tables created.'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'description' => 'Error activating SpeedWP: ' . $e->getMessage()
        ];
    }
}

/**
 * Deactivate addon module
 * 
 * @return array Result with success status and message
 */
function speedwp_deactivate()
{
    // TODO: Clean up any scheduled tasks or temporary data
    // Note: We don't drop tables to preserve data
    
    return [
        'status' => 'success',
        'description' => 'SpeedWP addon deactivated successfully.'
    ];
}

/**
 * Upgrade addon module
 * 
 * @param array $vars Module configuration variables
 * @return void
 */
function speedwp_upgrade($vars)
{
    $version = $vars['version'];
    
    // TODO: Implement database schema upgrades based on version
    switch ($version) {
        case '1.0.0':
            // Initial version - no upgrades needed
            break;
        // TODO: Add future version upgrade cases
    }
}

/**
 * Admin area output
 * 
 * @param array $vars Module configuration variables
 * @return string HTML output for admin area
 */
function speedwp_output($vars)
{
    // Include admin controller
    require_once __DIR__ . '/controllers/AdminController.php';
    
    $controller = new SpeedWP_AdminController($vars);
    return $controller->index();
}

/**
 * Client area output
 * 
 * @param array $vars Module configuration variables
 * @return array Client area page data
 */
function speedwp_clientarea($vars)
{
    // Include client controller
    require_once __DIR__ . '/controllers/ClientController.php';
    
    $controller = new SpeedWP_ClientController($vars);
    return $controller->dashboard();
}

/**
 * Admin services tab additional fields
 * 
 * @param array $vars Service variables
 * @return string HTML output for service tab
 */
function speedwp_AdminServicesTabFields($vars)
{
    // TODO: Add WordPress-specific fields to hosting service pages
    return '';
}

/**
 * Admin services tab save
 * 
 * @param array $vars Service variables
 * @return string Result message
 */
function speedwp_AdminServicesTabFieldsSave($vars)
{
    // TODO: Save WordPress-specific service data
    return '';
}
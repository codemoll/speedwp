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
            // TODO: Add configuration fields for cPanel integration
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
            'debug_mode' => [
                'FriendlyName' => 'Debug Mode',
                'Type' => 'yesno',
                'Description' => 'Enable debug logging for troubleshooting',
            ],
            // TODO: Add more configuration options as needed
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
    // TODO: Create necessary database tables for WordPress site management
    try {
        // Example table creation (implement as needed)
        $query = "CREATE TABLE IF NOT EXISTS `mod_speedwp_sites` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `client_id` int(10) NOT NULL,
            `domain` varchar(255) NOT NULL,
            `cpanel_user` varchar(50) NOT NULL,
            `wp_path` varchar(255) NOT NULL DEFAULT '/',
            `wp_version` varchar(20) DEFAULT NULL,
            `status` enum('active','inactive','suspended') DEFAULT 'active',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `client_id` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        
        full_query($query);
        
        return [
            'status' => 'success',
            'description' => 'SpeedWP addon activated successfully.'
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
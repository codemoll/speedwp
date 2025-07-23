<?php
/**
 * SpeedWP WHMCS Addon Module
 * 
 * Comprehensive WordPress management addon for WHMCS that enables hosting clients 
 * to manage their WordPress installations directly from the client area using cPanel integration.
 * 
 * Key Features:
 * - Automatic WordPress detection and registration
 * - One-click WordPress installation from client area
 * - WordPress core, plugin, and theme updates
 * - Automated backup and restore functionality
 * - Client self-service WordPress management
 * - Administrative oversight and bulk operations
 * - cPanel API integration for seamless file/database operations
 * 
 * Compatibility:
 * - WHMCS 8.0+ (tested with WHMCS 8.x series)
 * - PHP 7.4+ (compatible with PHP 7.4, 8.0, 8.1, 8.2+)
 * - cPanel hosting environment with API access
 * - MySQL 5.7+ / MariaDB 10.2+
 * 
 * @package    SpeedWP
 * @version    1.0.0
 * @author     SpeedWP Development Team
 * @link       https://github.com/codemoll/speedwp
 * @license    MIT License
 * @copyright  2024 SpeedWP Team
 * 
 * Security Features:
 * - Input validation and sanitization
 * - SQL injection prevention via prepared statements
 * - XSS protection through proper output encoding
 * - Secure password generation and storage
 * - API credential encryption and safe storage
 * 
 * Architecture:
 * - Modular controller-based design
 * - Separation of concerns (admin/client controllers)
 * - Comprehensive error handling and logging
 * - Database abstraction using WHMCS Capsule ORM
 * - Hook-based automation for hosting account lifecycle
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Define addon module configuration parameters
 * 
 * This function defines all configuration options available in the WHMCS admin area
 * for the SpeedWP addon module. These settings control various aspects of WordPress
 * management functionality and automation.
 * 
 * Configuration Categories:
 * 1. cPanel Integration - Server connection and API settings
 * 2. WordPress Automation - Auto-install, FTP creation, email integration
 * 3. Backup Management - Automatic backup creation and retention
 * 4. System Settings - Debug mode and operational parameters
 * 
 * All settings are stored in WHMCS database and accessible via getAddonVars('speedwp')
 * 
 * @return array Module configuration array with fields and metadata
 * @since 1.0.0
 * @compatible WHMCS 8.x+, PHP 7.4+
 */
function speedwp_config()
{
    return [
        // Module identification and metadata
        'name' => 'SpeedWP - WordPress Manager',
        'description' => 'Comprehensive WordPress management for hosting clients via cPanel integration. Compatible with WHMCS 8.0+ and PHP 7.4+.',
        'version' => '1.0.0',
        'author' => 'SpeedWP Team',
        'language' => 'english',
        
        // Configuration fields for admin area settings
        'fields' => [
            // === cPanel Integration Settings ===
            'cpanel_host' => [
                'FriendlyName' => 'cPanel Host',
                'Type' => 'text',
                'Size' => '25',
                'Default' => '',
                'Description' => 'Enter the cPanel hostname or IP address for API communication (e.g., server.example.com)',
            ],
            'cpanel_port' => [
                'FriendlyName' => 'cPanel Port',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '2083',
                'Description' => 'cPanel HTTPS API port (typically 2083 for SSL, 2082 for non-SSL)',
            ],
            
            // === WordPress Automation Settings ===
            'auto_install_wp' => [
                'FriendlyName' => 'Auto-Install WordPress',
                'Type' => 'yesno',
                'Description' => 'Automatically install WordPress on new hosting accounts when no existing installations are found (saves time for clients)',
            ],
            'auto_create_ftp' => [
                'FriendlyName' => 'Auto-Create FTP Accounts',
                'Type' => 'yesno',
                'Description' => 'Automatically create dedicated FTP accounts for each WordPress installation (provides secure file access)',
            ],
            'include_ftp_in_email' => [
                'FriendlyName' => 'Include FTP in Welcome Email',
                'Type' => 'yesno',
                'Description' => 'Include WordPress FTP credentials in hosting welcome emails (convenient but less secure)',
            ],
            
            // === Backup and Maintenance Settings ===
            'auto_backup_before_update' => [
                'FriendlyName' => 'Auto-Backup Before Updates',
                'Type' => 'yesno',
                'Description' => 'Automatically create full backups before performing WordPress core, plugin, or theme updates (recommended for safety)',
            ],
            'backup_retention_days' => [
                'FriendlyName' => 'Backup Retention (Days)',
                'Type' => 'text',
                'Size' => '5',
                'Default' => '30',
                'Description' => 'Number of days to retain backups (0 = keep forever, may consume significant disk space). Recommended: 30-90 days',
            ],
            
            // === System and Debug Settings ===
            'debug_mode' => [
                'FriendlyName' => 'Debug Mode',
                'Type' => 'yesno',
                'Description' => 'Enable detailed logging for troubleshooting (disable in production environments to reduce log size)',
            ],
        ]
    ];
}

/**
 * Activate addon module with comprehensive database setup
 * Creates all necessary database tables with proper relationships and constraints
 * Compatible with WHMCS 8.x+ and PHP 7.4+ using InnoDB engine
 * 
 * @return array Result with success status and descriptive message
 */
function speedwp_activate()
{
    try {
        // Create main WordPress sites table with comprehensive fields
        $sitesQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_sites` (
            `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Unique site identifier',
            `client_id` int(10) NOT NULL COMMENT 'WHMCS client ID reference',
            `domain` varchar(255) NOT NULL COMMENT 'Site domain name',
            `cpanel_user` varchar(50) NOT NULL COMMENT 'cPanel username for API access',
            `wp_path` varchar(255) NOT NULL DEFAULT '/' COMMENT 'WordPress installation path relative to domain root',
            `wp_version` varchar(20) DEFAULT NULL COMMENT 'Current WordPress core version',
            `status` enum('active','inactive','suspended','updating','installing') DEFAULT 'active' COMMENT 'Site operational status',
            `admin_username` varchar(50) DEFAULT NULL COMMENT 'WordPress admin username',
            `admin_password` text DEFAULT NULL COMMENT 'WordPress admin password (encrypted)',
            `admin_email` varchar(255) DEFAULT NULL COMMENT 'WordPress admin email address',
            `site_title` varchar(255) DEFAULT NULL COMMENT 'WordPress site title/name',
            `site_url` varchar(255) DEFAULT NULL COMMENT 'Full site URL with protocol',
            `admin_url` varchar(255) DEFAULT NULL COMMENT 'WordPress admin panel URL',
            `ftp_username` varchar(50) DEFAULT NULL COMMENT 'Dedicated FTP username for site management',
            `ftp_password` text DEFAULT NULL COMMENT 'FTP password (encrypted)',
            `database_name` varchar(64) DEFAULT NULL COMMENT 'WordPress MySQL database name',
            `database_user` varchar(64) DEFAULT NULL COMMENT 'WordPress database username',
            `database_password` text DEFAULT NULL COMMENT 'WordPress database password (encrypted)',
            `ssl_enabled` tinyint(1) DEFAULT 0 COMMENT 'SSL/HTTPS status for site',
            `maintenance_mode` tinyint(1) DEFAULT 0 COMMENT 'WordPress maintenance mode status',
            `auto_update` tinyint(1) DEFAULT 1 COMMENT 'Auto-update enabled for core/plugins/themes',
            `backup_enabled` tinyint(1) DEFAULT 1 COMMENT 'Automatic backup functionality enabled',
            `last_backup` datetime DEFAULT NULL COMMENT 'Timestamp of most recent backup',
            `last_update_check` datetime DEFAULT NULL COMMENT 'Last WordPress update check timestamp',
            `disk_usage` bigint(20) DEFAULT 0 COMMENT 'Site disk usage in bytes',
            `file_count` int(10) DEFAULT 0 COMMENT 'Total number of files in WordPress installation',
            `plugin_count` int(10) DEFAULT 0 COMMENT 'Number of installed plugins',
            `theme_count` int(10) DEFAULT 0 COMMENT 'Number of installed themes',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Site registration timestamp',
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last modification timestamp',
            PRIMARY KEY (`id`),
            KEY `idx_client_id` (`client_id`),
            KEY `idx_domain` (`domain`),
            KEY `idx_cpanel_user` (`cpanel_user`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='WordPress site management and configuration data';";
        
        full_query($sitesQuery);
        
        // Create WordPress plugins table with relationship to sites
        $pluginsQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_plugins` (
            `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Unique plugin record identifier',
            `site_id` int(10) NOT NULL COMMENT 'Reference to WordPress site',
            `plugin_name` varchar(255) NOT NULL COMMENT 'Human-readable plugin name',
            `plugin_slug` varchar(255) NOT NULL COMMENT 'WordPress plugin directory slug',
            `version` varchar(20) DEFAULT NULL COMMENT 'Current installed plugin version',
            `status` enum('active','inactive') DEFAULT 'inactive' COMMENT 'Plugin activation status',
            `auto_update` tinyint(1) DEFAULT 0 COMMENT 'Automatic updates enabled for this plugin',
            `description` text DEFAULT NULL COMMENT 'Plugin description',
            `author` varchar(255) DEFAULT NULL COMMENT 'Plugin author',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Plugin discovery timestamp',
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last plugin data update',
            PRIMARY KEY (`id`),
            KEY `idx_site_id` (`site_id`),
            KEY `idx_plugin_slug` (`plugin_slug`),
            KEY `idx_status` (`status`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='WordPress plugin inventory and management';";
        
        full_query($pluginsQuery);
        
        // Create WordPress themes table with relationship to sites
        $themesQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_themes` (
            `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Unique theme record identifier',
            `site_id` int(10) NOT NULL COMMENT 'Reference to WordPress site',
            `theme_name` varchar(255) NOT NULL COMMENT 'Human-readable theme name',
            `theme_slug` varchar(255) NOT NULL COMMENT 'WordPress theme directory slug',
            `version` varchar(20) DEFAULT NULL COMMENT 'Current installed theme version',
            `status` enum('active','inactive') DEFAULT 'inactive' COMMENT 'Theme activation status (only one active per site)',
            `auto_update` tinyint(1) DEFAULT 0 COMMENT 'Automatic updates enabled for this theme',
            `description` text DEFAULT NULL COMMENT 'Theme description',
            `author` varchar(255) DEFAULT NULL COMMENT 'Theme author',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Theme discovery timestamp',
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last theme data update',
            PRIMARY KEY (`id`),
            KEY `idx_site_id` (`site_id`),
            KEY `idx_theme_slug` (`theme_slug`),
            KEY `idx_status` (`status`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='WordPress theme inventory and management';";
        
        full_query($themesQuery);
        
        // Create WordPress backups table with comprehensive backup tracking
        $backupsQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_backups` (
            `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Unique backup record identifier',
            `site_id` int(10) NOT NULL COMMENT 'Reference to WordPress site',
            `backup_name` varchar(255) NOT NULL COMMENT 'Backup filename or identifier',
            `backup_type` enum('full','files','database') DEFAULT 'full' COMMENT 'Type of backup created',
            `file_path` varchar(500) DEFAULT NULL COMMENT 'Server path to backup file',
            `file_size` bigint(20) DEFAULT 0 COMMENT 'Backup file size in bytes',
            `compression_type` varchar(20) DEFAULT 'gzip' COMMENT 'Backup compression method used',
            `status` enum('creating','completed','failed','deleted') DEFAULT 'creating' COMMENT 'Backup operation status',
            `trigger_type` enum('manual','automatic','pre_update','scheduled') DEFAULT 'manual' COMMENT 'What triggered this backup',
            `error_message` text DEFAULT NULL COMMENT 'Error details if backup failed',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Backup creation start timestamp',
            `completed_at` datetime DEFAULT NULL COMMENT 'Backup completion timestamp',
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last backup record update',
            PRIMARY KEY (`id`),
            KEY `idx_site_id` (`site_id`),
            KEY `idx_backup_type` (`backup_type`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='WordPress backup tracking and management';";
        
        full_query($backupsQuery);
        
        // Create comprehensive activity logs table
        $logsQuery = "CREATE TABLE IF NOT EXISTS `mod_speedwp_logs` (
            `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'Unique log entry identifier',
            `site_id` int(10) DEFAULT NULL COMMENT 'Reference to WordPress site (if applicable)',
            `client_id` int(10) DEFAULT NULL COMMENT 'WHMCS client ID (if client-initiated)',
            `action` varchar(100) NOT NULL COMMENT 'Action type or category',
            `description` text DEFAULT NULL COMMENT 'Human-readable description of the activity',
            `status` enum('success','error','warning','info') DEFAULT 'info' COMMENT 'Activity outcome status',
            `details` longtext DEFAULT NULL COMMENT 'Detailed technical information (JSON format)',
            `ip_address` varchar(45) DEFAULT NULL COMMENT 'Client IP address (supports IPv6)',
            `user_agent` text DEFAULT NULL COMMENT 'Client browser/user agent string',
            `execution_time` decimal(10,4) DEFAULT NULL COMMENT 'Action execution time in seconds',
            `memory_usage` int(10) DEFAULT NULL COMMENT 'Peak memory usage for this action',
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Activity timestamp',
            PRIMARY KEY (`id`),
            KEY `idx_site_id` (`site_id`),
            KEY `idx_client_id` (`client_id`),
            KEY `idx_action` (`action`),
            KEY `idx_status` (`status`),
            KEY `idx_created_at` (`created_at`),
            FOREIGN KEY (`site_id`) REFERENCES `mod_speedwp_sites`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='Comprehensive activity logging for WordPress operations';";
        
        full_query($logsQuery);
        
        // Log successful activation
        logActivity("SpeedWP: Module activated successfully - database tables created with InnoDB engine");
        
        return [
            'status' => 'success',
            'description' => 'SpeedWP addon activated successfully. All database tables created with proper relationships and indexes. Ready for WordPress management operations.'
        ];
        
    } catch (Exception $e) {
        // Log detailed error for debugging
        logActivity("SpeedWP Activation Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        
        return [
            'status' => 'error',
            'description' => 'SpeedWP activation failed: ' . $e->getMessage() . '. Check WHMCS activity log for detailed error information.'
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
 * Admin area output with comprehensive error handling
 * Provides WordPress management interface for administrators
 * Compatible with WHMCS 8.x+ admin interface standards
 * 
 * @param array $vars Module configuration variables and WHMCS admin context
 * @return string HTML output for admin area display
 */
function speedwp_output($vars)
{
    return speedwp_admin_output($vars);
}

/**
 * Alternative function name mentioned in problem statement
 * For WHMCS compatibility where this naming convention might be expected
 * 
 * @param array $vars Module configuration variables and WHMCS admin context
 * @return string HTML output for admin area display
 */
function addon_Speedwp_output($vars)
{
    return speedwp_admin_output($vars);
}

/**
 * Main admin area output implementation with comprehensive error handling
 * Provides WordPress management interface for administrators
 * Compatible with WHMCS 8.x+ admin interface standards
 * 
 * @param array $vars Module configuration variables and WHMCS admin context
 * @return string HTML output for admin area display
 */
function speedwp_admin_output($vars)
{
    try {
        // Log debug information
        logActivity("SpeedWP Debug: Admin output function called with vars: " . (is_array($vars) ? 'array[' . count($vars) . ']' : gettype($vars)));
        
        // Validate required module configuration - but show demo dashboard even if not configured
        $showConfigWarning = empty($vars['cpanel_host']);
        
        // Include admin controller with error handling
        $controllerPath = __DIR__ . '/controllers/AdminController.php';
        if (!file_exists($controllerPath)) {
            throw new Exception('Admin controller file not found: ' . $controllerPath);
        }
        
        require_once $controllerPath;
        
        // Verify controller class exists
        if (!class_exists('SpeedWP_AdminController')) {
            throw new Exception('SpeedWP_AdminController class not found after including file');
        }
        
        // Initialize controller and get output
        $controller = new SpeedWP_AdminController($vars);
        $dashboardOutput = $controller->index();
        
        // Ensure we have valid output
        if (empty($dashboardOutput)) {
            throw new Exception('Admin controller returned empty output');
        }
        
        // Add configuration warning at the top if needed, but still show dashboard
        $finalOutput = '';
        if ($showConfigWarning) {
            $finalOutput .= '<div class="alert alert-info" style="margin-bottom: 20px;">' .
                           '<h4><i class="fa fa-info-circle"></i> Configuration Notice</h4>' .
                           '<p>SpeedWP is displaying demo data. Configure cPanel host settings to manage real WordPress sites.</p>' .
                           '<p><a href="configaddonmods.php" class="btn btn-sm btn-primary">' .
                           '<i class="fa fa-cog"></i> Configure Settings</a></p>' .
                           '</div>';
        }
        
        $finalOutput .= $dashboardOutput;
        
        // Log successful output generation
        logActivity("SpeedWP Debug: Successfully generated admin output (" . strlen($finalOutput) . " characters)");
        
        return $finalOutput;
        
    } catch (Exception $e) {
        // Log detailed error for debugging
        $errorDetails = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'vars_provided' => is_array($vars) ? count($vars) : 'not array'
        ];
        
        logActivity("SpeedWP Admin Output Error: " . json_encode($errorDetails));
        
        // Return user-friendly error display with debugging info
        $errorOutput = '<div class="alert alert-danger" style="margin: 20px 0;">' .
               '<h4><i class="fa fa-exclamation-triangle"></i> SpeedWP Admin Error</h4>' .
               '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        
        // Add debug info if debug mode is enabled
        if (!empty($vars['debug_mode'])) {
            $errorOutput .= '<div style="margin-top: 10px; padding: 10px; background: #f8f8f8; border-radius: 4px;">' .
                           '<small><strong>Debug Info:</strong><br>' .
                           'File: ' . htmlspecialchars($e->getFile()) . '<br>' .
                           'Line: ' . $e->getLine() . '<br>' .
                           'Variables provided: ' . (is_array($vars) ? count($vars) : gettype($vars)) .
                           '</small></div>';
        }
        
        $errorOutput .= '<hr>' .
               '<h5>Troubleshooting Steps:</h5>' .
               '<ol>' .
               '<li>Verify SpeedWP addon is properly activated in Addon Modules</li>' .
               '<li>Check that all required files are present in the speedwp directory</li>' .
               '<li>Ensure database tables were created during activation</li>' .
               '<li>Enable debug mode in addon settings for detailed error information</li>' .
               '<li>Review WHMCS activity logs for detailed error messages</li>' .
               '</ol>' .
               '<p style="margin-top: 15px;">' .
               '<a href="addonmodules.php" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back to Addon Modules</a> ' .
               '<a href="configaddonmods.php" class="btn btn-primary"><i class="fa fa-cog"></i> Configure Settings</a>' .
               '</p>' .
               '</div>';
               
        return $errorOutput;
    }
}

/**
 * Client area output with comprehensive error handling and AJAX support
 * Provides WordPress management interface for hosting clients
 * Compatible with WHMCS 8.x+ client area standards
 * 
 * @param array $vars Module configuration variables and client context
 * @return array|void Client area page data array or void for AJAX responses
 */
function speedwp_clientarea($vars)
{
    try {
        // Handle AJAX requests first (these exit early)
        if (isset($_POST['action']) && !empty($_POST['action'])) {
            // Include client controller for AJAX handling
            $controllerPath = __DIR__ . '/controllers/ClientController.php';
            if (!file_exists($controllerPath)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Client controller file not found']);
                exit;
            }
            
            require_once $controllerPath;
            
            if (!class_exists('SpeedWP_ClientController')) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Client controller class not found']);
                exit;
            }
            
            $controller = new SpeedWP_ClientController($vars);
            $controller->handleAjax();
            return; // AJAX requests don't return template data
        }
        
        // Handle regular page requests
        $controllerPath = __DIR__ . '/controllers/ClientController.php';
        if (!file_exists($controllerPath)) {
            return [
                'pagetitle' => 'WordPress Manager - Error',
                'breadcrumb' => ['index.php?m=speedwp' => 'WordPress Manager'],
                'templatefile' => 'error',
                'vars' => [
                    'error' => 'Client controller not found. Please contact support.',
                    'error_details' => 'Missing file: ' . $controllerPath
                ]
            ];
        }
        
        require_once $controllerPath;
        
        if (!class_exists('SpeedWP_ClientController')) {
            return [
                'pagetitle' => 'WordPress Manager - Error',
                'breadcrumb' => ['index.php?m=speedwp' => 'WordPress Manager'],
                'templatefile' => 'error',
                'vars' => [
                    'error' => 'Client controller class not found. Please contact support.',
                    'error_details' => 'Class SpeedWP_ClientController missing after file inclusion'
                ]
            ];
        }
        
        // Initialize controller and return dashboard data
        $controller = new SpeedWP_ClientController($vars);
        return $controller->dashboard();
        
    } catch (Exception $e) {
        // Log error for debugging
        logActivity("SpeedWP Client Area Error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
        
        // Return error template data
        return [
            'pagetitle' => 'WordPress Manager - Error',
            'breadcrumb' => ['index.php?m=speedwp' => 'WordPress Manager'],
            'templatefile' => 'error',
            'vars' => [
                'error' => 'An error occurred while loading WordPress Manager.',
                'error_details' => htmlspecialchars($e->getMessage()),
                'support_message' => 'If this error persists, please contact support with the error details above.'
            ]
        ];
    }
}

/**
 * Admin services tab additional fields
 * 
 * @param array $vars Service variables
 * @return string HTML output for service tab
 */
function speedwp_AdminServicesTabFields($vars)
{
    $serviceid = $vars['serviceid'];
    $domain = $vars['domain'];
    $username = $vars['username'];
    
    // Get WordPress sites for this hosting service
    try {
        $query = "SELECT * FROM mod_speedwp_sites WHERE cpanel_user = ? ORDER BY wp_path";
        $stmt = Capsule::connection()->getPdo()->prepare($query);
        $stmt->execute([$username]);
        $wpSites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $wpSites = [];
    }
    
    $output = '<div class="speedwp-service-tab">';
    $output .= '<h4><i class="fa fa-wordpress"></i> WordPress Sites</h4>';
    
    if (empty($wpSites)) {
        $output .= '<div class="alert alert-info">';
        $output .= '<p>No WordPress sites found for this hosting account.</p>';
        $output .= '<button type="button" class="btn btn-primary btn-sm" onclick="scanHostingAccount(' . $serviceid . ')">Scan for WordPress</button>';
        $output .= '</div>';
    } else {
        $output .= '<div class="table-responsive">';
        $output .= '<table class="table table-condensed">';
        $output .= '<thead><tr><th>Path</th><th>Version</th><th>Status</th><th>Actions</th></tr></thead>';
        $output .= '<tbody>';
        
        foreach ($wpSites as $site) {
            $output .= '<tr>';
            $output .= '<td><strong>' . htmlspecialchars($site['wp_path']) . '</strong></td>';
            $output .= '<td>' . htmlspecialchars($site['wp_version'] ?: 'Unknown') . '</td>';
            $output .= '<td><span class="label label-' . ($site['status'] === 'active' ? 'success' : 'warning') . '">' . ucfirst($site['status']) . '</span></td>';
            $output .= '<td>';
            $output .= '<button type="button" class="btn btn-xs btn-info" onclick="manageSiteFromAdmin(' . $site['id'] . ')">Manage</button> ';
            $output .= '<button type="button" class="btn btn-xs btn-warning" onclick="updateSiteFromAdmin(' . $site['id'] . ')">Update</button>';
            $output .= '</td>';
            $output .= '</tr>';
        }
        
        $output .= '</tbody></table>';
        $output .= '</div>';
        
        $output .= '<button type="button" class="btn btn-primary btn-sm" onclick="scanHostingAccount(' . $serviceid . ')">Scan Again</button> ';
        $output .= '<button type="button" class="btn btn-success btn-sm" onclick="installWordPressFromAdmin(' . $serviceid . ', \'' . $domain . '\')">Install WordPress</button>';
    }
    
    $output .= '</div>';
    
    // Add JavaScript for admin service tab functionality
    $output .= '<script>
    function scanHostingAccount(serviceId) {
        if (confirm("Scan this hosting account for WordPress installations?")) {
            // TODO: Implement scanning functionality
            alert("Scanning functionality coming soon!");
        }
    }
    
    function manageSiteFromAdmin(siteId) {
        // TODO: Open site management modal
        alert("Site management from admin interface coming soon!");
    }
    
    function updateSiteFromAdmin(siteId) {
        if (confirm("Update this WordPress site?")) {
            // TODO: Implement update functionality
            alert("Update functionality coming soon!");
        }
    }
    
    function installWordPressFromAdmin(serviceId, domain) {
        var path = prompt("Enter installation path (e.g., / for root or /blog/ for subdirectory):", "/");
        if (path !== null) {
            // TODO: Implement WordPress installation
            alert("WordPress installation from admin interface coming soon!");
        }
    }
    </script>';
    
    return $output;
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
<?php
/**
 * SpeedWP Language File - English
 * 
 * Language definitions for SpeedWP WHMCS Addon Module.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Module Information
$_ADDONLANG['speedwp_title'] = "SpeedWP - WordPress Manager";
$_ADDONLANG['speedwp_description'] = "WordPress management for hosting clients via cPanel integration";

// General Terms
$_ADDONLANG['speedwp_wordpress'] = "WordPress";
$_ADDONLANG['speedwp_sites'] = "Sites";
$_ADDONLANG['speedwp_manage'] = "Manage";
$_ADDONLANG['speedwp_install'] = "Install";
$_ADDONLANG['speedwp_update'] = "Update";
$_ADDONLANG['speedwp_backup'] = "Backup";
$_ADDONLANG['speedwp_scan'] = "Scan";
$_ADDONLANG['speedwp_delete'] = "Delete";
$_ADDONLANG['speedwp_restore'] = "Restore";
$_ADDONLANG['speedwp_settings'] = "Settings";
$_ADDONLANG['speedwp_tools'] = "Tools";

// Status Terms
$_ADDONLANG['speedwp_status_active'] = "Active";
$_ADDONLANG['speedwp_status_inactive'] = "Inactive";
$_ADDONLANG['speedwp_status_suspended'] = "Suspended";
$_ADDONLANG['speedwp_status_updating'] = "Updating";
$_ADDONLANG['speedwp_status_backing_up'] = "Backing Up";
$_ADDONLANG['speedwp_status_error'] = "Error";

// Client Area
$_ADDONLANG['speedwp_client_dashboard_title'] = "WordPress Manager";
$_ADDONLANG['speedwp_client_dashboard_subtitle'] = "Manage your WordPress installations from your hosting accounts";
$_ADDONLANG['speedwp_client_no_sites'] = "No WordPress sites found";
$_ADDONLANG['speedwp_client_no_hosting'] = "No active hosting accounts found";
$_ADDONLANG['speedwp_client_scan_account'] = "Scan for WordPress";
$_ADDONLANG['speedwp_client_install_wp'] = "Install WordPress";
$_ADDONLANG['speedwp_client_manage_site'] = "Manage Site";
$_ADDONLANG['speedwp_client_update_site'] = "Update Site";
$_ADDONLANG['speedwp_client_backup_site'] = "Backup Site";
$_ADDONLANG['speedwp_client_site_info'] = "Site Information";
$_ADDONLANG['speedwp_client_version'] = "Version";
$_ADDONLANG['speedwp_client_last_updated'] = "Last Updated";
$_ADDONLANG['speedwp_client_hosting_package'] = "Hosting Package";

// Quick Actions
$_ADDONLANG['speedwp_quick_actions'] = "Quick Actions";
$_ADDONLANG['speedwp_scan_all_accounts'] = "Scan All Accounts";
$_ADDONLANG['speedwp_scan_all_description'] = "Find existing WordPress installations";
$_ADDONLANG['speedwp_install_new'] = "Install WordPress";
$_ADDONLANG['speedwp_install_new_description'] = "Create new WordPress site";
$_ADDONLANG['speedwp_update_all'] = "Update All Sites";
$_ADDONLANG['speedwp_update_all_description'] = "Update WordPress core and plugins";
$_ADDONLANG['speedwp_backup_all'] = "Backup All Sites";
$_ADDONLANG['speedwp_backup_all_description'] = "Create backups of all sites";

// Admin Area
$_ADDONLANG['speedwp_admin_dashboard'] = "SpeedWP Dashboard";
$_ADDONLANG['speedwp_admin_sites_management'] = "WordPress Sites Management";
$_ADDONLANG['speedwp_admin_client_management'] = "Client Management";
$_ADDONLANG['speedwp_admin_total_sites'] = "Total WordPress Sites";
$_ADDONLANG['speedwp_admin_active_sites'] = "Active Sites";
$_ADDONLANG['speedwp_admin_updates_available'] = "Updates Available";
$_ADDONLANG['speedwp_admin_total_clients'] = "Clients with WordPress";
$_ADDONLANG['speedwp_admin_recent_activity'] = "Recent Activity";
$_ADDONLANG['speedwp_admin_no_activity'] = "No recent activity to display";
$_ADDONLANG['speedwp_admin_scan_all_sites'] = "Scan All Accounts";
$_ADDONLANG['speedwp_admin_manage_sites'] = "Manage Sites";
$_ADDONLANG['speedwp_admin_manage_clients'] = "Manage Clients";

// Actions and Operations
$_ADDONLANG['speedwp_action_scanning'] = "Scanning for WordPress installations...";
$_ADDONLANG['speedwp_action_installing'] = "Installing WordPress...";
$_ADDONLANG['speedwp_action_updating'] = "Updating WordPress...";
$_ADDONLANG['speedwp_action_backing_up'] = "Creating backup...";
$_ADDONLANG['speedwp_action_restoring'] = "Restoring from backup...";
$_ADDONLANG['speedwp_action_deleting'] = "Deleting WordPress installation...";

// Success Messages
$_ADDONLANG['speedwp_success_scan'] = "WordPress scan completed successfully";
$_ADDONLANG['speedwp_success_install'] = "WordPress installed successfully";
$_ADDONLANG['speedwp_success_update'] = "WordPress updated successfully";
$_ADDONLANG['speedwp_success_backup'] = "Backup created successfully";
$_ADDONLANG['speedwp_success_restore'] = "WordPress restored successfully";
$_ADDONLANG['speedwp_success_delete'] = "WordPress installation deleted successfully";
$_ADDONLANG['speedwp_success_config_saved'] = "Configuration saved successfully";

// Error Messages
$_ADDONLANG['speedwp_error_scan_failed'] = "WordPress scan failed";
$_ADDONLANG['speedwp_error_install_failed'] = "WordPress installation failed";
$_ADDONLANG['speedwp_error_update_failed'] = "WordPress update failed";
$_ADDONLANG['speedwp_error_backup_failed'] = "Backup creation failed";
$_ADDONLANG['speedwp_error_restore_failed'] = "WordPress restore failed";
$_ADDONLANG['speedwp_error_delete_failed'] = "WordPress deletion failed";
$_ADDONLANG['speedwp_error_permission_denied'] = "Permission denied";
$_ADDONLANG['speedwp_error_site_not_found'] = "WordPress site not found";
$_ADDONLANG['speedwp_error_cpanel_connection'] = "cPanel connection failed";
$_ADDONLANG['speedwp_error_invalid_credentials'] = "Invalid cPanel credentials";
$_ADDONLANG['speedwp_error_database_error'] = "Database error occurred";
$_ADDONLANG['speedwp_error_file_system'] = "File system error";
$_ADDONLANG['speedwp_error_network'] = "Network communication error";

// Warnings
$_ADDONLANG['speedwp_warning_backup_recommended'] = "Backup recommended before proceeding";
$_ADDONLANG['speedwp_warning_operation_irreversible'] = "This operation cannot be undone";
$_ADDONLANG['speedwp_warning_long_operation'] = "This operation may take several minutes";
$_ADDONLANG['speedwp_warning_disk_space'] = "Ensure sufficient disk space is available";

// Confirmations
$_ADDONLANG['speedwp_confirm_scan'] = "Scan this hosting account for WordPress installations?";
$_ADDONLANG['speedwp_confirm_install'] = "Install WordPress in the specified location?";
$_ADDONLANG['speedwp_confirm_update'] = "Update this WordPress installation?";
$_ADDONLANG['speedwp_confirm_backup'] = "Create backup for this WordPress site?";
$_ADDONLANG['speedwp_confirm_restore'] = "Restore WordPress from the selected backup?";
$_ADDONLANG['speedwp_confirm_delete'] = "Delete this WordPress installation permanently?";
$_ADDONLANG['speedwp_confirm_bulk_scan'] = "Scan all hosting accounts? This may take several minutes.";
$_ADDONLANG['speedwp_confirm_bulk_update'] = "Update all WordPress sites? This may take a long time.";
$_ADDONLANG['speedwp_confirm_bulk_backup'] = "Create backups for all WordPress sites?";

// Installation Options
$_ADDONLANG['speedwp_install_domain'] = "Domain";
$_ADDONLANG['speedwp_install_path'] = "Installation Path";
$_ADDONLANG['speedwp_install_database'] = "Database Name";
$_ADDONLANG['speedwp_install_admin_user'] = "Admin Username";
$_ADDONLANG['speedwp_install_admin_email'] = "Admin Email";
$_ADDONLANG['speedwp_install_admin_password'] = "Admin Password";
$_ADDONLANG['speedwp_install_site_title'] = "Site Title";
$_ADDONLANG['speedwp_install_language'] = "Language";

// Configuration
$_ADDONLANG['speedwp_config_cpanel_host'] = "cPanel Host";
$_ADDONLANG['speedwp_config_cpanel_port'] = "cPanel Port";
$_ADDONLANG['speedwp_config_cpanel_ssl'] = "Use SSL";
$_ADDONLANG['speedwp_config_debug_mode'] = "Debug Mode";
$_ADDONLANG['speedwp_config_auto_scan'] = "Auto Scan New Accounts";
$_ADDONLANG['speedwp_config_auto_backup'] = "Auto Backup Before Updates";
$_ADDONLANG['speedwp_config_backup_retention'] = "Backup Retention Days";

// Tools
$_ADDONLANG['speedwp_tools_health_check'] = "System Health Check";
$_ADDONLANG['speedwp_tools_bulk_operations'] = "Bulk Operations";
$_ADDONLANG['speedwp_tools_reports'] = "Reports";
$_ADDONLANG['speedwp_tools_logs'] = "Activity Logs";
$_ADDONLANG['speedwp_tools_cleanup'] = "Cleanup Tools";

// Reports
$_ADDONLANG['speedwp_report_overview'] = "Overview Report";
$_ADDONLANG['speedwp_report_sites_by_version'] = "Sites by WordPress Version";
$_ADDONLANG['speedwp_report_update_status'] = "Update Status Report";
$_ADDONLANG['speedwp_report_backup_status'] = "Backup Status Report";
$_ADDONLANG['speedwp_report_client_usage'] = "Client Usage Report";

// Navigation
$_ADDONLANG['speedwp_nav_dashboard'] = "Dashboard";
$_ADDONLANG['speedwp_nav_sites'] = "Sites";
$_ADDONLANG['speedwp_nav_clients'] = "Clients";
$_ADDONLANG['speedwp_nav_tools'] = "Tools";
$_ADDONLANG['speedwp_nav_settings'] = "Settings";
$_ADDONLANG['speedwp_nav_help'] = "Help";

// Help and Documentation
$_ADDONLANG['speedwp_help_title'] = "SpeedWP Help";
$_ADDONLANG['speedwp_help_getting_started'] = "Getting Started";
$_ADDONLANG['speedwp_help_troubleshooting'] = "Troubleshooting";
$_ADDONLANG['speedwp_help_api_documentation'] = "API Documentation";
$_ADDONLANG['speedwp_help_support'] = "Support";

// TODO: Add more language strings as features are implemented
// Future language strings for advanced features:
// - Plugin management
// - Theme management
// - Security scanning
// - Performance optimization
// - Staging environments
// - SSL certificate management
// - CDN integration
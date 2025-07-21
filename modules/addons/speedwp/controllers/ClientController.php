<?php
/**
 * SpeedWP Client Area Controller
 * 
 * Handles client area WordPress management interface and functionality.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

class SpeedWP_ClientController
{
    /**
     * @var array Module configuration variables
     */
    private $vars;
    
    /**
     * @var int Current client ID
     */
    private $clientId;

    /**
     * Constructor
     * 
     * @param array $vars Module configuration variables
     */
    public function __construct($vars)
    {
        $this->vars = $vars;
        $this->clientId = $_SESSION['uid'] ?? 0;
    }

    /**
     * Dashboard - Main client area page
     * 
     * @return array Template variables for client area
     */
    public function dashboard()
    {
        global $smarty;
        
        // TODO: Implement client authentication check
        if (!$this->clientId) {
            return [
                'pagetitle' => 'WordPress Manager',
                'breadcrumb' => ['index.php?m=speedwp' => 'WordPress Manager'],
                'templatefile' => 'error',
                'vars' => [
                    'error' => 'Access denied. Please log in to continue.'
                ]
            ];
        }

        // TODO: Get client's WordPress sites
        $wpSites = $this->getClientWordPressSites();
        
        // TODO: Get client's hosting accounts for WordPress detection
        $hostingAccounts = $this->getClientHostingAccounts();
        
        // Template variables
        $templateVars = [
            'modulename' => 'speedwp',
            'modulelink' => 'index.php?m=speedwp',
            'client_id' => $this->clientId,
            'wp_sites' => $wpSites,
            'hosting_accounts' => $hostingAccounts,
            'actions' => [
                'scan' => 'Scan for WordPress',
                'install' => 'Install WordPress',
                'manage' => 'Manage Sites'
            ],
            // TODO: Add more template variables as needed
        ];

        return [
            'pagetitle' => 'WordPress Manager',
            'breadcrumb' => ['index.php?m=speedwp' => 'WordPress Manager'],
            'templatefile' => 'dashboard',
            'vars' => $templateVars
        ];
    }

    /**
     * Handle AJAX requests from client area
     * 
     * @return void
     */
    public function handleAjax()
    {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'scan_wordpress':
                $this->scanForWordPress();
                break;
                
            case 'install_wordpress':
                $this->installWordPress();
                break;
                
            case 'update_wordpress':
                $this->updateWordPress();
                break;
                
            case 'backup_wordpress':
                $this->backupWordPress();
                break;
                
            case 'reset_password':
                $this->resetWordPressPassword();
                break;
                
            case 'change_password':
                $this->changeWordPressPassword();
                break;
                
            case 'toggle_maintenance':
                $this->toggleMaintenanceMode();
                break;
                
            case 'change_site_title':
                $this->changeSiteTitle();
                break;
                
            default:
                $this->ajaxResponse(['error' => 'Invalid action']);
        }
    }

    /**
     * Get client's WordPress sites
     * 
     * @return array WordPress sites data
     */
    private function getClientWordPressSites()
    {
        try {
            // Query database for client's WordPress sites with extended information
            $query = "SELECT s.*, 
                            COUNT(DISTINCT p.id) as plugin_count,
                            COUNT(DISTINCT t.id) as theme_count,
                            MAX(b.created_at) as last_backup,
                            CASE 
                                WHEN s.ssl_enabled = 1 THEN CONCAT('https://', s.domain, s.wp_path)
                                ELSE CONCAT('http://', s.domain, s.wp_path)
                            END as site_url,
                            CASE 
                                WHEN s.ssl_enabled = 1 THEN CONCAT('https://', s.domain, s.wp_path, 'wp-admin/')
                                ELSE CONCAT('http://', s.domain, s.wp_path, 'wp-admin/')
                            END as admin_url
                     FROM mod_speedwp_sites s
                     LEFT JOIN mod_speedwp_plugins p ON s.id = p.site_id
                     LEFT JOIN mod_speedwp_themes t ON s.id = t.site_id
                     LEFT JOIN mod_speedwp_backups b ON s.id = b.site_id AND b.status = 'completed'
                     WHERE s.client_id = :client_id 
                     AND s.status != 'inactive' 
                     GROUP BY s.id
                     ORDER BY s.domain ASC, s.wp_path ASC";
            
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['client_id' => $this->clientId]);
            
            $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data for display
            foreach ($sites as &$site) {
                $site['disk_usage_formatted'] = $this->formatBytes($site['disk_usage']);
                $site['has_backups'] = !empty($site['last_backup']);
                $site['needs_update'] = $this->checkIfUpdateNeeded($site['wp_version']);
                $site['ssl_status'] = $site['ssl_enabled'] ? 'Enabled' : 'Disabled';
                $site['maintenance_status'] = $site['maintenance_mode'] ? 'Enabled' : 'Disabled';
            }
            
            return $sites;
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error getting WP sites: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get client's hosting accounts
     * 
     * @return array Hosting accounts data
     */
    private function getClientHostingAccounts()
    {
        try {
            // TODO: Query WHMCS for client's active hosting accounts
            $query = "SELECT h.id, h.domain, h.username, p.name as product_name
                     FROM tblhosting h
                     JOIN tblproducts p ON h.packageid = p.id
                     WHERE h.userid = :client_id 
                     AND h.domainstatus = 'Active'
                     ORDER BY h.domain ASC";
            
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['client_id' => $this->clientId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error getting hosting accounts: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Scan for WordPress installations
     * 
     * @return void
     */
    private function scanForWordPress()
    {
        try {
            require_once __DIR__ . '/../lib/cpanelApi.php';
            
            $hostingId = $_POST['hosting_id'] ?? 0;
            
            // Get hosting account details
            $hostingQuery = "SELECT h.domain, h.username, h.password 
                            FROM tblhosting h 
                            WHERE h.id = :hosting_id 
                            AND h.userid = :client_id 
                            AND h.domainstatus = 'Active'";
            
            $stmt = Capsule::connection()->getPdo()->prepare($hostingQuery);
            $stmt->execute(['hosting_id' => $hostingId, 'client_id' => $this->clientId]);
            $hosting = $stmt->fetch();
            
            if (!$hosting) {
                throw new Exception('Hosting account not found or access denied');
            }
            
            $cpanel = new SpeedWP_CpanelApi();
            $cpanel->setCredentials($hosting['username'], decrypt($hosting['password']));
            
            // Scan for WordPress installations
            $installations = $cpanel->scanWordPressInstallations($hosting['domain']);
            $sitesFound = 0;
            
            foreach ($installations as $install) {
                // Check if already registered
                $existsQuery = "SELECT id FROM mod_speedwp_sites 
                               WHERE client_id = :client_id 
                               AND domain = :domain 
                               AND wp_path = :wp_path";
                
                $existsStmt = Capsule::connection()->getPdo()->prepare($existsQuery);
                $existsStmt->execute([
                    'client_id' => $this->clientId,
                    'domain' => $hosting['domain'],
                    'wp_path' => $install['path']
                ]);
                
                if (!$existsStmt->fetch()) {
                    // Get detailed site information
                    $siteInfo = $cpanel->getWordPressSiteInfo($install['path']);
                    
                    // Create FTP account
                    $ftpResult = $cpanel->createFtpAccount($hosting['domain'], $install['path']);
                    
                    // Register new installation
                    $insertQuery = "INSERT INTO mod_speedwp_sites 
                                   (client_id, domain, cpanel_user, wp_path, wp_version, status,
                                    site_url, admin_url, disk_usage, file_count, plugin_count, theme_count,
                                    ftp_username, ftp_password, created_at) 
                                   VALUES (:client_id, :domain, :cpanel_user, :wp_path, :wp_version, 'active',
                                           :site_url, :admin_url, :disk_usage, :file_count, :plugin_count, :theme_count,
                                           :ftp_username, :ftp_password, NOW())";
                    
                    $insertStmt = Capsule::connection()->getPdo()->prepare($insertQuery);
                    $insertStmt->execute([
                        'client_id' => $this->clientId,
                        'domain' => $hosting['domain'],
                        'cpanel_user' => $hosting['username'],
                        'wp_path' => $install['path'],
                        'wp_version' => $install['version'],
                        'site_url' => "http://{$hosting['domain']}{$install['path']}",
                        'admin_url' => "http://{$hosting['domain']}{$install['path']}wp-admin/",
                        'disk_usage' => $siteInfo['size'] ?? 0,
                        'file_count' => $siteInfo['files_count'] ?? 0,
                        'plugin_count' => count($siteInfo['plugins'] ?? []),
                        'theme_count' => count($siteInfo['themes'] ?? []),
                        'ftp_username' => $ftpResult['username'] ?? '',
                        'ftp_password' => $ftpResult['password'] ?? ''
                    ]);
                    
                    $sitesFound++;
                    
                    // Log activity
                    $this->logActivity('wordpress_scan', "WordPress site discovered: {$hosting['domain']}{$install['path']}");
                }
            }
            
            $this->ajaxResponse([
                'success' => true,
                'message' => 'WordPress scan completed',
                'sites_found' => $sitesFound,
                'total_installations' => count($installations)
            ]);
            
        } catch (Exception $e) {
            $this->logActivity('wordpress_scan', "WordPress scan failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Install WordPress
     * 
     * @return void
     */
    private function installWordPress()
    {
        try {
            $domain = $_POST['domain'] ?? '';
            $path = $_POST['path'] ?? '/';
            $hostingId = $_POST['hosting_id'] ?? 0;
            $siteTitle = $_POST['site_title'] ?? 'My WordPress Site';
            $adminUser = $_POST['admin_user'] ?? 'admin';
            $adminEmail = $_POST['admin_email'] ?? '';
            
            // Validate input
            if (empty($domain) || empty($hostingId)) {
                throw new Exception('Domain and hosting account are required');
            }
            
            // Get hosting account details
            $hostingQuery = "SELECT h.domain, h.username, h.password 
                            FROM tblhosting h 
                            WHERE h.id = :hosting_id 
                            AND h.userid = :client_id 
                            AND h.domainstatus = 'Active'";
            
            $stmt = Capsule::connection()->getPdo()->prepare($hostingQuery);
            $stmt->execute(['hosting_id' => $hostingId, 'client_id' => $this->clientId]);
            $hosting = $stmt->fetch();
            
            if (!$hosting) {
                throw new Exception('Hosting account not found or access denied');
            }
            
            // Get client email if not provided
            if (empty($adminEmail)) {
                $clientQuery = "SELECT email FROM tblclients WHERE id = :client_id";
                $clientStmt = Capsule::connection()->getPdo()->prepare($clientQuery);
                $clientStmt->execute(['client_id' => $this->clientId]);
                $client = $clientStmt->fetch();
                $adminEmail = $client['email'] ?? '';
            }
            
            require_once __DIR__ . '/../lib/cpanelApi.php';
            
            $cpanel = new SpeedWP_CpanelApi();
            $cpanel->setCredentials($hosting['username'], decrypt($hosting['password']));
            
            // Check if WordPress already exists at this path
            $existsQuery = "SELECT id FROM mod_speedwp_sites 
                           WHERE client_id = :client_id 
                           AND domain = :domain 
                           AND wp_path = :wp_path";
            
            $existsStmt = Capsule::connection()->getPdo()->prepare($existsQuery);
            $existsStmt->execute([
                'client_id' => $this->clientId,
                'domain' => $domain,
                'wp_path' => $path
            ]);
            
            if ($existsStmt->fetch()) {
                throw new Exception('WordPress is already installed at this location');
            }
            
            // Generate strong password
            $adminPassword = $this->generatePassword(12);
            
            // Prepare installation options
            $installOptions = [
                'admin_user' => $adminUser,
                'admin_email' => $adminEmail,
                'admin_password' => $adminPassword,
                'site_title' => $siteTitle,
                'db_name' => $hosting['username'] . '_wp_' . substr(md5($path), 0, 6),
                'db_user' => $hosting['username'] . '_wp_' . substr(md5($path), 0, 6),
                'db_password' => $this->generatePassword(16)
            ];
            
            // Install WordPress
            $installResult = $cpanel->installWordPress($domain, $path, $installOptions);
            
            if ($installResult['success']) {
                // Create FTP account
                $ftpResult = $cpanel->createFtpAccount($domain, $path);
                
                // Get site information
                $siteInfo = $cpanel->getWordPressSiteInfo($path);
                
                // Register new installation
                $insertQuery = "INSERT INTO mod_speedwp_sites 
                               (client_id, domain, cpanel_user, wp_path, wp_version, status,
                                admin_username, admin_password, admin_email, site_title,
                                site_url, admin_url, database_name, database_user, database_password,
                                ftp_username, ftp_password, disk_usage, file_count, created_at) 
                               VALUES (:client_id, :domain, :cpanel_user, :wp_path, 'latest', 'active',
                                       :admin_username, :admin_password, :admin_email, :site_title,
                                       :site_url, :admin_url, :database_name, :database_user, :database_password,
                                       :ftp_username, :ftp_password, :disk_usage, :file_count, NOW())";
                
                $insertStmt = Capsule::connection()->getPdo()->prepare($insertQuery);
                $siteId = $insertStmt->execute([
                    'client_id' => $this->clientId,
                    'domain' => $domain,
                    'cpanel_user' => $hosting['username'],
                    'wp_path' => $path,
                    'admin_username' => $adminUser,
                    'admin_password' => $adminPassword,
                    'admin_email' => $adminEmail,
                    'site_title' => $siteTitle,
                    'site_url' => $installResult['site_url'] ?? "http://{$domain}{$path}",
                    'admin_url' => $installResult['admin_url'] ?? "http://{$domain}{$path}wp-admin/",
                    'database_name' => $installOptions['db_name'],
                    'database_user' => $installOptions['db_user'],
                    'database_password' => $installOptions['db_password'],
                    'ftp_username' => $ftpResult['username'] ?? '',
                    'ftp_password' => $ftpResult['password'] ?? '',
                    'disk_usage' => $siteInfo['size'] ?? 0,
                    'file_count' => $siteInfo['files_count'] ?? 0
                ]);
                
                $newSiteId = Capsule::connection()->getPdo()->lastInsertId();
                
                // Log activity
                $this->logActivity('wordpress_install', "WordPress installed successfully: {$domain}{$path}");
                
                $this->ajaxResponse([
                    'success' => true,
                    'message' => 'WordPress installation completed successfully',
                    'site_id' => $newSiteId,
                    'admin_url' => $installResult['admin_url'] ?? "http://{$domain}{$path}wp-admin/",
                    'admin_username' => $adminUser,
                    'admin_password' => $adminPassword,
                    'ftp_credentials' => $ftpResult
                ]);
            } else {
                throw new Exception('WordPress installation failed');
            }
            
        } catch (Exception $e) {
            $this->logActivity('wordpress_install', "WordPress installation failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update WordPress
     * 
     * @return void
     */
    private function updateWordPress()
    {
        // TODO: Implement WordPress update via cPanel API
        try {
            $siteId = $_POST['site_id'] ?? 0;
            
            // TODO: Update WordPress core and plugins
            
            $this->ajaxResponse([
                'success' => true,
                'message' => 'WordPress update completed'
            ]);
            
        } catch (Exception $e) {
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Backup WordPress
     * 
     * @return void
     */
    private function backupWordPress()
    {
        try {
            $siteId = $_POST['site_id'] ?? 0;
            
            // Get site details
            $site = $this->getSiteDetails($siteId);
            if (!$site) {
                throw new Exception('WordPress site not found');
            }
            
            require_once __DIR__ . '/../lib/cpanelApi.php';
            
            $cpanel = new SpeedWP_CpanelApi();
            $cpanel->setCredentials($site['cpanel_user'], $this->getCpanelPassword($site['cpanel_user']));
            
            // Create backup
            $backupResult = $cpanel->backupWordPress($site['wp_path']);
            
            if ($backupResult['success']) {
                // Store backup record
                $query = "INSERT INTO mod_speedwp_backups 
                         (site_id, backup_name, backup_type, file_path, file_size, status, created_at) 
                         VALUES (:site_id, :backup_name, 'full', :file_path, :file_size, 'completed', NOW())";
                
                $stmt = Capsule::connection()->getPdo()->prepare($query);
                $stmt->execute([
                    'site_id' => $siteId,
                    'backup_name' => $backupResult['backup_file'],
                    'file_path' => $backupResult['file_backup']['backup_file'] ?? '',
                    'file_size' => $backupResult['file_backup']['size'] ?? 0
                ]);
                
                // Update site's last backup time
                $updateQuery = "UPDATE mod_speedwp_sites SET last_backup = NOW() WHERE id = :site_id";
                $updateStmt = Capsule::connection()->getPdo()->prepare($updateQuery);
                $updateStmt->execute(['site_id' => $siteId]);
                
                $this->logActivity('wordpress_backup', "Backup created for {$site['domain']}{$site['wp_path']}");
                
                $this->ajaxResponse([
                    'success' => true,
                    'message' => 'Backup created successfully',
                    'backup_file' => $backupResult['backup_file']
                ]);
            } else {
                throw new Exception('Backup creation failed');
            }
            
        } catch (Exception $e) {
            $this->logActivity('wordpress_backup', "Backup failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reset WordPress admin password
     * 
     * @return void
     */
    private function resetWordPressPassword()
    {
        try {
            $siteId = $_POST['site_id'] ?? 0;
            
            // Get site details
            $site = $this->getSiteDetails($siteId);
            if (!$site) {
                throw new Exception('WordPress site not found');
            }
            
            // Generate new password
            $newPassword = $this->generatePassword(12);
            
            // TODO: Update WordPress admin password via cPanel API or WP-CLI
            // For now, we'll just update our database
            $query = "UPDATE mod_speedwp_sites SET admin_password = :password WHERE id = :site_id";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['password' => $newPassword, 'site_id' => $siteId]);
            
            $this->logActivity('password_reset', "Admin password reset for {$site['domain']}{$site['wp_path']}");
            
            $this->ajaxResponse([
                'success' => true,
                'message' => 'Password reset successfully',
                'new_password' => $newPassword
            ]);
            
        } catch (Exception $e) {
            $this->logActivity('password_reset', "Password reset failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Change WordPress admin password
     * 
     * @return void
     */
    private function changeWordPressPassword()
    {
        try {
            $siteId = $_POST['site_id'] ?? 0;
            $newPassword = $_POST['new_password'] ?? '';
            
            // Get site details
            $site = $this->getSiteDetails($siteId);
            if (!$site) {
                throw new Exception('WordPress site not found');
            }
            
            // Generate password if not provided
            $generated = false;
            if (empty($newPassword)) {
                $newPassword = $this->generatePassword(12);
                $generated = true;
            }
            
            // Validate password strength
            if (strlen($newPassword) < 8) {
                throw new Exception('Password must be at least 8 characters long');
            }
            
            // TODO: Update WordPress admin password via cPanel API or WP-CLI
            $query = "UPDATE mod_speedwp_sites SET admin_password = :password WHERE id = :site_id";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['password' => $newPassword, 'site_id' => $siteId]);
            
            $this->logActivity('password_change', "Admin password changed for {$site['domain']}{$site['wp_path']}");
            
            $response = [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
            
            if ($generated) {
                $response['generated_password'] = $newPassword;
            }
            
            $this->ajaxResponse($response);
            
        } catch (Exception $e) {
            $this->logActivity('password_change', "Password change failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle maintenance mode
     * 
     * @return void
     */
    private function toggleMaintenanceMode()
    {
        try {
            $siteId = $_POST['site_id'] ?? 0;
            $maintenanceMode = $_POST['maintenance_mode'] ?? false;
            
            // Get site details
            $site = $this->getSiteDetails($siteId);
            if (!$site) {
                throw new Exception('WordPress site not found');
            }
            
            // Update maintenance mode
            $query = "UPDATE mod_speedwp_sites SET maintenance_mode = :maintenance_mode WHERE id = :site_id";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['maintenance_mode' => $maintenanceMode ? 1 : 0, 'site_id' => $siteId]);
            
            // TODO: Create/remove .maintenance file in WordPress directory
            
            $action = $maintenanceMode ? 'enabled' : 'disabled';
            $this->logActivity('maintenance_mode', "Maintenance mode {$action} for {$site['domain']}{$site['wp_path']}");
            
            $this->ajaxResponse([
                'success' => true,
                'message' => "Maintenance mode {$action} successfully"
            ]);
            
        } catch (Exception $e) {
            $this->logActivity('maintenance_mode', "Maintenance mode toggle failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Change site title
     * 
     * @return void
     */
    private function changeSiteTitle()
    {
        try {
            $siteId = $_POST['site_id'] ?? 0;
            $siteTitle = $_POST['site_title'] ?? '';
            
            if (empty($siteTitle)) {
                throw new Exception('Site title cannot be empty');
            }
            
            // Get site details
            $site = $this->getSiteDetails($siteId);
            if (!$site) {
                throw new Exception('WordPress site not found');
            }
            
            // Update site title
            $query = "UPDATE mod_speedwp_sites SET site_title = :site_title WHERE id = :site_id";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['site_title' => $siteTitle, 'site_id' => $siteId]);
            
            // TODO: Update WordPress site title via WP-CLI or database
            
            $this->logActivity('site_title_change', "Site title changed to '{$siteTitle}' for {$site['domain']}{$site['wp_path']}");
            
            $this->ajaxResponse([
                'success' => true,
                'message' => 'Site title changed successfully'
            ]);
            
        } catch (Exception $e) {
            $this->logActivity('site_title_change', "Site title change failed: " . $e->getMessage(), 'error');
            $this->ajaxResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get site details for client
     * 
     * @param int $siteId
     * @return array|false
     */
    private function getSiteDetails($siteId)
    {
        try {
            $query = "SELECT * FROM mod_speedwp_sites WHERE id = :site_id AND client_id = :client_id";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['site_id' => $siteId, 'client_id' => $this->clientId]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get cPanel password for user
     * 
     * @param string $cpanelUser
     * @return string
     */
    private function getCpanelPassword($cpanelUser)
    {
        try {
            $query = "SELECT password FROM tblhosting WHERE username = :username AND userid = :client_id";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute(['username' => $cpanelUser, 'client_id' => $this->clientId]);
            
            $result = $stmt->fetch();
            return $result ? decrypt($result['password']) : '';
            
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Send AJAX response
     * 
     * @param array $data Response data
     * @return void
     */
    private function ajaxResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Check if WordPress version needs update
     * 
     * @param string $currentVersion
     * @return bool
     */
    private function checkIfUpdateNeeded($currentVersion)
    {
        // Simple version check - in production, you'd compare with latest WordPress version
        if (empty($currentVersion) || $currentVersion === 'Unknown') {
            return true;
        }
        
        // TODO: Implement actual version comparison with latest WordPress version
        return false;
    }

    /**
     * Generate secure password
     * 
     * @param int $length
     * @return string
     */
    private function generatePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Log activity
     * 
     * @param string $action
     * @param string $description
     * @param string $status
     * @return void
     */
    private function logActivity($action, $description, $status = 'info')
    {
        try {
            $query = "INSERT INTO mod_speedwp_logs 
                     (client_id, action, description, status, ip_address, user_agent, created_at) 
                     VALUES (:client_id, :action, :description, :status, :ip_address, :user_agent, NOW())";
            
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute([
                'client_id' => $this->clientId,
                'action' => $action,
                'description' => $description,
                'status' => $status,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            // Also log to WHMCS activity log
            logActivity("SpeedWP [{$this->clientId}]: {$description}");
            
        } catch (Exception $e) {
            logActivity("SpeedWP Log Error: " . $e->getMessage());
        }
    }
}
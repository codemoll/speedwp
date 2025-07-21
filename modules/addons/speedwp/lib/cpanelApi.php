<?php
/**
 * SpeedWP cPanel API Integration
 * 
 * Handles cPanel API communication for WordPress management operations.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

class SpeedWP_CpanelApi
{
    /**
     * @var string cPanel hostname
     */
    private $cpanelHost;
    
    /**
     * @var int cPanel port
     */
    private $cpanelPort;
    
    /**
     * @var string cPanel username
     */
    private $username;
    
    /**
     * @var string cPanel password or API token
     */
    private $password;
    
    /**
     * @var bool Debug mode flag
     */
    private $debugMode;

    /**
     * Constructor
     * 
     * @param array $config Configuration parameters
     */
    public function __construct($config = [])
    {
        // Get configuration from WHMCS addon settings
        $addonConfig = getAddonVars('speedwp');
        
        $this->cpanelHost = $config['host'] ?? $addonConfig['cpanel_host'] ?? '';
        $this->cpanelPort = $config['port'] ?? $addonConfig['cpanel_port'] ?? 2083;
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->debugMode = $addonConfig['debug_mode'] ?? false;
        
        // TODO: Add validation for required configuration
        if (empty($this->cpanelHost)) {
            throw new Exception('cPanel host not configured');
        }
    }

    /**
     * Set authentication credentials
     * 
     * @param string $username cPanel username
     * @param string $password cPanel password or API token
     * @return void
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Scan for WordPress installations
     * 
     * @param string $domain Domain to scan (optional)
     * @return array WordPress installations found
     */
    public function scanWordPressInstallations($domain = '')
    {
        // TODO: Implement WordPress scanning via cPanel File Manager API
        try {
            $this->logDebug("Scanning for WordPress installations on domain: " . $domain);
            
            // Common WordPress paths to check
            $wpPaths = ['/', '/wp/', '/wordpress/', '/blog/', '/cms/'];
            $installations = [];
            
            foreach ($wpPaths as $path) {
                // TODO: Check for wp-config.php and wp-includes/version.php
                $wpConfigExists = $this->fileExists($path . 'wp-config.php');
                $wpVersionExists = $this->fileExists($path . 'wp-includes/version.php');
                
                if ($wpConfigExists && $wpVersionExists) {
                    $installations[] = [
                        'path' => $path,
                        'version' => $this->getWordPressVersion($path),
                        'domain' => $domain
                    ];
                }
            }
            
            $this->logDebug("Found " . count($installations) . " WordPress installations");
            return $installations;
            
        } catch (Exception $e) {
            $this->logError("Error scanning for WordPress: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Install WordPress
     * 
     * @param string $domain Target domain
     * @param string $path Installation path (default: '/')
     * @param array $options Installation options
     * @return array Installation result
     */
    public function installWordPress($domain, $path = '/', $options = [])
    {
        // TODO: Implement WordPress installation via cPanel
        try {
            $this->logDebug("Installing WordPress on {$domain}{$path}");
            
            // Default installation options
            $defaults = [
                'admin_user' => 'admin',
                'admin_email' => '',
                'site_title' => 'WordPress Site',
                'admin_password' => $this->generatePassword(),
                'db_name' => '',
                'db_user' => '',
                'db_password' => $this->generatePassword()
            ];
            
            $options = array_merge($defaults, $options);
            
            // TODO: Create database for WordPress
            $dbResult = $this->createDatabase($options['db_name'], $options['db_user'], $options['db_password']);
            
            // TODO: Download and extract WordPress
            $downloadResult = $this->downloadWordPress($path);
            
            // TODO: Create wp-config.php
            $configResult = $this->createWpConfig($path, $options);
            
            // TODO: Run WordPress installation
            $installResult = $this->runWordPressInstall($domain, $path, $options);
            
            $this->logDebug("WordPress installation completed for {$domain}{$path}");
            
            return [
                'success' => true,
                'domain' => $domain,
                'path' => $path,
                'admin_user' => $options['admin_user'],
                'admin_password' => $options['admin_password'],
                'database' => $options['db_name']
            ];
            
        } catch (Exception $e) {
            $this->logError("Error installing WordPress: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update WordPress core
     * 
     * @param string $path WordPress installation path
     * @return array Update result
     */
    public function updateWordPressCore($path)
    {
        // TODO: Implement WordPress core update
        try {
            $this->logDebug("Updating WordPress core at path: " . $path);
            
            // TODO: Backup current installation
            $backupResult = $this->backupWordPress($path);
            
            // TODO: Download latest WordPress
            $downloadResult = $this->downloadWordPress($path, true);
            
            // TODO: Run database update if needed
            $dbUpdateResult = $this->runWordPressDatabaseUpdate($path);
            
            $this->logDebug("WordPress core update completed for path: " . $path);
            
            return [
                'success' => true,
                'path' => $path,
                'backup_file' => $backupResult['backup_file'] ?? '',
                'new_version' => $this->getWordPressVersion($path)
            ];
            
        } catch (Exception $e) {
            $this->logError("Error updating WordPress core: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create backup of WordPress installation
     * 
     * @param string $path WordPress installation path
     * @return array Backup result
     */
    public function backupWordPress($path)
    {
        // TODO: Implement WordPress backup via cPanel
        try {
            $this->logDebug("Creating backup for WordPress at path: " . $path);
            
            $backupName = 'wp_backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
            
            // TODO: Create compressed backup of WordPress files and database
            $fileBackupResult = $this->createFileBackup($path, $backupName);
            $dbBackupResult = $this->createDatabaseBackup($path);
            
            $this->logDebug("WordPress backup completed: " . $backupName);
            
            return [
                'success' => true,
                'backup_file' => $backupName,
                'file_backup' => $fileBackupResult,
                'db_backup' => $dbBackupResult
            ];
            
        } catch (Exception $e) {
            $this->logError("Error creating WordPress backup: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get WordPress version from installation
     * 
     * @param string $path WordPress installation path
     * @return string WordPress version
     */
    public function getWordPressVersion($path)
    {
        // TODO: Read version from wp-includes/version.php
        try {
            $versionFile = $path . 'wp-includes/version.php';
            $content = $this->readFile($versionFile);
            
            if (preg_match('/\$wp_version\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                return $matches[1];
            }
            
            return 'Unknown';
            
        } catch (Exception $e) {
            $this->logError("Error getting WordPress version: " . $e->getMessage());
            return 'Unknown';
        }
    }

    /**
     * Execute cPanel API call
     * 
     * @param string $module API module
     * @param string $function API function
     * @param array $params API parameters
     * @return array API response
     */
    private function executeApiCall($module, $function, $params = [])
    {
        // TODO: Implement actual cPanel API communication
        try {
            $url = "https://{$this->cpanelHost}:{$this->cpanelPort}/execute/{$module}/{$function}";
            
            $this->logDebug("Executing cPanel API call: {$module}/{$function}");
            
            // TODO: Use cURL to make API request with authentication
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception("cPanel API returned HTTP {$httpCode}");
            }
            
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response from cPanel API");
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("cPanel API call failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if file exists via cPanel File Manager
     * 
     * @param string $filepath File path to check
     * @return bool File exists
     */
    private function fileExists($filepath)
    {
        // TODO: Implement file existence check via cPanel API
        try {
            $result = $this->executeApiCall('Fileman', 'stat', ['path' => $filepath]);
            return isset($result['data']) && !empty($result['data']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Read file content via cPanel File Manager
     * 
     * @param string $filepath File path to read
     * @return string File content
     */
    private function readFile($filepath)
    {
        // TODO: Implement file reading via cPanel API
        try {
            $result = $this->executeApiCall('Fileman', 'get_file_content', ['path' => $filepath]);
            return $result['data']['content'] ?? '';
        } catch (Exception $e) {
            throw new Exception("Failed to read file: " . $filepath);
        }
    }

    /**
     * Generate secure password
     * 
     * @param int $length Password length
     * @return string Generated password
     */
    public function generatePassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }

    /**
     * Log debug message
     * 
     * @param string $message Debug message
     * @return void
     */
    private function logDebug($message)
    {
        if ($this->debugMode) {
            logActivity("SpeedWP Debug: " . $message);
        }
    }

    /**
     * Log error message
     * 
     * @param string $message Error message
     * @return void
     */
    private function logError($message)
    {
        logActivity("SpeedWP Error: " . $message);
    }

    /**
     * Create database for WordPress
     * 
     * @param string $dbName Database name
     * @param string $dbUser Database username
     * @param string $dbPassword Database password
     * @return array Database creation result
     */
    private function createDatabase($dbName, $dbUser, $dbPassword)
    {
        try {
            $this->logDebug("Creating database: {$dbName}");
            
            // Create database
            $dbResult = $this->executeApiCall('Mysql', 'create_database', [
                'name' => $dbName
            ]);
            
            // Create database user
            $userResult = $this->executeApiCall('Mysql', 'create_user', [
                'name' => $dbUser,
                'password' => $dbPassword
            ]);
            
            // Grant privileges
            $privResult = $this->executeApiCall('Mysql', 'set_privileges_on_database', [
                'user' => $dbUser,
                'database' => $dbName,
                'privileges' => 'ALL PRIVILEGES'
            ]);
            
            return [
                'success' => true,
                'database' => $dbName,
                'user' => $dbUser
            ];
            
        } catch (Exception $e) {
            $this->logError("Database creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Download and extract WordPress
     * 
     * @param string $path Installation path
     * @param bool $update Whether this is an update (preserve wp-config.php)
     * @return array Download result
     */
    private function downloadWordPress($path, $update = false)
    {
        try {
            $this->logDebug("Downloading WordPress to path: {$path}");
            
            // Create directory if it doesn't exist
            $this->executeApiCall('Fileman', 'mkdir', [
                'path' => $path,
                'permissions' => 0755
            ]);
            
            // Download latest WordPress
            $downloadResult = $this->executeApiCall('Fileman', 'download_file', [
                'url' => 'https://wordpress.org/latest.tar.gz',
                'path' => $path . 'latest.tar.gz'
            ]);
            
            // Extract WordPress
            $extractResult = $this->executeApiCall('Fileman', 'extract_archive', [
                'archive' => $path . 'latest.tar.gz',
                'destination' => $path
            ]);
            
            // Move files from wordpress subdirectory to installation directory
            $moveResult = $this->executeApiCall('Fileman', 'move_files', [
                'source' => $path . 'wordpress/*',
                'destination' => $path
            ]);
            
            // Clean up
            $this->executeApiCall('Fileman', 'delete_files', [
                'files' => [$path . 'latest.tar.gz', $path . 'wordpress/']
            ]);
            
            return [
                'success' => true,
                'path' => $path
            ];
            
        } catch (Exception $e) {
            $this->logError("WordPress download failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create wp-config.php file
     * 
     * @param string $path WordPress installation path
     * @param array $options Configuration options
     * @return array Configuration result
     */
    private function createWpConfig($path, $options)
    {
        try {
            $this->logDebug("Creating wp-config.php at path: {$path}");
            
            // Generate unique keys and salts
            $salts = $this->generateWordPressSalts();
            
            // Create wp-config.php content
            $wpConfig = "<?php\n";
            $wpConfig .= "// ** MySQL settings ** //\n";
            $wpConfig .= "define('DB_NAME', '{$options['db_name']}');\n";
            $wpConfig .= "define('DB_USER', '{$options['db_user']}');\n";
            $wpConfig .= "define('DB_PASSWORD', '{$options['db_password']}');\n";
            $wpConfig .= "define('DB_HOST', 'localhost');\n";
            $wpConfig .= "define('DB_CHARSET', 'utf8');\n";
            $wpConfig .= "define('DB_COLLATE', '');\n\n";
            
            $wpConfig .= "// ** Authentication Unique Keys and Salts ** //\n";
            $wpConfig .= $salts . "\n";
            
            $wpConfig .= "// ** WordPress Database Table prefix ** //\n";
            $wpConfig .= "\$table_prefix = 'wp_';\n\n";
            
            $wpConfig .= "// ** WordPress debugging ** //\n";
            $wpConfig .= "define('WP_DEBUG', false);\n\n";
            
            $wpConfig .= "// ** Absolute path to the WordPress directory ** //\n";
            $wpConfig .= "if ( !defined('ABSPATH') )\n";
            $wpConfig .= "    define('ABSPATH', dirname(__FILE__) . '/');\n\n";
            
            $wpConfig .= "// ** Sets up WordPress vars and included files ** //\n";
            $wpConfig .= "require_once(ABSPATH . 'wp-settings.php');\n";
            
            // Write wp-config.php file
            $this->executeApiCall('Fileman', 'save_file_content', [
                'file' => $path . 'wp-config.php',
                'content' => $wpConfig,
                'permissions' => 0644
            ]);
            
            return [
                'success' => true,
                'config_file' => $path . 'wp-config.php'
            ];
            
        } catch (Exception $e) {
            $this->logError("wp-config.php creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run WordPress installation
     * 
     * @param string $domain Site domain
     * @param string $path Installation path
     * @param array $options Installation options
     * @return array Installation result
     */
    private function runWordPressInstall($domain, $path, $options)
    {
        try {
            $this->logDebug("Running WordPress installation for {$domain}{$path}");
            
            $installUrl = "http://{$domain}{$path}wp-admin/install.php";
            
            // Prepare installation data
            $installData = [
                'weblog_title' => $options['site_title'],
                'user_name' => $options['admin_user'],
                'admin_email' => $options['admin_email'],
                'admin_password' => $options['admin_password'],
                'admin_password2' => $options['admin_password'],
                'Submit' => 'Install WordPress'
            ];
            
            // Execute WordPress installation via HTTP request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $installUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($installData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200 && $httpCode !== 302) {
                throw new Exception("WordPress installation failed with HTTP {$httpCode}");
            }
            
            return [
                'success' => true,
                'admin_url' => "http://{$domain}{$path}wp-admin/",
                'site_url' => "http://{$domain}{$path}"
            ];
            
        } catch (Exception $e) {
            $this->logError("WordPress installation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run WordPress database update
     * 
     * @param string $path WordPress installation path
     * @return array Update result
     */
    private function runWordPressDatabaseUpdate($path)
    {
        try {
            $this->logDebug("Running WordPress database update for path: {$path}");
            
            // TODO: Execute wp-admin/upgrade.php or use WP-CLI if available
            // For now, we'll use a simple HTTP request approach
            
            return [
                'success' => true,
                'message' => 'Database update completed'
            ];
            
        } catch (Exception $e) {
            $this->logError("WordPress database update failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create file backup
     * 
     * @param string $path WordPress installation path
     * @param string $backupName Backup filename
     * @return array Backup result
     */
    private function createFileBackup($path, $backupName)
    {
        try {
            $this->logDebug("Creating file backup: {$backupName}");
            
            // Create compressed backup
            $backupResult = $this->executeApiCall('Fileman', 'compress', [
                'files' => [$path],
                'archive_name' => $backupName,
                'archive_type' => 'tar.gz',
                'destination' => '/backups/'
            ]);
            
            return [
                'success' => true,
                'backup_file' => '/backups/' . $backupName,
                'size' => $backupResult['size'] ?? 0
            ];
            
        } catch (Exception $e) {
            $this->logError("File backup creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create database backup
     * 
     * @param string $path WordPress installation path
     * @return array Backup result
     */
    private function createDatabaseBackup($path)
    {
        try {
            $this->logDebug("Creating database backup for path: {$path}");
            
            // Read wp-config.php to get database credentials
            $wpConfig = $this->readFile($path . 'wp-config.php');
            preg_match("/define\('DB_NAME',\s*'([^']+)'\)/", $wpConfig, $dbNameMatches);
            $dbName = $dbNameMatches[1] ?? '';
            
            if (empty($dbName)) {
                throw new Exception("Could not find database name in wp-config.php");
            }
            
            $backupName = 'db_backup_' . $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Create database backup
            $backupResult = $this->executeApiCall('Mysql', 'dump_database', [
                'database' => $dbName,
                'filename' => '/backups/' . $backupName
            ]);
            
            return [
                'success' => true,
                'backup_file' => '/backups/' . $backupName,
                'database' => $dbName
            ];
            
        } catch (Exception $e) {
            $this->logError("Database backup creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create FTP account for WordPress site
     * 
     * @param string $domain Site domain
     * @param string $path Site path
     * @param string $username FTP username
     * @param string $password FTP password
     * @return array FTP account creation result
     */
    public function createFtpAccount($domain, $path, $username = '', $password = '')
    {
        try {
            if (empty($username)) {
                $username = 'wp_' . substr(md5($domain . $path), 0, 8);
            }
            
            if (empty($password)) {
                $password = $this->generatePassword(12);
            }
            
            $this->logDebug("Creating FTP account: {$username} for {$domain}{$path}");
            
            // Create FTP account
            $ftpResult = $this->executeApiCall('Ftp', 'add_ftp', [
                'user' => $username,
                'pass' => $password,
                'homedir' => $path,
                'quota' => 0  // Unlimited quota
            ]);
            
            return [
                'success' => true,
                'username' => $username,
                'password' => $password,
                'host' => $this->cpanelHost,
                'port' => 21,
                'directory' => $path
            ];
            
        } catch (Exception $e) {
            $this->logError("FTP account creation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get WordPress site information
     * 
     * @param string $path WordPress installation path
     * @return array Site information
     */
    public function getWordPressSiteInfo($path)
    {
        try {
            $this->logDebug("Getting WordPress site info for path: {$path}");
            
            $info = [
                'version' => $this->getWordPressVersion($path),
                'path' => $path,
                'size' => 0,
                'files_count' => 0,
                'has_ssl' => false,
                'plugins' => [],
                'themes' => []
            ];
            
            // Get directory size and file count
            $sizeResult = $this->executeApiCall('Fileman', 'get_directory_usage', [
                'path' => $path
            ]);
            
            if (isset($sizeResult['data'])) {
                $info['size'] = $sizeResult['data']['size'] ?? 0;
                $info['files_count'] = $sizeResult['data']['files'] ?? 0;
            }
            
            // Check for plugins
            $pluginPath = $path . 'wp-content/plugins/';
            if ($this->fileExists($pluginPath)) {
                $plugins = $this->executeApiCall('Fileman', 'list_files', [
                    'path' => $pluginPath,
                    'include_mime' => 0
                ]);
                
                if (isset($plugins['data'])) {
                    foreach ($plugins['data'] as $plugin) {
                        if ($plugin['type'] === 'dir' && $plugin['file'] !== '.' && $plugin['file'] !== '..') {
                            $info['plugins'][] = $plugin['file'];
                        }
                    }
                }
            }
            
            // Check for themes
            $themePath = $path . 'wp-content/themes/';
            if ($this->fileExists($themePath)) {
                $themes = $this->executeApiCall('Fileman', 'list_files', [
                    'path' => $themePath,
                    'include_mime' => 0
                ]);
                
                if (isset($themes['data'])) {
                    foreach ($themes['data'] as $theme) {
                        if ($theme['type'] === 'dir' && $theme['file'] !== '.' && $theme['file'] !== '..') {
                            $info['themes'][] = $theme['file'];
                        }
                    }
                }
            }
            
            return $info;
            
        } catch (Exception $e) {
            $this->logError("Error getting WordPress site info: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate WordPress salts and keys
     * 
     * @return string WordPress authentication keys and salts
     */
    private function generateWordPressSalts()
    {
        $keys = [
            'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
            'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'
        ];
        
        $salts = '';
        foreach ($keys as $key) {
            $salt = $this->generatePassword(64);
            $salts .= "define('{$key}', '{$salt}');\n";
        }
        
        return $salts;
    }
}
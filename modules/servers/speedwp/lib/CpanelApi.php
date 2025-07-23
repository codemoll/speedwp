<?php
/**
 * SpeedWP cPanel API Integration for Server Module
 * 
 * Handles cPanel/WHM API communication for hosting account provisioning
 * and WordPress management via WP Toolkit integration.
 * 
 * @package    SpeedWP Server Module
 * @version    1.0.0
 * @author     SpeedWP Development Team
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

class SpeedWP_CpanelApi
{
    /**
     * @var string WHM/cPanel hostname
     */
    private $host;
    
    /**
     * @var int WHM port
     */
    private $port;
    
    /**
     * @var string WHM username
     */
    private $username;
    
    /**
     * @var string WHM password or API token
     */
    private $password;
    
    /**
     * @var bool Debug mode flag
     */
    private $debugMode;
    
    /**
     * @var int API timeout in seconds
     */
    private $timeout;

    /**
     * Constructor
     * 
     * @param array $config Configuration parameters
     */
    public function __construct($config = [])
    {
        $this->host = filter_var(trim($config['host'] ?? 'localhost'), FILTER_SANITIZE_STRING);
        $this->port = max(1, min(65535, intval($config['port'] ?? 2087)));
        $this->username = trim($config['username'] ?? 'root');
        $this->password = $config['password'] ?? '';
        $this->timeout = max(60, intval($config['timeout'] ?? 180));
        $this->debugMode = (bool)($config['debug'] ?? false);
        
        // Validate required parameters
        if (!$this->host) {
            throw new Exception('Invalid or missing hostname');
        }
        
        if (!$this->username) {
            throw new Exception('Username is required');
        }
        
        if (!$this->password) {
            throw new Exception('Password or API token is required');
        }
        
        // Log initialization in debug mode
        $this->logDebug("SpeedWP CpanelApi initialized for {$this->host}:{$this->port} with user {$this->username}");
    }

    /**
     * Test connection to WHM server
     * 
     * @return array Connection test result
     */
    public function testConnection()
    {
        try {
            $this->logDebug("Testing connection to WHM server: {$this->host}:{$this->port}");
            
            // Log the connection attempt with sanitized details
            $this->logDebug("Connecting to WHM API with username: {$this->username}");
            
            $result = $this->executeWhmApi('version');
            
            if (isset($result['version'])) {
                $this->logDebug("Connection successful - Server version: {$result['version']}");
                return [
                    'success' => true,
                    'server_info' => $result['version'] . ' on ' . $this->host
                ];
            }
            
            // If we get here, the API call succeeded but didn't return expected data
            throw new Exception('WHM API returned unexpected response format');
            
        } catch (Exception $e) {
            $this->logError("Connection test failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a new cPanel hosting account
     * 
     * @param array $accountDetails Account creation parameters
     * @return array Creation result
     */
    public function createAccount($accountDetails)
    {
        try {
            // Validate required parameters
            $requiredFields = ['user', 'pass', 'domain', 'contactemail'];
            foreach ($requiredFields as $field) {
                if (empty($accountDetails[$field])) {
                    throw new Exception("Missing required field: {$field}");
                }
            }
            
            // Sanitize and validate inputs
            $username = preg_replace('/[^a-zA-Z0-9_-]/', '', trim($accountDetails['user']));
            $domain = filter_var(trim($accountDetails['domain']), FILTER_SANITIZE_STRING);
            $email = filter_var(trim($accountDetails['contactemail']), FILTER_VALIDATE_EMAIL);
            
            if (!$username || strlen($username) < 3) {
                throw new Exception("Invalid username: must be at least 3 characters, alphanumeric only");
            }
            
            if (!$domain) {
                throw new Exception("Invalid domain name");
            }
            
            if (!$email) {
                throw new Exception("Invalid email address");
            }
            
            if (strlen($accountDetails['pass']) < 8) {
                throw new Exception("Password must be at least 8 characters long");
            }
            
            $this->logDebug("Creating cPanel account: {$username}@{$domain}");
            
            // Prepare WHM createacct API parameters
            $params = [
                'username' => $username,
                'password' => $accountDetails['pass'],
                'domain' => $domain,
                'plan' => trim($accountDetails['plan']) ?: 'default',
                'contactemail' => $email,
                'quota' => max(0, intval($accountDetails['quota'] ?? 0)),
                'hasshell' => 0,
                'maxpop' => $accountDetails['maxpop'] ?? 'unlimited',
                'maxsub' => $accountDetails['maxsub'] ?? 'unlimited',
                'maxpark' => $accountDetails['maxpark'] ?? 'unlimited',
                'maxaddon' => $accountDetails['maxaddon'] ?? 'unlimited'
            ];
            
            $this->logDebug("Account parameters: plan={$params['plan']}, quota={$params['quota']}, email={$email}");
            
            $result = $this->executeWhmApi('createacct', $params);
            $this->logDebug("WHM createacct API response received");
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                $this->logDebug("cPanel account created successfully: {$username}");
                return [
                    'success' => true,
                    'message' => 'Account created successfully',
                    'username' => $username,
                    'domain' => $domain
                ];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'Unknown error occurred';
                $this->logError("WHM createacct failed: " . $errorMsg);
                throw new Exception("Account creation failed: " . $errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("Account creation encountered error for {$accountDetails['user']}@{$accountDetails['domain']}: " . $e->getMessage());
            
            // Check if this is a timeout error and implement recovery logic
            if (strpos($e->getMessage(), 'Operation timed out') !== false || 
                strpos($e->getMessage(), 'cURL error') !== false ||
                strpos($e->getMessage(), 'timeout') !== false) {
                
                logActivity("SpeedWP: cURL timeout detected during account creation for {$username}@{$domain}, checking if account was created...");
                
                // Wait a moment for server to complete any pending operations
                sleep(2);
                
                // Check if the account actually exists despite the timeout
                $existsCheck = $this->checkAccountExists($username, $domain);
                
                if ($existsCheck['success'] && $existsCheck['exists']) {
                    // Account was created successfully despite the timeout!
                    logActivity("SpeedWP: SUCCESS - Account {$username}@{$domain} was created successfully despite timeout");
                    $this->logDebug("Timeout recovery successful: account {$username} exists and is active");
                    
                    return [
                        'success' => true,
                        'message' => 'Account created successfully (recovered from timeout)',
                        'username' => $username,
                        'domain' => $domain,
                        'timeout_recovery' => true
                    ];
                } else {
                    // Account was not created, return the original timeout error
                    logActivity("SpeedWP: FAILED - Account {$username}@{$domain} was not created after timeout");
                    $this->logError("Timeout recovery failed: account {$username} does not exist");
                }
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a cPanel account exists
     * 
     * @param string $username Account username
     * @param string $domain Account domain (optional)
     * @return array Account existence check result
     */
    public function checkAccountExists($username, $domain = null)
    {
        try {
            $this->logDebug("Checking if cPanel account exists: {$username}" . ($domain ? "@{$domain}" : ""));
            
            // Use the accountsummary API to check if account exists
            $result = $this->executeWhmApi('accountsummary', ['user' => $username]);
            
            if (isset($result['acct'][0])) {
                $account = $result['acct'][0];
                $accountExists = true;
                $accountDomain = $account['domain'] ?? '';
                
                // If domain is provided, verify it matches
                if ($domain && $accountDomain !== $domain) {
                    $this->logDebug("Account {$username} exists but domain mismatch: expected {$domain}, found {$accountDomain}");
                    $accountExists = false;
                }
                
                $this->logDebug("Account check result: " . ($accountExists ? 'EXISTS' : 'NOT_FOUND'));
                
                return [
                    'success' => true,
                    'exists' => $accountExists,
                    'username' => $username,
                    'domain' => $accountDomain,
                    'status' => $account['suspended'] ?? 'active'
                ];
            } else {
                $this->logDebug("Account {$username} does not exist");
                return [
                    'success' => true,
                    'exists' => false,
                    'username' => $username,
                    'domain' => $domain
                ];
            }
            
        } catch (Exception $e) {
            $this->logError("Account existence check failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'exists' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Install WordPress using WP Toolkit
     * 
     * @param array $wpDetails WordPress installation parameters
     * @return array Installation result
     */
    public function installWordPress($wpDetails)
    {
        try {
            // Validate required parameters
            $requiredFields = ['domain', 'admin_user', 'admin_email'];
            foreach ($requiredFields as $field) {
                if (empty($wpDetails[$field])) {
                    throw new Exception("Missing required WordPress field: {$field}");
                }
            }
            
            // Sanitize and validate inputs
            $domain = filter_var(trim($wpDetails['domain']), FILTER_SANITIZE_STRING);
            $adminUser = preg_replace('/[^a-zA-Z0-9_]/', '', trim($wpDetails['admin_user']));
            $adminEmail = filter_var(trim($wpDetails['admin_email']), FILTER_VALIDATE_EMAIL);
            $siteTitle = htmlspecialchars(trim($wpDetails['site_title'] ?? $domain));
            
            if (!$domain) {
                throw new Exception("Invalid domain for WordPress installation");
            }
            
            if (!$adminUser || strlen($adminUser) < 3) {
                throw new Exception("Invalid WordPress admin username (minimum 3 characters, alphanumeric only)");
            }
            
            if (!$adminEmail) {
                throw new Exception("Invalid email address for WordPress admin");
            }
            
            // Validate WordPress version
            $validVersions = ['latest', '6.4', '6.3', '6.2'];
            $wpVersion = in_array($wpDetails['version'], $validVersions) ? $wpDetails['version'] : 'latest';
            
            $this->logDebug("Installing WordPress on {$domain} via WP Toolkit");
            
            // Generate secure admin password if not provided
            $adminPassword = !empty($wpDetails['admin_pass']) ? $wpDetails['admin_pass'] : $this->generatePassword(12);
            
            // WP Toolkit installation parameters
            $params = [
                'domain' => $domain,
                'path' => '/',
                'admin_username' => $adminUser,
                'admin_password' => $adminPassword,
                'admin_email' => $adminEmail,
                'site_title' => $siteTitle,
                'wp_version' => $wpVersion,
                'locale' => 'en_US'
            ];
            
            // Execute WP Toolkit installation API call
            $result = $this->executeWpToolkitApi('install', $params);
            
            if ($result['success']) {
                // Configure additional WordPress settings
                if ($wpDetails['enable_ssl'] ?? false) {
                    $this->enableWordPressSSL($domain);
                }
                
                if ($wpDetails['enable_backups'] ?? false) {
                    $backupFreq = in_array($wpDetails['backup_frequency'], ['daily', 'weekly', 'monthly']) 
                        ? $wpDetails['backup_frequency'] : 'weekly';
                    $this->setupWordPressBackups($domain, $backupFreq);
                }
                
                $adminUrl = 'https://' . $domain . '/wp-admin/';
                $siteUrl = 'https://' . $domain . '/';
                
                $this->logDebug("WordPress installed successfully on {$domain}");
                
                return [
                    'success' => true,
                    'admin_url' => $adminUrl,
                    'site_url' => $siteUrl,
                    'admin_user' => $adminUser,
                    'admin_pass' => $adminPassword,
                    'wp_version' => $wpVersion
                ];
            } else {
                throw new Exception("WP Toolkit installation failed: " . $result['message']);
            }
            
        } catch (Exception $e) {
            $this->logError("WordPress installation failed for {$wpDetails['domain']}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get WordPress site details from WP Toolkit
     * 
     * @param string $domain Domain name
     * @return array WordPress site details
     */
    public function getWordPressDetails($domain)
    {
        try {
            $this->logDebug("Fetching WordPress details for {$domain}");
            
            $result = $this->executeWpToolkitApi('get_site_info', ['domain' => $domain]);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'domain' => $domain,
                    'wp_version' => $result['wp_version'],
                    'admin_url' => $result['admin_url'],
                    'plugins' => $result['plugins'] ?? [],
                    'themes' => $result['themes'] ?? [],
                    'updates_available' => $result['updates_available'] ?? 0,
                    'last_backup' => $result['last_backup'] ?? null,
                    'ssl_enabled' => $result['ssl_enabled'] ?? false,
                    'auto_updates' => $result['auto_updates'] ?? false
                ];
            }
            
            throw new Exception("Failed to fetch WordPress details");
            
        } catch (Exception $e) {
            $this->logError("Error fetching WordPress details for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Suspend a cPanel account
     * 
     * @param string $username Account username
     * @param string $reason Suspension reason
     * @return array Suspension result
     */
    public function suspendAccount($username, $reason = '')
    {
        try {
            $this->logDebug("Suspending cPanel account: {$username}");
            
            $params = [
                'user' => $username,
                'reason' => $reason ?: 'Suspended via WHMCS'
            ];
            
            $result = $this->executeWhmApi('suspendacct', $params);
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                return ['success' => true, 'message' => 'Account suspended successfully'];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'Unknown error occurred';
                throw new Exception($errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("Account suspension failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Unsuspend a cPanel account
     * 
     * @param string $username Account username
     * @return array Unsuspension result
     */
    public function unsuspendAccount($username)
    {
        try {
            $this->logDebug("Unsuspending cPanel account: {$username}");
            
            $result = $this->executeWhmApi('unsuspendacct', ['user' => $username]);
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                return ['success' => true, 'message' => 'Account unsuspended successfully'];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'Unknown error occurred';
                throw new Exception($errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("Account unsuspension failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Terminate a cPanel account
     * 
     * @param string $username Account username
     * @return array Termination result
     */
    public function terminateAccount($username)
    {
        try {
            $this->logDebug("Terminating cPanel account: {$username}");
            
            $result = $this->executeWhmApi('removeacct', ['user' => $username]);
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                return ['success' => true, 'message' => 'Account terminated successfully'];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'Unknown error occurred';
                throw new Exception($errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("Account termination failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Change cPanel account password
     * 
     * @param string $username Account username
     * @param string $newPassword New password
     * @return array Password change result
     */
    public function changeAccountPassword($username, $newPassword)
    {
        try {
            $this->logDebug("Changing password for cPanel account: {$username}");
            
            $params = [
                'user' => $username,
                'pass' => $newPassword
            ];
            
            $result = $this->executeWhmApi('passwd', $params);
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                return ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'Unknown error occurred';
                throw new Exception($errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("Password change failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get account usage statistics
     * 
     * @param string $username Account username
     * @return array Usage statistics
     */
    public function getAccountUsage($username)
    {
        try {
            $this->logDebug("Fetching usage statistics for: {$username}");
            
            $result = $this->executeWhmApi('accountsummary', ['user' => $username]);
            
            if (isset($result['acct'][0])) {
                $account = $result['acct'][0];
                return [
                    'success' => true,
                    'disk_used' => $account['diskused'] ?? 0,
                    'disk_limit' => $account['disklimit'] ?? 0,
                    'bandwidth_used' => $account['totalbytes'] ?? 0,
                    'bandwidth_limit' => $account['limit'] ?? 0
                ];
            }
            
            throw new Exception("Account not found");
            
        } catch (Exception $e) {
            $this->logError("Usage fetch failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Suspend WordPress site via WP Toolkit
     * 
     * @param string $domain Domain name
     * @return array Suspension result
     */
    public function suspendWordPressSite($domain)
    {
        try {
            $this->logDebug("Suspending WordPress site: {$domain}");
            
            $result = $this->executeWpToolkitApi('suspend', ['domain' => $domain]);
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'WordPress site suspended successfully'];
            } else {
                throw new Exception($result['message'] ?? 'WordPress suspension failed');
            }
            
        } catch (Exception $e) {
            $this->logError("WordPress site suspension failed for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Unsuspend WordPress site via WP Toolkit
     * 
     * @param string $domain Domain name
     * @return array Unsuspension result
     */
    public function unsuspendWordPressSite($domain)
    {
        try {
            $this->logDebug("Unsuspending WordPress site: {$domain}");
            
            $result = $this->executeWpToolkitApi('unsuspend', ['domain' => $domain]);
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'WordPress site unsuspended successfully'];
            } else {
                throw new Exception($result['message'] ?? 'WordPress unsuspension failed');
            }
            
        } catch (Exception $e) {
            $this->logError("WordPress site unsuspension failed for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create WordPress backup via WP Toolkit
     * 
     * @param string $domain Domain name
     * @return array Backup result
     */
    public function createWordPressBackup($domain)
    {
        try {
            $this->logDebug("Creating WordPress backup for: {$domain}");
            
            $backupName = 'wp_backup_' . date('Y-m-d_H-i-s') . '.tar.gz';
            
            $result = $this->executeWpToolkitApi('create_backup', [
                'domain' => $domain,
                'backup_name' => $backupName
            ]);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'backup_name' => $result['backup_name'] ?? $backupName,
                    'backup_size' => $result['backup_size'] ?? 'Unknown',
                    'created_at' => $result['created_at'] ?? date('Y-m-d H:i:s')
                ];
            } else {
                throw new Exception($result['message'] ?? 'WordPress backup creation failed');
            }
            
        } catch (Exception $e) {
            $this->logError("WordPress backup failed for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Reset WordPress admin password via WP Toolkit
     * 
     * @param string $domain Domain name
     * @param string $newPassword New password
     * @return array Password reset result
     */
    public function resetWordPressPassword($domain, $newPassword)
    {
        try {
            $this->logDebug("Resetting WordPress password for: {$domain}");
            
            $result = $this->executeWpToolkitApi('reset_password', [
                'domain' => $domain,
                'admin_password' => $newPassword
            ]);
            
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'WordPress password reset successfully',
                    'new_password' => $newPassword
                ];
            } else {
                throw new Exception($result['message'] ?? 'WordPress password reset failed');
            }
            
        } catch (Exception $e) {
            $this->logError("WordPress password reset failed for {$domain}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create final backup before account termination
     * 
     * @param string $username Account username
     * @return array Backup result
     */
    public function createFinalBackup($username)
    {
        try {
            $this->logDebug("Creating final backup for account: {$username}");
            
            $backupName = 'final_backup_' . $username . '_' . date('Y-m-d_H-i-s') . '.tar.gz';
            
            $result = $this->executeWhmApi('fullbackup', [
                'user' => $username,
                'dest' => '/backup/' . $backupName
            ]);
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                return [
                    'success' => true,
                    'backup_name' => $backupName,
                    'backup_path' => '/backup/' . $backupName
                ];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'Unknown error occurred';
                throw new Exception("Final backup failed: " . $errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("Final backup failed for {$username}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Enable SSL for WordPress site
     * 
     * @param string $domain Domain name
     * @return array SSL enablement result
     */
    private function enableWordPressSSL($domain)
    {
        try {
            $this->logDebug("Enabling SSL for WordPress site: {$domain}");
            
            // Call SSL API (Let's Encrypt or other SSL provider)
            $result = $this->executeWhmApi('start_autossl_check', ['domain' => $domain]);
            
            if (isset($result['result'][0]['status']) && $result['result'][0]['status'] == 1) {
                return ['success' => true, 'message' => 'SSL enabled successfully'];
            } else {
                $errorMsg = $result['result'][0]['statusmsg'] ?? 'SSL enablement failed';
                throw new Exception($errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logError("SSL enablement failed for {$domain}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Setup WordPress backups via WP Toolkit
     * 
     * @param string $domain Domain name
     * @param string $frequency Backup frequency
     * @return array Backup setup result
     */
    private function setupWordPressBackups($domain, $frequency)
    {
        try {
            $this->logDebug("Setting up WordPress backups for {$domain} - Frequency: {$frequency}");
            
            $result = $this->executeWpToolkitApi('setup_backups', [
                'domain' => $domain,
                'frequency' => $frequency,
                'retention' => 30 // days
            ]);
            
            if ($result['success']) {
                return ['success' => true, 'message' => 'Backups configured successfully'];
            } else {
                throw new Exception($result['message'] ?? 'Backup setup failed');
            }
            
        } catch (Exception $e) {
            $this->logError("Backup setup failed for {$domain}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Execute WHM API call
     * 
     * @param string $function API function name
     * @param array $params API parameters
     * @return array API response
     */
    private function executeWhmApi($function, $params = [])
    {
        try {
            // Validate function name to prevent injection
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $function)) {
                throw new Exception("Invalid API function name");
            }
            
            $url = "https://{$this->host}:{$this->port}/json-api/{$function}";
            $this->logDebug("WHM API Request: {$function} to {$url}");
            
            // Sanitize parameters
            $sanitizedParams = [];
            foreach ($params as $key => $value) {
                $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
                if ($key) {
                    $sanitizedParams[$key] = is_string($value) ? trim($value) : $value;
                }
            }
            
            // Log parameters (without sensitive data)
            $logParams = $sanitizedParams;
            if (isset($logParams['password'])) {
                $logParams['password'] = '[REDACTED]';
            }
            if (isset($logParams['pass'])) {
                $logParams['pass'] = '[REDACTED]';
            }
            $this->logDebug("WHM API Parameters: " . json_encode($logParams));
            
            $postData = http_build_query($sanitizedParams);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SpeedWP-WHMCS-Module/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            
            if (!empty($sanitizedParams)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $this->logDebug("WHM API Response: HTTP {$httpCode}");
            
            if ($error) {
                throw new Exception("cURL error: " . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception("HTTP error: " . $httpCode);
            }
            
            if (empty($response)) {
                throw new Exception("Empty response from server");
            }
            
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }
            
            // Log response status (without sensitive data)
            if (isset($result['result'][0]['status'])) {
                $this->logDebug("WHM API Result Status: " . $result['result'][0]['status']);
                if (isset($result['result'][0]['statusmsg'])) {
                    $this->logDebug("WHM API Status Message: " . $result['result'][0]['statusmsg']);
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("WHM API call failed ({$function}): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Execute WP Toolkit API call
     * 
     * @param string $action API action
     * @param array $params API parameters
     * @return array API response
     */
    private function executeWpToolkitApi($action, $params = [])
    {
        try {
            // Validate action name to prevent injection
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $action)) {
                throw new Exception("Invalid WP Toolkit API action name");
            }
            
            $this->logDebug("Executing WP Toolkit API call: {$action}");
            
            // WP Toolkit API endpoint
            $url = "https://{$this->host}:{$this->port}/wp-toolkit/api/{$action}";
            $this->logDebug("WP Toolkit API Request: {$action} to {$url}");
            
            // Sanitize parameters
            $sanitizedParams = [];
            foreach ($params as $key => $value) {
                $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
                if ($key) {
                    $sanitizedParams[$key] = is_string($value) ? trim($value) : $value;
                }
            }
            
            // Log parameters (without sensitive data)
            $logParams = $sanitizedParams;
            if (isset($logParams['admin_password'])) {
                $logParams['admin_password'] = '[REDACTED]';
            }
            $this->logDebug("WP Toolkit API Parameters: " . json_encode($logParams));
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SpeedWP-WHMCS-Module/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            
            if (!empty($sanitizedParams)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sanitizedParams));
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $this->logDebug("WP Toolkit API Response: HTTP {$httpCode}");
            
            if ($error) {
                throw new Exception("WP Toolkit API cURL error: " . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception("WP Toolkit API HTTP error: " . $httpCode);
            }
            
            if (empty($response)) {
                throw new Exception("Empty response from WP Toolkit API");
            }
            
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response from WP Toolkit API: " . json_last_error_msg());
            }
            
            // Log response status (without sensitive data)
            if (isset($result['success'])) {
                $this->logDebug("WP Toolkit API Result Success: " . ($result['success'] ? 'true' : 'false'));
                if (isset($result['message'])) {
                    $this->logDebug("WP Toolkit API Message: " . $result['message']);
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            $this->logError("WP Toolkit API call failed ({$action}): " . $e->getMessage());
            throw $e;
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
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
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
}
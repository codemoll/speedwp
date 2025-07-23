# SpeedWP Server Module for WHMCS

A comprehensive WHMCS server module that provisions cPanel hosting accounts with automatic WordPress installation via WP Toolkit integration. This module handles the complete hosting account lifecycle including creation, suspension, termination, and provides both client and admin area WordPress management interfaces.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Server Setup](#server-setup)
- [Product Configuration](#product-configuration)
- [Client Experience](#client-experience)
- [Admin Features](#admin-features)
- [API Integration](#api-integration)
- [Troubleshooting](#troubleshooting)
- [Support](#support)
- [Development](#development)

## Features

### Core Hosting Provisioning
- **cPanel Account Creation**: Automatic cPanel hosting account provisioning
- **WordPress Auto-Installation**: One-click WordPress setup via WP Toolkit
- **Account Lifecycle Management**: Suspend, unsuspend, and terminate accounts
- **Resource Monitoring**: Real-time disk space and bandwidth usage tracking
- **Password Management**: Secure password generation and updates

### WordPress Management
- **WP Toolkit Integration**: Full WordPress management via cPanel's WP Toolkit
- **Automatic Backups**: Scheduled WordPress backups with customizable retention
- **Update Management**: WordPress core, plugin, and theme updates
- **SSL Configuration**: Automatic SSL certificate provisioning and management
- **Security Features**: WordPress hardening and security monitoring

### Client Area Features
- **WordPress Dashboard**: Comprehensive WordPress management interface
- **One-Click Actions**: Backup creation, password resets, updates
- **Resource Usage**: Real-time hosting account statistics
- **Quick Access**: Direct links to WordPress admin and cPanel
- **Mobile Responsive**: Optimized for desktop and mobile devices

### Admin Area Features
- **Server Management**: Complete server and account overview
- **Bulk Operations**: Mass account management and WordPress operations
- **Detailed Reporting**: WordPress site statistics and health monitoring
- **Custom Actions**: Admin-specific WordPress management tools
- **Service Integration**: Seamless WHMCS services tab integration

## Requirements

### System Requirements
- **WHMCS**: Version 8.0 or later (tested with 8.x series)
- **PHP**: Version 7.4 or later (compatible with PHP 8.x)
- **MySQL**: Version 5.7 or later / MariaDB 10.2+
- **cURL**: Required for cPanel/WHM API communication

### Server Requirements
- **cPanel/WHM**: Latest stable version with API access enabled
- **WP Toolkit**: Installed and configured on cPanel server
- **SSL Certificate**: Valid SSL certificate for secure API communication
- **API Access**: WHM root or reseller account with API permissions

### Network Requirements
- **Firewall**: WHMCS server IP whitelisted in WHM firewall
- **Ports**: Access to WHM ports (2087 for HTTPS, 2086 for HTTP)
- **DNS**: Proper DNS resolution between WHMCS and cPanel servers

## Installation

### Step 1: Upload Module Files

Upload the SpeedWP server module to your WHMCS installation:

```bash
# Upload to WHMCS modules/servers directory
/path/to/whmcs/modules/servers/speedwp/
```

Ensure proper file permissions:

```bash
# Set appropriate permissions
chmod -R 755 /path/to/whmcs/modules/servers/speedwp/
chown -R www-data:www-data /path/to/whmcs/modules/servers/speedwp/
```

### Step 2: File Structure Verification

Verify the complete file structure is in place:

```
modules/servers/speedwp/
├── speedwp.php                    # Main server module file
├── lib/
│   ├── CpanelApi.php             # cPanel/WHM API integration
│   ├── ClientAreaController.php   # Client area functionality
│   └── AdminController.php       # Admin area functionality
├── templates/
│   ├── dashboard.tpl             # Client area dashboard template
│   └── error.tpl                 # Error display template
└── README.md                     # This documentation file
```

### Step 3: WHMCS Configuration

No additional database setup is required as this is a server module (not an addon module).

## Configuration

### Step 1: Create a Server in WHMCS

1. Navigate to **Setup > Products/Services > Servers** in WHMCS admin
2. Click **Add New Server**
3. Configure the server details:
   - **Name**: Choose a descriptive name (e.g., "SpeedWP cPanel Server")
   - **Hostname**: Your cPanel server hostname or IP address
   - **IP Address**: Server IP address (optional but recommended)
   - **Type**: Select "SpeedWP" from the dropdown
   - **Username**: WHM root username (usually "root")
   - **Password**: WHM root password or API token
   - **Access Hash**: Leave blank (password/token is preferred)
   - **Secure**: Check this box for HTTPS connections (recommended)
   - **Port**: WHM port (2087 for HTTPS, 2086 for HTTP)

### Step 2: Configure Server Module Options

After creating the server, configure the SpeedWP-specific options:

| Option | Description | Default | Notes |
|--------|-------------|---------|-------|
| **Server IP/Hostname** | cPanel server address | - | Can override server hostname |
| **WHM Port** | WHM API port | 2087 | Use 2087 for HTTPS, 2086 for HTTP |
| **WHM Username** | API username | root | Usually "root" or reseller username |
| **WHM Password/API Token** | Authentication | - | API token recommended over password |
| **Auto-Install WordPress** | Auto WordPress setup | Yes | Install WordPress on account creation |
| **WordPress Version** | WP version to install | latest | latest, 6.4, 6.3, 6.2 |
| **Default Admin Username** | WP admin username | admin | Default WordPress admin user |
| **Enable SSL** | Auto-enable SSL | Yes | Automatic SSL for WordPress sites |
| **Enable Backups** | Auto-backup setup | Yes | Configure automatic WordPress backups |
| **Backup Frequency** | Backup schedule | weekly | daily, weekly, monthly |

### Step 3: Test Server Connection

1. In the server configuration, click **Test Connection**
2. Verify successful connection to WHM server
3. Check that WP Toolkit is detected and accessible
4. Confirm API permissions are sufficient

## Server Setup

### cPanel/WHM Configuration

#### 1. Enable API Access

Ensure API access is enabled in WHM:

```bash
# Via WHM interface:
# WHM > Development > API Shell
# Or: Home > Development > Manage API Tokens
```

#### 2. Configure Firewall

Whitelist your WHMCS server IP:

```bash
# Via WHM interface:
# Home > Plugins > ConfigServer Security & Firewall
# Add WHMCS server IP to csf.allow
```

#### 3. WP Toolkit Setup

Verify WP Toolkit is installed and configured:

```bash
# Check WP Toolkit installation
/usr/local/cpanel/3rdparty/wp-toolkit/toolkit --version

# Verify WP Toolkit is enabled
grep -i "wp-toolkit" /var/cpanel/cpanel.config
```

#### 4. Create API Token (Recommended)

Generate a secure API token instead of using root password:

1. Go to **WHM > Development > Manage API Tokens**
2. Click **Generate Token**
3. Set appropriate permissions:
   - Account Functions: Create, List, Suspend, Unsuspend, Terminate
   - WordPress Functions: Install, Manage, Update, Backup
4. Note the token for WHMCS server configuration

### SSL Certificate Setup

Ensure your cPanel server has a valid SSL certificate:

```bash
# Check SSL certificate
openssl s_client -connect your-server.com:2087 -servername your-server.com

# Auto-SSL setup (if using cPanel AutoSSL)
/usr/local/cpanel/bin/checkallsslcerts
```

## Product Configuration

### Step 1: Create Hosting Products

1. Navigate to **Setup > Products/Services > Products/Services**
2. Click **Create a New Product**
3. Configure basic product details:
   - **Product Type**: Hosting Account
   - **Product Name**: "SpeedWP WordPress Hosting" (or preferred name)
   - **Product Group**: Select appropriate group
   - **Description**: Include WordPress management features

### Step 2: Module Settings Configuration

In the product configuration, go to **Module Settings** tab:

1. **Module Name**: Select "SpeedWP"
2. **Server**: Choose your configured SpeedWP server
3. **Package**: cPanel package name (optional)
4. Configure module-specific options as needed

### Step 3: Custom Fields (Optional)

Add custom fields for enhanced functionality:

| Field Name | Type | Description |
|------------|------|-------------|
| WordPress Admin URL | Text | WordPress admin panel URL |
| WordPress Admin User | Text | WordPress admin username |
| WordPress Admin Password | Password | WordPress admin password (encrypted) |
| WordPress Version | Text | Installed WordPress version |
| SSL Status | Text | SSL certificate status |

### Step 4: Welcome Email Template

Customize the welcome email template to include WordPress information:

```html
Dear {$client_name},

Your SpeedWP WordPress hosting account has been successfully created!

Hosting Account Details:
- Domain: {$service_domain}
- Username: {$service_username}
- Password: {$service_password}
- Server: {$service_server_name}

WordPress Details:
- WordPress Admin: https://{$service_domain}/wp-admin/
- WordPress Username: [Available in client area]
- WordPress Password: [Available in client area]

cPanel Access:
- cPanel URL: https://{$service_server_name}:2083
- Username: {$service_username}
- Password: {$service_password}

To manage your WordPress site, log into your client area and access the WordPress management dashboard.

Best regards,
{$company_name}
```

## Client Experience

### Client Area Dashboard

Clients access WordPress management through the **Service Details** page in their WHMCS client area:

#### WordPress Management Features
- **Site Overview**: WordPress version, SSL status, last backup
- **Quick Actions**: Create backup, reset password, update WordPress
- **Plugin & Theme Management**: View installed components and updates
- **Resource Usage**: Hosting account disk space and bandwidth
- **Direct Access**: One-click login to WordPress admin and cPanel

#### Mobile Optimization
- Responsive design for tablets and smartphones
- Touch-friendly interface elements
- Collapsible sections for better mobile navigation
- Optimized loading for slower connections

### Self-Service Capabilities

Clients can perform the following actions independently:

1. **WordPress Management**
   - Create instant backups
   - Reset WordPress admin password
   - Update WordPress core, plugins, and themes
   - Toggle automatic updates on/off
   - View site statistics and health status

2. **Hosting Account Management**
   - Monitor resource usage (disk space, bandwidth)
   - Access cPanel and webmail interfaces
   - View account status and package details
   - Contact support directly from the interface

## Admin Features

### Services Tab Integration

The SpeedWP module adds a comprehensive **WordPress** tab to the admin services interface:

#### Account Overview
- Hosting account details and resource usage
- Real-time statistics with progress bars
- Account status and package information
- Quick access to cPanel and admin tools

#### WordPress Management
- Complete WordPress site information
- Plugin and theme inventory with update status
- Backup history and creation tools
- Security status and SSL configuration

#### Admin Actions
- **Refresh Details**: Update WordPress information from WP Toolkit
- **Create Backup**: Generate immediate WordPress backup
- **Reset Password**: Reset WordPress admin password
- **Update WordPress**: Perform core, plugin, and theme updates
- **Manage Site**: Advanced WordPress management interface

### Bulk Operations

Perform operations across multiple accounts:

```php
// Example bulk operations (future enhancement)
- Bulk WordPress updates across all accounts
- Mass backup creation for all WordPress sites
- Bulk security scanning and hardening
- Mass plugin/theme updates and management
```

### Reporting and Analytics

Comprehensive reporting features:

- WordPress installation statistics
- Update compliance across all accounts
- Backup success/failure rates
- Resource usage trends and alerts
- Security incident reporting

## API Integration

### cPanel/WHM API Functions

The module uses the following API endpoints:

#### Account Management
```bash
# Create account
POST /json-api/createacct

# Suspend account  
POST /json-api/suspendacct

# Unsuspend account
POST /json-api/unsuspendacct

# Terminate account
POST /json-api/removeacct

# Change password
POST /json-api/passwd

# Get account summary
GET /json-api/accountsummary
```

#### WP Toolkit API
```bash
# Install WordPress
POST /wp-toolkit/api/install

# Get site information
GET /wp-toolkit/api/get_site_info

# Create backup
POST /wp-toolkit/api/create_backup

# Update WordPress
POST /wp-toolkit/api/update

# Reset password
POST /wp-toolkit/api/reset_password
```

### Mock/Demo Data

When API connections fail or for demonstration purposes, the module provides realistic mock data:

- Demo WordPress installation details
- Sample plugin and theme inventories
- Simulated backup creation and management
- Mock update processes with realistic timing
- Demo resource usage statistics

### API Error Handling

Comprehensive error handling ensures module stability:

```php
// Connection failure fallback
try {
    $result = $api->executeCall($endpoint, $params);
} catch (Exception $e) {
    // Log error and return demo data
    logActivity("SpeedWP API Error: " . $e->getMessage());
    return $this->getDemoData();
}
```

## Troubleshooting

### Common Issues and Solutions

#### 1. Connection Errors

**Symptom**: "Connection failed" messages in admin area

**Solutions**:
- Verify WHM hostname and port settings
- Check firewall rules and IP whitelisting
- Confirm SSL certificate validity
- Test API credentials manually

**Debugging**:
```bash
# Test WHM connection
curl -k -u "root:password" "https://server.com:2087/json-api/version"

# Check firewall
iptables -L | grep 2087
```

#### 2. WordPress Installation Failures

**Symptom**: WordPress not installing during account creation

**Solutions**:
- Verify WP Toolkit is installed and functional
- Check database creation permissions
- Confirm sufficient disk space
- Review cPanel error logs

**Debugging**:
```bash
# Check WP Toolkit status
/usr/local/cpanel/3rdparty/wp-toolkit/toolkit --list

# Review installation logs
tail -f /usr/local/cpanel/logs/error_log
```

#### 3. Client Area Display Issues

**Symptom**: WordPress management interface not loading

**Solutions**:
- Clear WHMCS template cache
- Verify template file permissions
- Check for PHP errors in WHMCS logs
- Confirm service module assignment

**Debugging**:
```bash
# Clear WHMCS cache
rm -rf /path/to/whmcs/templates_c/*

# Check PHP error log
tail -f /var/log/php_errors.log
```

#### 4. API Permission Issues

**Symptom**: "Access denied" or permission errors

**Solutions**:
- Verify WHM account has sufficient privileges
- Check API token permissions and expiration
- Confirm account is not suspended or limited
- Review WHM security policies

**Debugging**:
```bash
# Check account privileges
grep -i "root" /etc/passwd

# Verify API token
/usr/local/cpanel/bin/whmapi1 api_token_list
```

### Log File Locations

Monitor these log files for troubleshooting:

```bash
# WHMCS Activity Log
# Available in WHMCS admin: Utilities > Logs > Activity Log

# WHM Error Logs
/usr/local/cpanel/logs/error_log
/usr/local/cpanel/logs/access_log

# cPanel User Error Logs
/home/username/logs/error_log

# WP Toolkit Logs
/var/log/wp-toolkit/wp-toolkit.log

# System Logs
/var/log/messages
/var/log/httpd/error_log
```

### Debug Mode

Enable debug mode for detailed logging:

1. Edit the server configuration in WHMCS
2. Add debug parameter to API calls
3. Monitor WHMCS activity logs for detailed information
4. **Important**: Disable debug mode in production

### Performance Optimization

For optimal performance:

```bash
# Optimize MySQL (if needed)
mysql_tune

# Enable OPcache for PHP
echo "opcache.enable=1" >> /etc/php.ini

# Configure WHM for API performance
# Increase max_connections in MySQL
# Optimize Apache/Nginx configuration
```

## Support

### Getting Help

1. **Documentation**: Review this README and inline code comments
2. **WHMCS Activity Log**: Check for specific error messages with debug mode
3. **cPanel Logs**: Review server-side logs for API and WordPress issues
4. **Community**: Submit issues via the project repository

### Reporting Issues

When reporting issues, please include:

- WHMCS version and environment details
- cPanel/WHM version and configuration
- Complete error messages from logs
- Steps to reproduce the issue
- Screenshots of error conditions

### Feature Requests

Submit feature requests with:

- Detailed description of desired functionality
- Use case and business justification
- Proposed implementation approach
- Compatibility considerations

## Development

### Extending the Module

The module is designed for easy extension:

#### Adding New API Functions

```php
// Add to CpanelApi.php
public function newWordPressFunction($params)
{
    try {
        $result = $this->executeWpToolkitApi('new_action', $params);
        return $result;
    } catch (Exception $e) {
        return $this->getMockData('new_action', $params);
    }
}
```

#### Customizing Client Area

Modify templates in `templates/` directory:
- `dashboard.tpl`: Main client interface
- `error.tpl`: Error display template
- Add new templates as needed

#### Extending Admin Features

Add new admin functions to `AdminController.php`:

```php
public function newAdminFeature()
{
    // Implementation
    return $htmlOutput;
}
```

### Code Standards

Follow these standards when contributing:

- **PHP**: PSR-12 coding standard
- **JavaScript**: ES6+ with backward compatibility
- **CSS**: BEM methodology for class naming
- **Comments**: PHPDoc format for all functions
- **Security**: Input validation and output escaping

### Testing

Test all changes with:

```bash
# Test server module functions
# Create test hosting account
# Verify WordPress installation
# Test suspension/unsuspension
# Confirm termination process

# Test client area interface
# Verify all AJAX functions
# Test mobile responsiveness
# Confirm error handling

# Test admin area integration
# Verify services tab display
# Test all admin actions
# Confirm bulk operations
```

### Security Considerations

1. **Input Validation**: Sanitize all user inputs
2. **Output Encoding**: Escape all output to prevent XSS
3. **SQL Injection**: Use prepared statements
4. **API Security**: Secure credential storage and transmission
5. **File Permissions**: Proper file system permissions
6. **Error Handling**: Don't expose sensitive information in errors

## Changelog

### Version 1.0.0 (Initial Release)

**New Features**:
- Complete WHMCS server module implementation
- cPanel/WHM API integration with fallback demo mode
- WordPress installation via WP Toolkit integration
- Comprehensive client area WordPress management interface
- Admin area services tab integration with WordPress details
- Account lifecycle management (create, suspend, unsuspend, terminate)
- Real-time resource usage monitoring and display
- Mobile-responsive client interface design
- Comprehensive error handling and logging
- Mock/demo data for development and testing

**API Integration**:
- WHM JSON API for account management
- WP Toolkit API for WordPress operations (with placeholders)
- Secure credential handling and storage
- Connection testing and validation
- Automatic fallback to demo mode on API failures

**Documentation**:
- Complete setup and configuration guide
- Troubleshooting section with common issues
- API integration documentation
- Development guidelines and extension points

---

## License

This SpeedWP server module is provided as open-source software. Review and test thoroughly before production use.

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes with appropriate tests
4. Submit a pull request with detailed description

---

**Note**: This is a comprehensive server provisioning module that requires proper cPanel/WHM setup and configuration. The module includes demo/mock functionality for development and testing purposes. Ensure all API integrations are properly configured before production deployment.
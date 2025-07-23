# SpeedWP - WHMCS WordPress Manager

A comprehensive WordPress management addon for WHMCS that enables hosting clients to manage their WordPress installations directly from the client area using cPanel integration.

## Features

- **cPanel Account Management**: Create, suspend, unsuspend, and terminate hosting accounts
- **WordPress Installation**: Automatic WordPress installation via WP Toolkit integration
- **WordPress Management**: Complete WordPress lifecycle management
- **Backup & Restore**: Automated backup creation and restoration
- **SSL Integration**: Automatic SSL certificate provisioning
- **Usage Monitoring**: Real-time disk space and bandwidth usage tracking
- **Error Handling**: Comprehensive error logging and reporting
- **Production Ready**: Full API error handling without fallbacks

## Installation

### Requirements

- **WHMCS**: Version 8.0 or later (compatible with 8.x+)
- **PHP**: Version 7.4 or later (tested with PHP 7.4+ and 8.x)
- **cPanel/WHM**: Server with valid API access and WP Toolkit installed
- **MySQL**: Version 5.7 or later
- **SSL**: Valid SSL certificate for secure API communication
- **API Access**: WHM root or reseller account with API token access

### Critical Production Setup Requirements

⚠️ **Important**: This module requires proper cPanel/WHM API connectivity for production use.

#### 1. cPanel/WHM Server Requirements
- **WHM Access**: Root or reseller account with full API access
- **WP Toolkit**: Must be installed and functional on the cPanel server
- **API Authentication**: API tokens are strongly recommended over passwords
- **Network Access**: WHMCS server must be able to reach cPanel server on HTTPS ports
- **SSL/TLS**: Valid SSL certificates on both WHMCS and cPanel servers

#### 2. Required cPanel Features
- **Account Management**: createacct, suspendacct, unsuspendacct, removeacct, passwd
- **Usage Statistics**: accountsummary API function access
- **WP Toolkit Integration**: Full WP Toolkit API access for WordPress management
- **SSL Management**: AutoSSL or Let's Encrypt integration
- **Backup System**: Full backup API access (fullbackup function)

#### 3. Network and Security
- **Firewall Rules**: Ensure WHMCS server IP is whitelisted in cPanel firewall
- **API Rate Limits**: Configure appropriate rate limiting to prevent API abuse
- **SSL Verification**: Enable SSL verification for all API communications
- **Credential Security**: Use API tokens instead of passwords when possible

### Step-by-Step Setup Instructions

#### 1. Upload Module Files
```bash
# Upload the speedwp folder to your WHMCS modules/servers directory
/path/to/whmcs/modules/servers/speedwp/
```

Ensure all files have proper permissions:
```bash
chmod -R 755 /path/to/whmcs/modules/servers/speedwp/
```

#### 2. Create Server in WHMCS
1. Navigate to **Setup > Products/Services > Servers** in WHMCS admin area
2. Click **Add New Server**
3. Configure server details:
   - **Name**: Your cPanel server name
   - **Hostname**: cPanel server hostname or IP
   - **Module**: speedwp
   - **Server Details**: Root username and password/API token

#### 3. Configure Module Settings
Configure the following settings when adding/editing the server:
- **Server IP/Hostname**: Your cPanel server hostname or IP address
- **WHM Port**: Usually 2087 for HTTPS
- **WHM Username**: Root or reseller username  
- **WHM Password/API Token**: Valid credentials with full API access
- **Package Name**: cPanel package/plan name (must exist on server)
- **Auto-Install WordPress**: Enable for automatic WordPress installation
- **WordPress Version**: WordPress version to install (latest recommended)
- **Default Admin Username**: Default WordPress admin username
- **Enable SSL**: Automatically enable SSL for new WordPress sites
- **Enable Backups**: Enable automatic backups via WP Toolkit
- **Backup Frequency**: How often to create backups
- **Debug Mode**: Enable for troubleshooting (disable in production)

## Product Setup and Client Assignment Guide

### Creating WordPress Hosting Products

To enable SpeedWP functionality for your hosting clients, you need to create and configure hosting products properly:

#### 1. Create Hosting Products in WHMCS
1. Navigate to **Setup > Products/Services > Products/Services**
2. Click **Create New Group** (if needed) or use existing group
3. Click **Create a New Product**
4. Configure the product:
   - **Product Type**: Hosting Account
   - **Product Name**: "WordPress Hosting" (or your preferred name)
   - **Description**: Include mention of WordPress management features

#### 2. Configure Product Module Settings
1. In the product configuration, go to the **Module Settings** tab
2. Select your hosting module (e.g., cPanel, Plesk, etc.)
3. Configure server assignments and package details
4. **Important**: Ensure the server has cPanel API access enabled

#### 3. Enable WordPress Features in Product Description
Add to your product description:
```
✓ WordPress Management via Client Area
✓ One-Click WordPress Installation
✓ Automatic WordPress Updates
✓ Built-in Backup & Restore
✓ Plugin & Theme Management
✓ WordPress Security Scanning
```

### Assigning SpeedWP to Clients

#### Method 1: Automatic Assignment (Recommended)
SpeedWP automatically works with hosting accounts through hooks:
1. When a hosting account is created, SpeedWP automatically scans for WordPress
2. If auto-install is enabled, WordPress is installed automatically
3. Clients immediately have access via "WordPress Manager" in their client area

#### Method 2: Manual WordPress Site Registration
For existing clients or manual setup:
1. Go to **Clients > View/Search Clients**
2. Select the client and view their services
3. In the hosting service details, you'll see a "WordPress Sites" section
4. Click **Scan for WordPress** to detect existing installations
5. Or click **Install WordPress** to create a new installation

### Client Access

**For Clients:**
Clients access WordPress management through the service details in their client area:
1. Log into the WHMCS client area
2. Navigate to **My Products & Services**
3. Click on their hosting service
4. Access WordPress management features from the service details page

**Available Features:**
- View WordPress installation details
- Access WordPress admin panel
- View backup status and history
- Monitor resource usage
- Basic WordPress management tools

### Hosting Package Configuration

Configure your WHMCS hosting products to use the SpeedWP server module:

**Hosting Package Setup:**
- **Basic Hosting**: cPanel account + optional WordPress installation
- **WordPress Hosting**: cPanel account + automatic WordPress installation + SSL
- **Premium Hosting**: All features + automatic backups + enhanced support

**Server Assignment:**
1. Create hosting products in WHMCS
2. Assign products to servers using the SpeedWP module
3. Configure package settings and WordPress options
4. Set pricing and billing cycles

#### 5. Troubleshooting Client Assignment

**Common Issues and Solutions:**

1. **Client can't see WordPress Manager menu**
   - Verify client has active hosting account
   - Check that hosting account uses compatible hosting module
   - Ensure SpeedWP addon is activated

2. **WordPress sites not detected**
   - Run manual scan from admin area
   - Check cPanel API connectivity
   - Verify file permissions on hosting account

3. **WordPress management features not working**
   - Verify cPanel host configuration in addon settings
   - Check WHMCS activity logs for error messages
   - Enable debug mode to diagnose issues

### cPanel API Setup

The addon requires cPanel API access to manage WordPress installations. Ensure your cPanel server has:

1. **API Access Enabled**
   - WHMCS server IP whitelisted in cPanel
   - API tokens or WHM access configured

2. **Required Permissions**
   - File Manager access
   - Database management
   - Subdomain/addon domain management
   - Backup creation/restoration

## File Structure

```
modules/servers/speedwp/
├── speedwp.php               # Main server module file
├── lib/
│   ├── CpanelApi.php        # cPanel API integration
│   ├── AdminController.php  # Admin area functionality
│   └── ClientAreaController.php # Client area functionality
├── templates/
│   └── (template files)     # Client area templates
└── README.md                # Module documentation
```

## Usage

### Admin Area

Administrators can manage hosting accounts and WordPress installations through:
1. **Setup > Products/Services > Servers**: Configure SpeedWP servers
2. **Clients > View/Search Clients**: Manage individual client services  
3. **Service Details**: Access WordPress management tools for each service

### Client Area

Clients can access WordPress management through their service details:
1. **My Products & Services**: View active hosting services
2. **Service Details**: Access WordPress admin panel and basic management tools
3. **Account Information**: View usage statistics and service status

## Database Tables

The addon creates the following database table:

### `mod_speedwp_sites`
Stores WordPress installation information:
- `id`: Unique site identifier
- `client_id`: WHMCS client ID
- `domain`: Site domain
- `cpanel_user`: cPanel username
- `wp_path`: WordPress installation path
- `wp_version`: WordPress version
- `status`: Site status (active/inactive/suspended)
- `created_at`: Installation date
- `updated_at`: Last modification date

## Development

### Extending the Module

The addon is designed for extensibility:

1. **Adding New Features**
   - Extend controllers for new functionality
   - Add language strings to `lang/english.php`
   - Create new templates as needed

2. **cPanel API Extensions**
   - Add new methods to `lib/cpanelApi.php`
   - Follow existing error handling patterns
   - Implement proper logging

3. **Hook Integration**
   - Add new hooks to `hooks.php`
   - Follow WHMCS hook naming conventions
   - Handle errors gracefully

### TODO List

#### Phase 1: Core Functionality
- [ ] Complete cPanel API integration
- [ ] Implement WordPress installation workflow
- [ ] Add WordPress update mechanisms
- [ ] Create backup/restore functionality
- [ ] Implement error handling and logging

#### Phase 2: Enhanced Features
- [ ] Plugin management interface
- [ ] Theme management capabilities
- [ ] WordPress security scanning
- [ ] Performance optimization tools
- [ ] Staging environment support

#### Phase 3: Advanced Features
- [ ] Multi-site (WordPress Network) support
- [ ] SSL certificate integration
- [ ] CDN management
- [ ] Database optimization tools
- [ ] Automated maintenance schedules

#### Phase 4: Integration & Analytics
- [ ] Third-party plugin integrations
- [ ] Usage analytics and reporting
- [ ] White-label customization options
- [ ] API for external integrations

## Configuration Options

### Server Module Settings

| Setting | Description | Default |
|---------|-------------|---------|
| Server IP/Hostname | cPanel server hostname or IP | - |
| WHM Port | WHM API port | 2087 |
| WHM Username | Root or reseller username | root |
| WHM Password/API Token | API credentials | - |
| Package Name | cPanel hosting package | default |
| Auto-Install WordPress | Auto-install WordPress | Yes |
| WordPress Version | WordPress version to install | latest |
| Default Admin Username | Default WP admin username | admin |
| Enable SSL | Auto-enable SSL | Yes |
| Enable Backups | Auto-enable backups | Yes |
| Backup Frequency | Backup schedule | weekly |
| Debug Mode | Enable debug logging | No |

## Security Considerations

1. **API Credentials**
   - Store cPanel credentials securely
   - Use API tokens instead of passwords when possible
   - Implement credential rotation policies

2. **File Permissions**
   - Ensure proper file permissions on WordPress installations
   - Validate file paths to prevent directory traversal
   - Sanitize all user inputs

3. **Database Security**
   - Use prepared statements for all database queries
   - Validate and sanitize data before storage
   - Implement proper access controls

## Production Troubleshooting

### Common Production Issues

#### 1. Account Creation Failures
**Symptoms**: Accounts fail to create, no demo mode fallbacks

**Debug Steps**:
1. Enable "Debug Mode" in server configuration
2. Check WHMCS Activity Log for detailed API errors
3. Verify cPanel server connectivity: `telnet your-cpanel-server.com 2087`
4. Test WHM API access manually
5. Confirm package name exists on cPanel server
6. Check disk space and account limits on cPanel server

**Required WHMCS Configuration**:
- Server IP/Hostname: Must be reachable from WHMCS
- WHM Port: Usually 2087 (HTTPS) or 2086 (HTTP - not recommended) 
- WHM Username: Valid root or reseller username
- WHM Password/API Token: Valid credentials with full API access
- Package Name: Must exist as a hosting package on the cPanel server

#### 2. WordPress Installation Failures
**Symptoms**: cPanel account created but WordPress installation fails

**Debug Steps**:
1. Verify WP Toolkit is installed and functional on cPanel server
2. Check WP Toolkit API endpoint accessibility
3. Ensure target domain resolves to cPanel server
4. Verify sufficient disk space for WordPress installation
5. Check database creation permissions

#### 3. Account Management Issues (Suspend/Terminate/Password Change)
**Symptoms**: Operations fail with API errors

**Debug Steps**:
1. Verify account exists on cPanel server
2. Check WHM API permissions for account management
3. Ensure account is not protected from suspension/termination
4. Verify username format matches cPanel standards

#### 4. Usage Statistics Not Updating
**Symptoms**: Disk/bandwidth usage shows as 0 or doesn't update

**Debug Steps**:
1. Verify 'accountsummary' API function is accessible
2. Check account exists and is active on cPanel server
3. Ensure account has usage data to report

### Error Logging

All production errors are logged to WHMCS Activity Log with detailed information:

- **API Request Details**: Function called, parameters sent
- **API Response Details**: HTTP status, error messages
- **Connection Issues**: Network, SSL, authentication problems
- **Data Validation**: Missing or invalid parameters

### Debug Mode

Enable debug mode for detailed troubleshooting:
1. Go to server configuration in WHMCS
2. Set "Debug Mode" to "Yes"
3. Reproduce the issue
4. Check Activity Log for detailed debug information
5. **Important**: Disable debug mode in production after troubleshooting

## Troubleshooting

### Common Issues

1. **cPanel Connection Errors**
   - Verify hostname and port settings
   - Check firewall rules and IP whitelisting
   - Test API credentials manually

2. **WordPress Detection Issues**
   - Ensure proper file permissions
   - Check for non-standard WordPress installations
   - Verify cPanel File Manager access

3. **Installation Failures**
   - Check available disk space
   - Verify database creation permissions
   - Review error logs for specific issues

### Debug Mode

Enable debug mode in module settings to:
- Log detailed API communications
- Track WordPress operations
- Identify configuration issues
- Monitor performance metrics

### Log Files

Monitor WHMCS activity logs for SpeedWP entries:
- Installation attempts
- Update operations
- Error conditions
- API communications

## Support and Contributing

### Getting Help

1. **Documentation**: Review this README and inline code comments
2. **Logs**: Check WHMCS activity logs with debug mode enabled
3. **Testing**: Use the built-in health check tools
4. **Community**: Submit issues via the project repository

### Contributing

1. **Code Style**: Follow WHMCS addon development standards
2. **Testing**: Test all changes with multiple WordPress versions
3. **Documentation**: Update README and inline comments
4. **Security**: Follow secure coding practices

### License

This addon is provided as-is for educational and development purposes. Review and test thoroughly before production use.

## Changelog

### Version 1.0.0 (Initial Release)
- Basic addon structure
- cPanel API integration skeleton
- Client and admin area interfaces
- WordPress detection framework
- Database schema and hooks
- Comprehensive documentation

---

**Note**: This is the initial foundational release. All features marked as "TODO" require implementation before production use. The current version provides the structure and framework for a complete WordPress management solution.
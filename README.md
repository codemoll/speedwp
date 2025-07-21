# SpeedWP - WHMCS WordPress Manager

A comprehensive WordPress management addon for WHMCS that enables hosting clients to manage their WordPress installations directly from the client area using cPanel integration.

## Features

- **WordPress Detection**: Automatically scan hosting accounts for existing WordPress installations
- **One-Click Installation**: Install WordPress directly from the client area
- **Update Management**: Update WordPress core, themes, and plugins
- **Backup & Restore**: Create and manage WordPress backups
- **Client Self-Service**: Allow clients to manage their own WordPress sites
- **Admin Overview**: Complete administrative interface for managing all client WordPress installations
- **cPanel Integration**: Seamless integration with cPanel for file and database operations

## Installation

### Requirements

- WHMCS 7.0 or later
- PHP 7.4 or later
- cPanel hosting environment
- MySQL 5.7 or later
- SSL certificate for secure API communication

### Setup Instructions

1. **Upload Files**
   ```bash
   # Upload the speedwp folder to your WHMCS modules/addons directory
   /path/to/whmcs/modules/addons/speedwp/
   ```

2. **Activate the Addon**
   - Navigate to **Setup > Addon Modules** in WHMCS admin area
   - Find "SpeedWP - WordPress Manager" and click **Activate**
   - Configure the addon settings:
     - **cPanel Host**: Your cPanel server hostname or IP
     - **cPanel Port**: Usually 2083 for HTTPS
     - **Debug Mode**: Enable for troubleshooting (disable in production)

3. **Configure Access Control**
   - Set administrator role permissions for the addon
   - Grant access to admin staff who should manage WordPress installations

4. **Test Configuration**
   - Visit the addon page in admin area to verify setup
   - Test cPanel connectivity with a sample hosting account
   - Scan a hosting account for existing WordPress installations

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
modules/addons/speedwp/
├── speedwp.php              # Main addon module file
├── hooks.php                # WHMCS hook registrations
├── controllers/
│   ├── ClientController.php # Client area functionality
│   └── AdminController.php  # Admin area functionality
├── lib/
│   └── cpanelApi.php       # cPanel API integration
├── templates/
│   ├── clientarea/
│   │   └── dashboard.tpl   # Client dashboard template
│   └── admin/
│       └── dashboard.tpl   # Admin dashboard template
├── lang/
│   └── english.php         # Language definitions
└── README.md               # This file
```

## Usage

### Client Area

Clients can access WordPress management through:
1. **Main Navigation**: "WordPress Manager" menu item
2. **Features**:
   - View all WordPress sites
   - Scan hosting accounts for WordPress
   - Install new WordPress sites
   - Update existing installations
   - Create and manage backups
   - Quick action buttons for common tasks

### Admin Area

Administrators can manage all WordPress installations through:
1. **Addon Modules > SpeedWP**
2. **Features**:
   - Overview dashboard with statistics
   - Manage all client WordPress sites
   - Bulk operations (scan, update, backup)
   - Client management interface
   - System tools and health checks

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

### Module Settings

| Setting | Description | Default |
|---------|-------------|---------|
| cPanel Host | Server hostname/IP | - |
| cPanel Port | API port | 2083 |
| Debug Mode | Enable debug logging | No |

### Future Settings
- Auto-scan new accounts
- Backup retention period
- Update scheduling
- Security scan frequency
- Performance monitoring

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
{*
 * SpeedWP Client Area Dashboard Template
 * 
 * WordPress management interface for clients.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 *}

{* Include CSS for better styling *}
<style>
.speedwp-dashboard {
    margin-top: 20px;
}
.speedwp-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.speedwp-site-item {
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
    background: #f9f9f9;
}
.speedwp-site-actions {
    margin-top: 10px;
}
.speedwp-site-actions .btn {
    margin-right: 5px;
    margin-bottom: 5px;
}
.speedwp-status-active { color: #5cb85c; }
.speedwp-status-suspended { color: #f0ad4e; }
.speedwp-status-inactive { color: #d9534f; }
</style>

<div class="speedwp-dashboard">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fa fa-wordpress"></i> WordPress Manager</h2>
            <p class="text-muted">Manage your WordPress installations from your hosting accounts.</p>
        </div>
    </div>

    {if $wp_sites}
        <div class="row">
            <div class="col-md-12">
                <div class="speedwp-card">
                    <h3>Your WordPress Sites</h3>
                    
                    {foreach from=$wp_sites item=site}
                        <div class="speedwp-site-item">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4>
                                        <i class="fa fa-globe"></i> 
                                        <a href="{$site.site_url}" target="_blank">{$site.domain}{$site.wp_path}</a>
                                        <small class="speedwp-status-{$site.status}">
                                            ({$site.status|ucfirst})
                                        </small>
                                        {if $site.ssl_enabled}
                                            <span class="label label-success"><i class="fa fa-lock"></i> SSL</span>
                                        {/if}
                                        {if $site.maintenance_mode}
                                            <span class="label label-warning"><i class="fa fa-wrench"></i> Maintenance</span>
                                        {/if}
                                    </h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-muted">
                                                <strong>WordPress:</strong> {$site.wp_version|default:"Unknown"}<br>
                                                <strong>Size:</strong> {$site.disk_usage_formatted}<br>
                                                <strong>Files:</strong> {$site.file_count|number_format}
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted">
                                                <strong>Plugins:</strong> {$site.plugin_count}<br>
                                                <strong>Themes:</strong> {$site.theme_count}<br>
                                                <strong>Last Backup:</strong> {if $site.last_backup}{$site.last_backup|date_format:"%Y-%m-%d"}{else}None{/if}
                                            </p>
                                        </div>
                                    </div>
                                    {if $site.ftp_username}
                                        <div class="speedwp-ftp-info" style="background: #f8f9fa; padding: 10px; border-radius: 4px; margin-top: 10px;">
                                            <h5><i class="fa fa-server"></i> FTP Access</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <strong>Host:</strong> {$site.domain}<br>
                                                    <strong>Username:</strong> {$site.ftp_username}
                                                </div>
                                                <div class="col-md-6">
                                                    <strong>Port:</strong> 21<br>
                                                    <strong>Directory:</strong> {$site.wp_path}
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                                <div class="col-md-4">
                                    <div class="speedwp-site-actions text-right">
                                        <a href="{$site.admin_url}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fa fa-sign-in"></i> WP Admin
                                        </a>
                                        <button class="btn btn-info btn-sm" onclick="manageSite({$site.id})">
                                            <i class="fa fa-cog"></i> Manage
                                        </button>
                                        <br>
                                        <button class="btn btn-warning btn-sm" onclick="updateSite({$site.id})" {if $site.needs_update}style="background-color: #f39c12;"{/if}>
                                            <i class="fa fa-refresh"></i> Update
                                            {if $site.needs_update}<span class="badge">!</span>{/if}
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="backupSite({$site.id})">
                                            <i class="fa fa-download"></i> Backup
                                        </button>
                                        <br>
                                        <button class="btn btn-default btn-sm" onclick="resetPassword({$site.id})">
                                            <i class="fa fa-key"></i> Reset Password
                                        </button>
                                        <button class="btn btn-default btn-sm" onclick="toggleMaintenance({$site.id}, {$site.maintenance_mode})">
                                            <i class="fa fa-wrench"></i> 
                                            {if $site.maintenance_mode}Disable{else}Enable{/if} Maintenance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}

    {if $hosting_accounts}
        <div class="row">
            <div class="col-md-12">
                <div class="speedwp-card">
                    <h3>Hosting Accounts</h3>
                    <p class="text-muted">Scan your hosting accounts for WordPress installations or install new ones.</p>
                    
                    {foreach from=$hosting_accounts item=account}
                        <div class="speedwp-site-item">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4><i class="fa fa-server"></i> {$account.domain}</h4>
                                    <p class="text-muted">Package: {$account.product_name}</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="speedwp-site-actions text-right">
                                        <button class="btn btn-info btn-sm" onclick="scanAccount({$account.id})">
                                            <i class="fa fa-search"></i> Scan for WordPress
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="installWordPress({$account.id})">
                                            <i class="fa fa-plus"></i> Install WordPress
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            </div>
        </div>
    {else}
        <div class="row">
            <div class="col-md-12">
                <div class="speedwp-card">
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> No Active Hosting Accounts</h4>
                        <p>You don't have any active hosting accounts. WordPress Manager requires an active hosting service to function.</p>
                        <p><a href="cart.php" class="btn btn-primary">Browse Hosting Plans</a></p>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {* Quick Actions Section *}
    <div class="row">
        <div class="col-md-12">
            <div class="speedwp-card">
                <h3>Quick Actions</h3>
                <div class="row">
                    <div class="col-md-3">
                        <button class="btn btn-block btn-primary" onclick="scanAllAccounts()">
                            <i class="fa fa-search"></i><br>
                            Scan All Accounts
                        </button>
                        <small class="text-muted">Find existing WordPress installations</small>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-block btn-success" onclick="showInstallModal()">
                            <i class="fa fa-plus"></i><br>
                            Install WordPress
                        </button>
                        <small class="text-muted">Create new WordPress site</small>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-block btn-warning" onclick="updateAllSites()">
                            <i class="fa fa-refresh"></i><br>
                            Update All Sites
                        </button>
                        <small class="text-muted">Update WordPress core and plugins</small>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-block btn-info" onclick="backupAllSites()">
                            <i class="fa fa-download"></i><br>
                            Backup All Sites
                        </button>
                        <small class="text-muted">Create backups of all sites</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* Loading overlay *}
<div id="speedwp-loading" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 4px;">
        <i class="fa fa-spinner fa-spin"></i> Processing...
    </div>
</div>

{* JavaScript for AJAX operations *}
<script>
{literal}
function showLoading() {
    document.getElementById('speedwp-loading').style.display = 'block';
}

function hideLoading() {
    document.getElementById('speedwp-loading').style.display = 'none';
}

function manageSite(siteId) {
    // Show site management modal with detailed options
    var modalHtml = `
        <div class="modal fade" id="manageSiteModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Manage WordPress Site</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Site Actions</h5>
                                <div class="list-group">
                                    <a href="#" class="list-group-item" onclick="changePassword(${siteId})">
                                        <i class="fa fa-key"></i> Change Admin Password
                                    </a>
                                    <a href="#" class="list-group-item" onclick="changeSiteTitle(${siteId})">
                                        <i class="fa fa-edit"></i> Change Site Title
                                    </a>
                                    <a href="#" class="list-group-item" onclick="managePlugins(${siteId})">
                                        <i class="fa fa-plug"></i> Manage Plugins
                                    </a>
                                    <a href="#" class="list-group-item" onclick="manageThemes(${siteId})">
                                        <i class="fa fa-paint-brush"></i> Manage Themes
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Backup & Security</h5>
                                <div class="list-group">
                                    <a href="#" class="list-group-item" onclick="viewBackups(${siteId})">
                                        <i class="fa fa-history"></i> View Backups
                                    </a>
                                    <a href="#" class="list-group-item" onclick="securityScan(${siteId})">
                                        <i class="fa fa-shield"></i> Security Scan
                                    </a>
                                    <a href="#" class="list-group-item" onclick="viewLogs(${siteId})">
                                        <i class="fa fa-list"></i> Activity Logs
                                    </a>
                                    <a href="#" class="list-group-item" onclick="siteHealth(${siteId})">
                                        <i class="fa fa-heartbeat"></i> Site Health
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('#manageSiteModal').modal('show');
    $('#manageSiteModal').on('hidden.bs.modal', function () {
        $(this).remove();
    });
}

function resetPassword(siteId) {
    if (!confirm('Reset WordPress admin password for this site? A new password will be generated.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'reset_password',
            site_id: siteId
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Password reset successful!\nNew password: ' + response.new_password);
            } else {
                alert('Error: ' + (response.error || 'Password reset failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function toggleMaintenance(siteId, currentStatus) {
    var action = currentStatus ? 'disable' : 'enable';
    var message = action === 'enable' ? 'Enable maintenance mode?' : 'Disable maintenance mode?';
    
    if (!confirm(message)) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'toggle_maintenance',
            site_id: siteId,
            maintenance_mode: !currentStatus
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Maintenance mode ' + action + 'd successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Operation failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function changePassword(siteId) {
    var newPassword = prompt('Enter new WordPress admin password (leave blank to generate):');
    if (newPassword === null) return;
    
    showLoading();
    
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'change_password',
            site_id: siteId,
            new_password: newPassword
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Password changed successfully!' + (response.generated_password ? '\nNew password: ' + response.generated_password : ''));
            } else {
                alert('Error: ' + (response.error || 'Password change failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function changeSiteTitle(siteId) {
    var newTitle = prompt('Enter new site title:');
    if (!newTitle) return;
    
    showLoading();
    
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'change_site_title',
            site_id: siteId,
            site_title: newTitle
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Site title changed successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Title change failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function managePlugins(siteId) {
    alert('Plugin management interface coming soon!');
    // TODO: Implement plugin management modal
}

function manageThemes(siteId) {
    alert('Theme management interface coming soon!');
    // TODO: Implement theme management modal
}

function viewBackups(siteId) {
    alert('Backup management interface coming soon!');
    // TODO: Implement backup viewing modal
}

function securityScan(siteId) {
    alert('Security scanning feature coming soon!');
    // TODO: Implement security scanning
}

function viewLogs(siteId) {
    alert('Activity logs viewer coming soon!');
    // TODO: Implement activity logs modal
}

function siteHealth(siteId) {
    alert('Site health check coming soon!');
    // TODO: Implement site health check
}

function updateSite(siteId) {
    if (!confirm('Update WordPress for this site? This may take a few minutes.')) {
        return;
    }
    
    showLoading();
    
    // TODO: Implement AJAX call to update WordPress
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'update_wordpress',
            site_id: siteId
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('WordPress update completed successfully!');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Update failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function backupSite(siteId) {
    if (!confirm('Create backup for this WordPress site?')) {
        return;
    }
    
    showLoading();
    
    // TODO: Implement AJAX call to backup WordPress
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'backup_wordpress',
            site_id: siteId
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Backup created successfully!\nFile: ' + (response.backup_file || 'backup.tar.gz'));
            } else {
                alert('Error: ' + (response.error || 'Backup failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function scanAccount(accountId) {
    showLoading();
    
    // TODO: Implement AJAX call to scan hosting account
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'scan_wordpress',
            hosting_id: accountId
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('Scan completed! Found ' + response.sites_found + ' WordPress installation(s).');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Scan failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function installWordPress(accountId) {
    // TODO: Show installation modal with options
    var domain = prompt('Enter domain or subdirectory for WordPress installation:');
    if (!domain) return;
    
    showLoading();
    
    // TODO: Implement AJAX call to install WordPress
    $.ajax({
        url: 'index.php?m=speedwp&action=ajax',
        method: 'POST',
        data: {
            action: 'install_wordpress',
            hosting_id: accountId,
            domain: domain,
            path: '/'
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                alert('WordPress installation started! Please check back in a few minutes.');
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Installation failed'));
            }
        },
        error: function() {
            hideLoading();
            alert('Communication error. Please try again.');
        }
    });
}

function scanAllAccounts() {
    if (!confirm('Scan all hosting accounts for WordPress? This may take a few minutes.')) {
        return;
    }
    
    alert('Bulk scanning feature coming soon!');
    // TODO: Implement bulk scanning
}

function showInstallModal() {
    alert('WordPress installation wizard coming soon!');
    // TODO: Show modal with installation options
}

function updateAllSites() {
    if (!confirm('Update all WordPress sites? This may take several minutes.')) {
        return;
    }
    
    alert('Bulk update feature coming soon!');
    // TODO: Implement bulk updates
}

function backupAllSites() {
    if (!confirm('Create backups for all WordPress sites? This may take several minutes.')) {
        return;
    }
    
    alert('Bulk backup feature coming soon!');
    // TODO: Implement bulk backups
}
{/literal}
</script>
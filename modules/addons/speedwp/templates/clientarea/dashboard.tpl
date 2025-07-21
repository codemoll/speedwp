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
                                        {$site.domain}{$site.wp_path}
                                        <small class="speedwp-status-{$site.status}">
                                            ({$site.status|ucfirst})
                                        </small>
                                    </h4>
                                    <p class="text-muted">
                                        {if $site.wp_version}
                                            WordPress Version: {$site.wp_version} | 
                                        {/if}
                                        Last Updated: {$site.updated_at|date_format:"%Y-%m-%d %H:%M"}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <div class="speedwp-site-actions text-right">
                                        {* TODO: Add actual functionality to these buttons *}
                                        <button class="btn btn-primary btn-sm" onclick="manageSite({$site.id})">
                                            <i class="fa fa-cog"></i> Manage
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="updateSite({$site.id})">
                                            <i class="fa fa-refresh"></i> Update
                                        </button>
                                        <button class="btn btn-success btn-sm" onclick="backupSite({$site.id})">
                                            <i class="fa fa-download"></i> Backup
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
    // TODO: Implement site management modal or redirect
    alert('Site management interface coming soon!\nSite ID: ' + siteId);
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
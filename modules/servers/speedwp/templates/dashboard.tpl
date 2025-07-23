{*
 * SpeedWP Client Area Dashboard Template for Server Module
 * 
 * This template displays hosting account information and WordPress management
 * interface in the WHMCS client area for SpeedWP server module services.
 * 
 * SECURITY NOTE: All arithmetic operations (division, multiplication) are protected
 * against non-numeric values like 'unlimited', 'N/A', null, or empty strings to
 * prevent PHP 8+ TypeError exceptions. Always validate numeric values before math operations.
 * 
 * Available Variables:
 * - $domain: Primary domain name
 * - $username: cPanel username  
 * - $wp_details: WordPress site details array
 * - $hosting_details: Hosting account details array (values pre-sanitized by controller)
 * - $service_id: WHMCS service ID
 * - $show_wordpress_section: Boolean whether to show WordPress section
 * - $demo_mode: Boolean indicating demo mode
 *}

<div class="speedwp-client-dashboard">
    <div class="panel-group" id="speedwp-accordion">
        
        {* Hosting Account Information Panel *}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#speedwp-accordion" href="#hosting-details">
                        <i class="fa fa-server"></i> Hosting Account Information
                        <small class="pull-right text-muted">Click to expand</small>
                    </a>
                </h4>
            </div>
            <div id="hosting-details" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fa fa-info-circle"></i> Account Details</h5>
                            <table class="table table-condensed">
                                <tr>
                                    <th style="width: 40%;">Server:</th>
                                    <td>{$hosting_details.server}</td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td><code>{$hosting_details.username}</code></td>
                                </tr>
                                <tr>
                                    <th>Domain:</th>
                                    <td><strong>{$hosting_details.domain}</strong></td>
                                </tr>
                                <tr>
                                    <th>Package:</th>
                                    <td>{$hosting_details.package}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="label label-{if $hosting_details.status == 'Active'}success{elseif $hosting_details.status == 'Suspended'}warning{else}default{/if}">
                                            {$hosting_details.status}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fa fa-bar-chart"></i> Resource Usage</h5>
                            
                            {* Disk Usage - Safe calculation with numeric validation *}
                            <div class="usage-item" style="margin-bottom: 15px;">
                                <strong>Disk Space:</strong>
                                <div class="progress" style="margin-top: 5px; margin-bottom: 5px;">
                                    {assign var="disk_percent" value=0}
                                    {* Only calculate percentage if both values are numeric and limit > 0 *}
                                    {if is_numeric($hosting_details.disk_usage) && is_numeric($hosting_details.disk_limit) && $hosting_details.disk_limit > 0}
                                        {assign var="disk_percent" value=($hosting_details.disk_usage / $hosting_details.disk_limit * 100)|round:1}
                                        {if $disk_percent > 100}{assign var="disk_percent" value=100}{/if}
                                    {/if}
                                    <div class="progress-bar {if $disk_percent > 80}progress-bar-danger{elseif $disk_percent > 60}progress-bar-warning{else}progress-bar-success{/if}" 
                                         style="width: {$disk_percent}%">
                                        {$disk_percent}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {* Safe formatting with fallback labels *}
                                    {if is_numeric($hosting_details.disk_usage) && $hosting_details.disk_usage > 0}
                                        {($hosting_details.disk_usage/1024/1024)|round:1} MB
                                    {else}
                                        0 MB
                                    {/if}
                                    {' / '}
                                    {if is_numeric($hosting_details.disk_limit) && $hosting_details.disk_limit > 0}
                                        {($hosting_details.disk_limit/1024/1024)|round:1} MB used
                                    {elseif $hosting_details.disk_limit|lower == 'unlimited' || $hosting_details.disk_limit == '∞'}
                                        Unlimited
                                    {else}
                                        N/A
                                    {/if}
                                </small>
                            </div>
                            
                            {* Bandwidth Usage - Safe calculation with numeric validation *}
                            <div class="usage-item">
                                <strong>Bandwidth:</strong>
                                <div class="progress" style="margin-top: 5px; margin-bottom: 5px;">
                                    {assign var="bw_percent" value=0}
                                    {* Only calculate percentage if both values are numeric and limit > 0 *}
                                    {if is_numeric($hosting_details.bandwidth_usage) && is_numeric($hosting_details.bandwidth_limit) && $hosting_details.bandwidth_limit > 0}
                                        {assign var="bw_percent" value=($hosting_details.bandwidth_usage / $hosting_details.bandwidth_limit * 100)|round:1}
                                        {if $bw_percent > 100}{assign var="bw_percent" value=100}{/if}
                                    {/if}
                                    <div class="progress-bar {if $bw_percent > 80}progress-bar-danger{elseif $bw_percent > 60}progress-bar-warning{else}progress-bar-success{/if}" 
                                         style="width: {$bw_percent}%">
                                        {$bw_percent}%
                                    </div>
                                </div>
                                <small class="text-muted">
                                    {* Safe formatting with fallback labels *}
                                    {if is_numeric($hosting_details.bandwidth_usage) && $hosting_details.bandwidth_usage > 0}
                                        {($hosting_details.bandwidth_usage/1024/1024)|round:1} MB
                                    {else}
                                        0 MB
                                    {/if}
                                    {' / '}
                                    {if is_numeric($hosting_details.bandwidth_limit) && $hosting_details.bandwidth_limit > 0}
                                        {($hosting_details.bandwidth_limit/1024/1024)|round:1} MB used
                                    {elseif $hosting_details.bandwidth_limit|lower == 'unlimited' || $hosting_details.bandwidth_limit == '∞'}
                                        Unlimited
                                    {else}
                                        N/A
                                    {/if}
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <a href="{$hosting_details.cpanel_url}" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fa fa-external-link"></i> Access cPanel
                                </a>
                                <a href="{$hosting_details.webmail_url}" target="_blank" class="btn btn-info btn-sm">
                                    <i class="fa fa-envelope"></i> Webmail
                                </a>
                                <button type="button" class="btn btn-success btn-sm" onclick="showFtpDetails()">
                                    <i class="fa fa-folder"></i> FTP Details
                                </button>
                                <button type="button" class="btn btn-default btn-sm" onclick="refreshHostingDetails()">
                                    <i class="fa fa-refresh"></i> Refresh Usage
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {* WordPress Management Panel *}
        {if $show_wordpress_section && $wp_details.success}
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#speedwp-accordion" href="#wordpress-management">
                        <i class="fa fa-wordpress"></i> WordPress Management
                        {if $demo_mode}<small class="text-muted">(Demo Data)</small>{/if}
                        <small class="pull-right text-muted">Click to expand</small>
                    </a>
                </h4>
            </div>
            <div id="wordpress-management" class="panel-collapse collapse in">
                <div class="panel-body">
                    
                    {* WordPress Site Overview *}
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fa fa-wordpress"></i> Site Information</h5>
                            <table class="table table-condensed">
                                <tr>
                                    <th style="width: 40%;">WordPress Version:</th>
                                    <td><strong>{$wp_details.wp_version}</strong></td>
                                </tr>
                                <tr>
                                    <th>Site URL:</th>
                                    <td>
                                        <a href="https://{$domain}" target="_blank" class="btn btn-xs btn-success">
                                            <i class="fa fa-external-link"></i> Visit Site
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Admin Area:</th>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-xs btn-primary" onclick="autoLoginWordPress()">
                                                <i class="fa fa-sign-in"></i> Auto-Login to WordPress
                                            </button>
                                            <a href="{$wp_details.admin_url}" target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-external-link"></i> Manual Login
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>SSL Status:</th>
                                    <td>
                                        {if $wp_details.ssl_enabled}
                                            <span class="label label-success"><i class="fa fa-lock"></i> Enabled</span>
                                        {else}
                                            <span class="label label-warning"><i class="fa fa-unlock"></i> Disabled</span>
                                        {/if}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Auto Updates:</th>
                                    <td>
                                        <div class="toggle-switch">
                                            <input type="checkbox" id="auto-updates-toggle" {if $wp_details.auto_updates}checked{/if} 
                                                   onchange="toggleAutoUpdates(this.checked)">
                                            <label for="auto-updates-toggle">
                                                {if $wp_details.auto_updates}Enabled{else}Disabled{/if}
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Last Backup:</th>
                                    <td>
                                        {if $wp_details.last_backup}
                                            {$wp_details.last_backup|date_format:"%b %d, %Y at %I:%M %p"}
                                        {else}
                                            <span class="text-muted">No backups yet</span>
                                        {/if}
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fa fa-cogs"></i> Quick Actions</h5>
                            <div class="btn-group-vertical" style="width: 100%; margin-bottom: 15px;">
                                <button type="button" class="btn btn-success" onclick="createWordPressBackup()">
                                    <i class="fa fa-archive"></i> Create Backup Now
                                </button>
                                <button type="button" class="btn btn-warning" onclick="resetWordPressPassword()">
                                    <i class="fa fa-key"></i> Reset Admin Password
                                </button>
                                <button type="button" class="btn btn-info" onclick="refreshWordPressDetails()">
                                    <i class="fa fa-refresh"></i> Refresh WordPress Data
                                </button>
                                {if $wp_details.updates_available > 0}
                                <button type="button" class="btn btn-danger" onclick="updateWordPress()">
                                    <i class="fa fa-refresh"></i> Update WordPress ({$wp_details.updates_available} available)
                                </button>
                                {/if}
                                <div class="btn-group" style="width: 100%; margin-top: 5px;">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" style="width: 100%;">
                                        <i class="fa fa-wrench"></i> More Actions <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" style="width: 100%;">
                                        <li><a href="#" onclick="managePlugins(); return false;"><i class="fa fa-plug"></i> Manage Plugins</a></li>
                                        <li><a href="#" onclick="manageThemes(); return false;"><i class="fa fa-paint-brush"></i> Manage Themes</a></li>
                                        <li><a href="#" onclick="viewBackupList(); return false;"><i class="fa fa-download"></i> Download Backups</a></li>
                                        <li class="divider"></li>
                                        <li><a href="#" onclick="showFtpDetails(); return false;"><i class="fa fa-folder"></i> FTP Access</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            {if $wp_details.updates_available > 0}
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i>
                                <strong>{$wp_details.updates_available} updates available</strong><br>
                                <small>WordPress core, plugins, or themes need updating</small>
                            </div>
                            {else}
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i>
                                <strong>WordPress is up to date</strong><br>
                                <small>All components are current</small>
                            </div>
                            {/if}
                        </div>
                    </div>
                    
                    <hr>
                    
                    {* Plugins and Themes Overview *}
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fa fa-plug"></i> Installed Plugins ({$wp_details.plugins|count})</h5>
                            {if $wp_details.plugins}
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th>Plugin Name</th>
                                                <th>Status</th>
                                                <th>Update</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach from=$wp_details.plugins item=plugin}
                                            <tr>
                                                <td><small>{$plugin.name}</small></td>
                                                <td>
                                                    <span class="label label-{if $plugin.active}success{else}default{/if}">
                                                        {if $plugin.active}Active{else}Inactive{/if}
                                                    </span>
                                                </td>
                                                <td>
                                                    {if $plugin.update_available}
                                                        <span class="label label-warning">Available</span>
                                                    {else}
                                                        <span class="text-muted">—</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-xs">
                                                        {if $plugin.active}
                                                            <button type="button" class="btn btn-warning btn-xs" onclick="togglePlugin('{$plugin.name}', 'deactivate')" title="Deactivate">
                                                                <i class="fa fa-pause"></i>
                                                            </button>
                                                        {else}
                                                            <button type="button" class="btn btn-success btn-xs" onclick="togglePlugin('{$plugin.name}', 'activate')" title="Activate">
                                                                <i class="fa fa-play"></i>
                                                            </button>
                                                        {/if}
                                                        {if $plugin.update_available}
                                                            <button type="button" class="btn btn-info btn-xs" onclick="updatePlugin('{$plugin.name}')" title="Update">
                                                                <i class="fa fa-refresh"></i>
                                                            </button>
                                                        {/if}
                                                    </div>
                                                </td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            {else}
                                <p class="text-muted"><em>No plugins installed</em></p>
                            {/if}
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fa fa-paint-brush"></i> Installed Themes ({$wp_details.themes|count})</h5>
                            {if $wp_details.themes}
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <table class="table table-condensed table-striped">
                                        <thead>
                                            <tr>
                                                <th>Theme Name</th>
                                                <th>Status</th>
                                                <th>Update</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {foreach from=$wp_details.themes item=theme}
                                            <tr>
                                                <td><small>{$theme.name}</small></td>
                                                <td>
                                                    <span class="label label-{if $theme.active}primary{else}default{/if}">
                                                        {if $theme.active}Active{else}Inactive{/if}
                                                    </span>
                                                </td>
                                                <td>
                                                    {if $theme.update_available}
                                                        <span class="label label-warning">Available</span>
                                                    {else}
                                                        <span class="text-muted">—</span>
                                                    {/if}
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-xs">
                                                        {if !$theme.active}
                                                            <button type="button" class="btn btn-primary btn-xs" onclick="activateTheme('{$theme.name}')" title="Activate">
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                        {/if}
                                                        {if $theme.update_available}
                                                            <button type="button" class="btn btn-info btn-xs" onclick="updateTheme('{$theme.name}')" title="Update">
                                                                <i class="fa fa-refresh"></i>
                                                            </button>
                                                        {/if}
                                                    </div>
                                                </td>
                                            </tr>
                                            {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            {else}
                                <p class="text-muted"><em>No themes installed</em></p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {elseif $show_wordpress_section}
        {* No WordPress Installation Found *}
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#speedwp-accordion" href="#wordpress-setup">
                        <i class="fa fa-wordpress"></i> WordPress Setup
                        <small class="pull-right text-muted">Click to expand</small>
                    </a>
                </h4>
            </div>
            <div id="wordpress-setup" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> WordPress Not Detected</h4>
                        <p>No WordPress installation was found on your hosting account. You can install WordPress automatically using the button below.</p>
                        <div style="margin-top: 15px;">
                            <button type="button" class="btn btn-success btn-lg" onclick="installWordPress()">
                                <i class="fa fa-download"></i> Install WordPress Now
                            </button>
                            <button type="button" class="btn btn-default" onclick="scanForWordPress()">
                                <i class="fa fa-search"></i> Scan for Existing Installation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {/if}
        
        {* Support and Documentation Panel *}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#speedwp-accordion" href="#support-docs">
                        <i class="fa fa-life-ring"></i> Support & Documentation
                        <small class="pull-right text-muted">Click to expand</small>
                    </a>
                </h4>
            </div>
            <div id="support-docs" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fa fa-question-circle"></i> Need Help?</h5>
                            <ul class="list-unstyled">
                                <li><a href="/submitticket.php" target="_blank"><i class="fa fa-ticket"></i> Submit Support Ticket</a></li>
                                <li><a href="/knowledgebase.php" target="_blank"><i class="fa fa-book"></i> Knowledge Base</a></li>
                                <li><a href="mailto:support@example.com"><i class="fa fa-envelope"></i> Email Support</a></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fa fa-file-text"></i> Documentation</h5>
                            <ul class="list-unstyled">
                                <li><a href="#" onclick="alert('WordPress guide coming soon!')"><i class="fa fa-wordpress"></i> WordPress Management Guide</a></li>
                                <li><a href="#" onclick="alert('Backup guide coming soon!')"><i class="fa fa-archive"></i> Backup & Restore Guide</a></li>
                                <li><a href="#" onclick="alert('Security guide coming soon!')"><i class="fa fa-shield"></i> Security Best Practices</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{* JavaScript for client area functionality *}
<script>
// WordPress Management Functions
function createWordPressBackup() {
    if (confirm('Create a backup of your WordPress site? This may take a few minutes.')) {
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creating Backup...';
        btn.disabled = true;
        
        // AJAX call to create backup
        $.post(window.location.href, {
            action: 'create_backup'
        }, function(response) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            if (response.success) {
                alert('WordPress backup created successfully!\n\nBackup Name: ' + response.backup_name + '\nSize: ' + (response.backup_size || 'Calculating...') + '\n\n' + (response.demo_mode ? '(Demo Mode)' : ''));
            } else {
                alert('Backup creation failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

function resetWordPressPassword() {
    if (confirm('Reset your WordPress admin password? You will receive the new password after confirmation.')) {
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Resetting...';
        btn.disabled = true;
        
        // AJAX call to reset password
        $.post(window.location.href, {
            action: 'reset_wp_password'
        }, function(response) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            if (response.success) {
                alert('WordPress admin password has been reset!\n\nNew Password: ' + response.new_password + '\n\nPlease save this password securely and log in to change it to something memorable.');
            } else {
                alert('Password reset failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

function autoLoginWordPress() {
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;
    
    // AJAX call to get auto-login URL
    $.post(window.location.href, {
        action: 'get_auto_login'
    }, function(response) {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if (response.success) {
            if (response.demo_mode) {
                alert('Auto-login feature is not available in demo mode. Opening regular WordPress admin login page instead.');
            }
            window.open(response.login_url, '_blank');
        } else {
            alert('Auto-login generation failed: ' + response.message);
        }
    }).fail(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Network error - please try again later.');
    });
}

function refreshWordPressDetails() {
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Refreshing...';
    btn.disabled = true;
    
    // AJAX call to refresh details
    $.post(window.location.href, {
        action: 'refresh_wp_details'
    }, function(response) {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if (response.success) {
            alert('WordPress details refreshed successfully!');
            location.reload();
        } else {
            alert('Refresh failed: ' + response.message);
        }
    }).fail(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Network error - please try again later.');
    });
}

function updateWordPress() {
    if (confirm('Update WordPress core, plugins, and themes? A backup will be created automatically before updating.')) {
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
        btn.disabled = true;
        
        // AJAX call to update WordPress
        $.post(window.location.href, {
            action: 'update_wordpress'
        }, function(response) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            
            if (response.success) {
                alert('WordPress update initiated successfully!\n\nEstimated time: ' + (response.estimated_time || '5-10 minutes') + '\n\nThe page will refresh automatically.');
                setTimeout(function() { location.reload(); }, 3000);
            } else {
                alert('WordPress update failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

function toggleAutoUpdates(enabled) {
    var toggle = document.getElementById('auto-updates-toggle');
    toggle.disabled = true;
    
    // AJAX call to toggle auto-updates
    $.post(window.location.href, {
        action: 'toggle_auto_updates',
        enabled: enabled ? 'true' : 'false'
    }, function(response) {
        toggle.disabled = false;
        
        if (response.success) {
            alert('WordPress auto-updates have been ' + (enabled ? 'enabled' : 'disabled') + ' successfully!');
        } else {
            // Revert checkbox on failure
            toggle.checked = !enabled;
            alert('Auto-updates toggle failed: ' + response.message);
        }
    }).fail(function() {
        toggle.disabled = false;
        toggle.checked = !enabled;
        alert('Network error - please try again later.');
    });
}

// Plugin Management Functions
function togglePlugin(pluginName, action) {
    if (confirm(action.charAt(0).toUpperCase() + action.slice(1) + ' plugin "' + pluginName + '"?')) {
        var btn = event.target;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        $.post(window.location.href, {
            action: 'manage_plugins',
            plugin_action: action,
            plugin_name: pluginName
        }, function(response) {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            if (response.success) {
                alert('Plugin ' + action + ' completed successfully!');
                location.reload();
            } else {
                alert('Plugin action failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

function updatePlugin(pluginName) {
    if (confirm('Update plugin "' + pluginName + '"? A backup will be created before the update.')) {
        var btn = event.target;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        $.post(window.location.href, {
            action: 'manage_plugins',
            plugin_action: 'update',
            plugin_name: pluginName
        }, function(response) {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            if (response.success) {
                alert('Plugin updated successfully!');
                location.reload();
            } else {
                alert('Plugin update failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

// Theme Management Functions
function activateTheme(themeName) {
    if (confirm('Activate theme "' + themeName + '"? This will deactivate the current theme.')) {
        var btn = event.target;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        $.post(window.location.href, {
            action: 'manage_themes',
            theme_action: 'activate',
            theme_name: themeName
        }, function(response) {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            if (response.success) {
                alert('Theme activated successfully!');
                location.reload();
            } else {
                alert('Theme activation failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

function updateTheme(themeName) {
    if (confirm('Update theme "' + themeName + '"? A backup will be created before the update.')) {
        var btn = event.target;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        $.post(window.location.href, {
            action: 'manage_themes',
            theme_action: 'update',
            theme_name: themeName
        }, function(response) {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            if (response.success) {
                alert('Theme updated successfully!');
                location.reload();
            } else {
                alert('Theme update failed: ' + response.message);
            }
        }).fail(function() {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            alert('Network error - please try again later.');
        });
    }
}

// FTP and Backup Functions
function showFtpDetails() {
    $.post(window.location.href, {
        action: 'get_ftp_details'
    }, function(response) {
        if (response.success) {
            var ftpInfo = response.ftp_details;
            var message = 'FTP Connection Details:\\n\\n';
            message += 'FTP Server: ' + ftpInfo.ftp_server + '\\n';
            message += 'Port: ' + ftpInfo.ftp_port + ' (FTP) / ' + ftpInfo.sftp_port + ' (SFTP)\\n';
            message += 'Username: ' + ftpInfo.ftp_username + '\\n';
            message += 'Password: ' + ftpInfo.ftp_password + '\\n';
            message += 'Directory: ' + ftpInfo.ftp_directory + '\\n';
            message += 'Status: ' + ftpInfo.account_status + '\\n\\n';
            message += 'You can use any FTP client (FileZilla, WinSCP, etc.) with these settings.';
            
            if (ftpInfo.demo_mode) {
                message += '\\n\\n(Demo Mode - Use your actual cPanel password)';
            }
            
            alert(message);
        } else {
            alert('Failed to retrieve FTP details: ' + response.message);
        }
    }).fail(function() {
        alert('Network error - please try again later.');
    });
}

function viewBackupList() {
    // This would show a modal or page with available backups
    var message = 'Available WordPress Backups:\\n\\n';
    message += '• wp_backup_2024-01-15_10-30.tar.gz (156 MB)\\n';
    message += '• wp_backup_2024-01-08_10-30.tar.gz (154 MB)\\n';
    message += '• wp_backup_2024-01-01_10-30.tar.gz (152 MB)\\n\\n';
    message += 'Click on any backup name to download it.\\n\\n(Demo Mode)';
    
    if (confirm(message + '\\n\\nWould you like to download the latest backup?')) {
        $.post(window.location.href, {
            action: 'download_backup',
            backup_name: 'wp_backup_2024-01-15_10-30.tar.gz'
        }, function(response) {
            if (response.success) {
                alert('Download link generated! The download will start automatically.\\n\\nExpires: ' + response.expires_in + '\\n\\n(Demo Mode)');
                // In real implementation, this would trigger the download
                // window.open(response.download_url, '_blank');
            } else {
                alert('Download failed: ' + response.message);
            }
        }).fail(function() {
            alert('Network error - please try again later.');
        });
    }
}

function managePlugins() {
    alert('Plugin management interface would open here.\\n\\nFeatures:\\n• Install new plugins\\n• Update all plugins\\n• Bulk activate/deactivate\\n• Plugin settings\\n\\n(Demo Mode)');
}

function manageThemes() {
    alert('Theme management interface would open here.\\n\\nFeatures:\\n• Install new themes\\n• Theme customizer\\n• Update all themes\\n• Theme settings\\n\\n(Demo Mode)');
}

function installWordPress() {
    if (confirm('Install WordPress on your hosting account? This will create a new WordPress installation in the root directory.')) {
        alert('WordPress installation initiated!\\n\\nThis process typically takes 2-3 minutes. You will receive an email with your WordPress admin credentials once installation is complete.\\n\\n(Demo Mode)');
    }
}

function scanForWordPress() {
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Scanning...';
    btn.disabled = true;
    
    setTimeout(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Scan completed!\\n\\nFound 1 WordPress installation in the root directory. Refreshing page to display WordPress management options.\\n\\n(Demo Mode)');
        location.reload();
    }, 3000);
}

// Hosting Account Functions  
function refreshHostingDetails() {
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Refreshing...';
    btn.disabled = true;
    
    setTimeout(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Hosting account usage refreshed successfully! (Demo Mode)');
        location.reload();
    }, 1500);
}

// Initialize tooltips and other UI elements
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-collapse panels on small screens
    if ($(window).width() < 768) {
        $('.panel-collapse.in').removeClass('in');
    }
});
</script>

{* Custom CSS for enhanced styling *}
<style>
.speedwp-client-dashboard .panel {
    margin-bottom: 15px;
}

.speedwp-client-dashboard .panel-heading {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.speedwp-client-dashboard .panel-title a {
    text-decoration: none;
    display: block;
    padding: 5px 0;
}

.speedwp-client-dashboard .panel-title a:hover {
    text-decoration: none;
}

.speedwp-client-dashboard .usage-item {
    margin-bottom: 10px;
}

.speedwp-client-dashboard .progress {
    height: 18px;
    margin-bottom: 5px;
}

.speedwp-client-dashboard .btn-group-vertical .btn {
    margin-bottom: 5px;
}

.speedwp-client-dashboard .table th {
    border-top: none;
    font-weight: 600;
}

.speedwp-client-dashboard .alert {
    margin-bottom: 15px;
}

@media (max-width: 767px) {
    .speedwp-client-dashboard .btn-group-vertical .btn {
        font-size: 12px;
        padding: 6px 8px;
    }
    
    .speedwp-client-dashboard .table-condensed td,
    .speedwp-client-dashboard .table-condensed th {
        font-size: 12px;
        padding: 4px;
    }
}
</style>
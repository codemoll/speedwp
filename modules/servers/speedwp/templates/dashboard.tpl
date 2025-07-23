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
                                        <a href="{$wp_details.admin_url}" target="_blank" class="btn btn-xs btn-primary">
                                            <i class="fa fa-sign-in"></i> Login to WordPress
                                        </a>
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
        
        // Simulate backup creation
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('WordPress backup created successfully!\n\nBackup Name: wp_backup_' + new Date().toISOString().slice(0,19).replace(/:/g, '-') + '.tar.gz\nSize: ~156 MB\n\n(Demo Mode)');
        }, 3000);
    }
}

function resetWordPressPassword() {
    if (confirm('Reset your WordPress admin password? You will receive the new password after confirmation.')) {
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Resetting...';
        btn.disabled = true;
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            var newPassword = 'WP' + Math.floor(Math.random() * 100000);
            alert('WordPress admin password has been reset!\n\nNew Password: ' + newPassword + '\n\nPlease save this password securely and log in to change it to something memorable.\n\n(Demo Mode)');
        }, 2000);
    }
}

function refreshWordPressDetails() {
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Refreshing...';
    btn.disabled = true;
    
    setTimeout(function() {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('WordPress details refreshed successfully! (Demo Mode)');
        location.reload();
    }, 2000);
}

function updateWordPress() {
    if (confirm('Update WordPress core, plugins, and themes? A backup will be created automatically before updating.')) {
        var btn = event.target;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Updating...';
        btn.disabled = true;
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('WordPress has been updated successfully!\n\n• WordPress core updated to latest version\n• 2 plugins updated\n• 1 theme updated\n• Automatic backup created\n\n(Demo Mode)');
            location.reload();
        }, 5000);
    }
}

function toggleAutoUpdates(enabled) {
    var status = enabled ? 'enabled' : 'disabled';
    
    // Show loading state
    var toggle = document.getElementById('auto-updates-toggle');
    toggle.disabled = true;
    
    setTimeout(function() {
        toggle.disabled = false;
        alert('WordPress auto-updates have been ' + status + ' successfully! (Demo Mode)');
    }, 1000);
}

function installWordPress() {
    if (confirm('Install WordPress on your hosting account? This will create a new WordPress installation in the root directory.')) {
        alert('WordPress installation initiated!\n\nThis process typically takes 2-3 minutes. You will receive an email with your WordPress admin credentials once installation is complete.\n\n(Demo Mode)');
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
        alert('Scan completed!\n\nFound 1 WordPress installation in the root directory. Refreshing page to display WordPress management options.\n\n(Demo Mode)');
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
{*
 * SpeedWP Admin Error Template
 * 
 * Error display for admin area issues.
 * 
 * @package    SpeedWP
 * @author     SpeedWP Team
 * @version    1.0.0
 *}

<div class="speedwp-admin-error" style="margin: 20px 0;">
    <div class="alert alert-danger">
        <h4><i class="fa fa-exclamation-triangle"></i> SpeedWP Error</h4>
        {if $error_message}
            <p><strong>{$error_message}</strong></p>
        {else}
            <p><strong>An error occurred while loading the SpeedWP dashboard.</strong></p>
        {/if}
        
        <hr>
        
        <h5>Troubleshooting:</h5>
        <ul>
            <li>Check that the SpeedWP addon is properly activated</li>
            <li>Verify database tables were created during activation</li>
            <li>Ensure cPanel host is configured in addon settings</li>
            <li>Check WHMCS activity logs for detailed error messages</li>
        </ul>
        
        <p style="margin-top: 15px;">
            <a href="configaddonmods.php" class="btn btn-primary">
                <i class="fa fa-cog"></i> Configure SpeedWP Settings
            </a>
            <a href="addonmodules.php" class="btn btn-default">
                <i class="fa fa-arrow-left"></i> Back to Addon Modules
            </a>
        </p>
    </div>
</div>

<style>
.speedwp-admin-error .alert {
    padding: 20px;
    border-radius: 4px;
}
.speedwp-admin-error h4 {
    margin-top: 0;
    color: #d9534f;
}
.speedwp-admin-error h5 {
    margin-bottom: 10px;
    color: #333;
}
.speedwp-admin-error ul {
    margin-bottom: 0;
}
.speedwp-admin-error .btn {
    margin-right: 10px;
}
</style>
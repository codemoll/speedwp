{*
 * SpeedWP Client Area Error Template
 * 
 * Enhanced error display template for client-facing error messages with better UX.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 *}

<div class="speedwp-error">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-exclamation-triangle"></i> WordPress Manager Error
            </h3>
        </div>
        <div class="panel-body">
            {if $error}
                <div class="alert alert-danger">
                    <h4><i class="fa fa-exclamation-circle"></i> Error</h4>
                    <p><strong>{$error}</strong></p>
                    
                    {if $error_details}
                        <hr>
                        <p><strong>Technical Details:</strong></p>
                        <p class="small text-muted">{$error_details}</p>
                    {/if}
                    
                    {if $support_message}
                        <hr>
                        <p>{$support_message}</p>
                    {/if}
                </div>
            {else}
                <div class="alert alert-danger">
                    <h4><i class="fa fa-exclamation-circle"></i> Unexpected Error</h4>
                    <p><strong>An error occurred while processing your request.</strong></p>
                    <p>Please try again or contact support if the issue persists.</p>
                </div>
            {/if}
            
            <div class="well well-sm">
                <h5>What you can try:</h5>
                <ul class="mb-0">
                    <li>Refresh this page to try again</li>
                    <li>Check your hosting account status in the client area</li>
                    <li>Verify your hosting service is active and not suspended</li>
                    <li>Contact support if the error continues to occur</li>
                </ul>
            </div>
            
            <div class="text-center" style="margin-top: 20px;">
                <a href="javascript:location.reload()" class="btn btn-warning">
                    <i class="fa fa-refresh"></i> Try Again
                </a>
                <a href="{$modulelink|default:'index.php?m=speedwp'}" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> Back to WordPress Manager
                </a>
                <a href="clientarea.php" class="btn btn-default">
                    <i class="fa fa-home"></i> Client Area Home
                </a>
                <a href="submitticket.php" class="btn btn-info">
                    <i class="fa fa-support"></i> Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.speedwp-error .panel-danger {
    margin-top: 20px;
}

.speedwp-error .alert {
    margin-bottom: 20px;
}

.speedwp-error .well {
    background-color: #f5f5f5;
    border: 1px solid #e3e3e3;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.speedwp-error .btn {
    margin: 0 5px 10px 0;
}

.speedwp-error .mb-0 {
    margin-bottom: 0;
}
</style>
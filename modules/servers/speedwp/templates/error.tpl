{*
 * SpeedWP Error Template for Server Module
 * 
 * This template displays error messages when the SpeedWP server module
 * encounters issues during operation.
 * 
 * Available Variables:
 * - $error: Main error message
 * - $details: Detailed error information (optional)
 * - $support_message: Support contact message (optional)
 *}

<div class="speedwp-error-page">
    <div class="panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="fa fa-exclamation-triangle"></i> SpeedWP Error
            </h3>
        </div>
        <div class="panel-body">
            <div class="alert alert-danger">
                <h4><i class="fa fa-times-circle"></i> Error Loading WordPress Management</h4>
                <p><strong>{$error}</strong></p>
                
                {if $details}
                <div class="error-details" style="margin-top: 15px; padding: 10px; background: #f8f8f8; border-radius: 4px;">
                    <h5>Technical Details:</h5>
                    <p class="text-muted"><small>{$details}</small></p>
                </div>
                {/if}
            </div>
            
            <div class="troubleshooting-steps">
                <h4><i class="fa fa-wrench"></i> Troubleshooting Steps</h4>
                <ol>
                    <li>Refresh this page to retry loading the WordPress management interface</li>
                    <li>Check that your hosting account is active and properly configured</li>
                    <li>Verify that WordPress is installed on your hosting account</li>
                    <li>Ensure that the server configuration is correct</li>
                    <li>Contact support if the problem persists</li>
                </ol>
            </div>
            
            <div class="error-actions" style="margin-top: 20px;">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" onclick="location.reload()">
                        <i class="fa fa-refresh"></i> Retry Loading
                    </button>
                    <a href="clientarea.php" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Client Area
                    </a>
                    <a href="submitticket.php" class="btn btn-warning">
                        <i class="fa fa-ticket"></i> Contact Support
                    </a>
                </div>
            </div>
            
            {if $support_message}
            <div class="alert alert-info" style="margin-top: 20px;">
                <h5><i class="fa fa-info-circle"></i> Support Information</h5>
                <p>{$support_message}</p>
            </div>
            {/if}
        </div>
    </div>
    
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <i class="fa fa-question-circle"></i> Need Immediate Help?
            </h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="support-option text-center">
                        <i class="fa fa-ticket fa-3x text-primary"></i>
                        <h5>Submit a Ticket</h5>
                        <p class="text-muted">Get help from our technical support team</p>
                        <a href="submitticket.php" class="btn btn-primary btn-sm">Submit Ticket</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="support-option text-center">
                        <i class="fa fa-book fa-3x text-info"></i>
                        <h5>Knowledge Base</h5>
                        <p class="text-muted">Search our help documentation</p>
                        <a href="knowledgebase.php" class="btn btn-info btn-sm">Browse Articles</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="support-option text-center">
                        <i class="fa fa-envelope fa-3x text-success"></i>
                        <h5>Email Support</h5>
                        <p class="text-muted">Send us an email for assistance</p>
                        <a href="mailto:support@example.com" class="btn btn-success btn-sm">Send Email</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.speedwp-error-page .support-option {
    margin-bottom: 20px;
    padding: 20px;
    border: 1px solid #e1e1e1;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.speedwp-error-page .support-option:hover {
    border-color: #ccc;
    background-color: #f9f9f9;
}

.speedwp-error-page .troubleshooting-steps ol {
    padding-left: 20px;
}

.speedwp-error-page .troubleshooting-steps li {
    margin-bottom: 5px;
}

.speedwp-error-page .error-details {
    border-left: 4px solid #d9534f;
    padding-left: 15px;
}

.speedwp-error-page .btn-group .btn {
    margin-right: 5px;
}
</style>
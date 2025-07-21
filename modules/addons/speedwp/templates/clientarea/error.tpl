{*
 * SpeedWP Error Template
 * 
 * Error page for SpeedWP client area issues.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
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
                    <strong>Error:</strong> {$error}
                </div>
            {else}
                <div class="alert alert-danger">
                    <strong>An error occurred while processing your request.</strong>
                </div>
            {/if}
            
            <p>
                <a href="{$modulelink}" class="btn btn-primary">
                    <i class="fa fa-arrow-left"></i> Return to WordPress Manager
                </a>
                <a href="clientarea.php" class="btn btn-default">
                    <i class="fa fa-home"></i> Client Area Home
                </a>
            </p>
        </div>
    </div>
</div>
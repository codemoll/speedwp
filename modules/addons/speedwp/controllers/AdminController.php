<?php
/**
 * SpeedWP Admin Area Controller
 * 
 * Handles admin area WordPress management interface and functionality.
 * 
 * @package    SpeedWP
 * @author     Your Name
 * @version    1.0.0
 * @link       https://github.com/codemoll/speedwp
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

class SpeedWP_AdminController
{
    /**
     * @var array Module configuration variables
     */
    private $vars;

    /**
     * Constructor
     * 
     * @param array $vars Module configuration variables
     */
    public function __construct($vars)
    {
        $this->vars = $vars;
    }

    /**
     * Main admin area dashboard
     * 
     * @return string HTML output for admin area
     */
    public function index()
    {
        $action = $_GET['action'] ?? 'dashboard';
        
        switch ($action) {
            case 'sites':
                return $this->manageSites();
            case 'clients':
                return $this->manageClients();
            case 'settings':
                return $this->settings();
            case 'tools':
                return $this->tools();
            default:
                return $this->dashboard();
        }
    }

    /**
     * Dashboard overview
     * 
     * @return string HTML output
     */
    private function dashboard()
    {
        // TODO: Get overview statistics
        $stats = $this->getOverviewStats();
        
        // TODO: Get recent activity
        $recentActivity = $this->getRecentActivity();
        
        $output = '<div class="speedwp-admin-dashboard">';
        $output .= '<h2>SpeedWP WordPress Manager - Dashboard</h2>';
        
        // Stats cards
        $output .= '<div class="row">';
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['total_sites'] . '</h3>';
        $output .= '<p>Total WordPress Sites</p>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['active_sites'] . '</h3>';
        $output .= '<p>Active Sites</p>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['total_clients'] . '</h3>';
        $output .= '<p>Clients with WP</p>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $this->formatBytes($stats['disk_usage']) . '</h3>';
        $output .= '<p>Total Disk Usage</p>';
        $output .= '</div></div></div>';
        $output .= '</div>';
        
        // Additional stats row
        $output .= '<div class="row">';
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['total_plugins'] . '</h3>';
        $output .= '<p>Total Plugins</p>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['total_themes'] . '</h3>';
        $output .= '<p>Total Themes</p>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['total_backups'] . '</h3>';
        $output .= '<p>Total Backups</p>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="col-md-3">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-body text-center">';
        $output .= '<h3>' . $stats['updates_available'] . '</h3>';
        $output .= '<p>Updates Available</p>';
        $output .= '</div></div></div>';
        $output .= '</div>';
        
        // Navigation buttons
        $output .= '<div class="row" style="margin-top: 20px;">';
        $output .= '<div class="col-md-12">';
        $output .= '<a href="?m=speedwp&action=sites" class="btn btn-primary">Manage Sites</a> ';
        $output .= '<a href="?m=speedwp&action=clients" class="btn btn-info">Manage Clients</a> ';
        $output .= '<a href="?m=speedwp&action=tools" class="btn btn-warning">Tools</a> ';
        $output .= '<a href="?m=speedwp&action=settings" class="btn btn-default">Settings</a>';
        $output .= '</div></div>';
        
        // Recent activity
        $output .= '<div class="row" style="margin-top: 30px;">';
        $output .= '<div class="col-md-12">';
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-heading"><h4>Recent Activity</h4></div>';
        $output .= '<div class="panel-body">';
        
        if (empty($recentActivity)) {
            $output .= '<p><em>No recent activity to display.</em></p>';
        } else {
            $output .= '<div class="table-responsive">';
            $output .= '<table class="table table-striped">';
            $output .= '<thead><tr><th>Date</th><th>Description</th><th>Site</th><th>Client</th></tr></thead>';
            $output .= '<tbody>';
            
            foreach ($recentActivity as $activity) {
                $output .= '<tr>';
                $output .= '<td>' . date('Y-m-d H:i', strtotime($activity['created_at'])) . '</td>';
                $output .= '<td>' . htmlspecialchars($activity['description']) . '</td>';
                $output .= '<td>' . htmlspecialchars($activity['domain'] . $activity['wp_path']) . '</td>';
                $output .= '<td>' . htmlspecialchars($activity['client_name'] ?? 'System') . '</td>';
                $output .= '</tr>';
            }
            
            $output .= '</tbody></table>';
            $output .= '</div>';
        }
        
        $output .= '</div></div></div></div>';
        
        // TODO: Add charts and graphs for better visualization
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Manage WordPress sites
     * 
     * @return string HTML output
     */
    private function manageSites()
    {
        // TODO: Get all WordPress sites with pagination
        $sites = $this->getAllWordPressSites();
        
        $output = '<div class="speedwp-admin-sites">';
        $output .= '<h2>WordPress Sites Management</h2>';
        
        $output .= '<div class="panel panel-default">';
        $output .= '<div class="panel-heading">';
        $output .= '<div class="row">';
        $output .= '<div class="col-md-6"><h4>All WordPress Sites</h4></div>';
        $output .= '<div class="col-md-6 text-right">';
        $output .= '<button class="btn btn-success btn-sm" onclick="scanAllSites()">Scan All Accounts</button>';
        $output .= '</div></div></div>';
        
        $output .= '<div class="panel-body">';
        
        if (empty($sites)) {
            $output .= '<p><em>No WordPress sites found. Use the "Scan All Accounts" button to detect existing installations.</em></p>';
        } else {
            $output .= '<div class="table-responsive">';
            $output .= '<table class="table table-striped">';
            $output .= '<thead>';
            $output .= '<tr>';
            $output .= '<th>Domain</th>';
            $output .= '<th>Client</th>';
            $output .= '<th>Version</th>';
            $output .= '<th>Status</th>';
            $output .= '<th>Last Updated</th>';
            $output .= '<th>Actions</th>';
            $output .= '</tr>';
            $output .= '</thead>';
            $output .= '<tbody>';
            
            foreach ($sites as $site) {
                $output .= '<tr>';
                $output .= '<td>' . htmlspecialchars($site['domain'] . $site['wp_path']) . '</td>';
                $output .= '<td>' . htmlspecialchars($site['client_name'] ?? 'Unknown') . '</td>';
                $output .= '<td>' . htmlspecialchars($site['wp_version'] ?? 'Unknown') . '</td>';
                $output .= '<td><span class="label label-' . $this->getStatusClass($site['status']) . '">' . ucfirst($site['status']) . '</span></td>';
                $output .= '<td>' . date('Y-m-d H:i', strtotime($site['updated_at'])) . '</td>';
                $output .= '<td>';
                $output .= '<button class="btn btn-xs btn-primary" onclick="manageSite(' . $site['id'] . ')">Manage</button> ';
                $output .= '<button class="btn btn-xs btn-warning" onclick="updateSite(' . $site['id'] . ')">Update</button>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            
            $output .= '</tbody></table>';
            $output .= '</div>';
        }
        
        $output .= '</div></div>';
        $output .= '</div>';
        
        // TODO: Add JavaScript for AJAX operations
        $output .= $this->getJavaScript();
        
        return $output;
    }

    /**
     * Manage clients with WordPress
     * 
     * @return string HTML output
     */
    private function manageClients()
    {
        // TODO: Implement client management interface
        $output = '<div class="speedwp-admin-clients">';
        $output .= '<h2>Client WordPress Management</h2>';
        $output .= '<p><em>Client management interface coming soon...</em></p>';
        $output .= '<a href="?m=speedwp" class="btn btn-default">Back to Dashboard</a>';
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Module settings
     * 
     * @return string HTML output
     */
    private function settings()
    {
        // TODO: Implement settings interface
        $output = '<div class="speedwp-admin-settings">';
        $output .= '<h2>SpeedWP Settings</h2>';
        $output .= '<p><em>Settings interface coming soon...</em></p>';
        $output .= '<p>Use the main addon configuration to adjust cPanel settings and other options.</p>';
        $output .= '<a href="?m=speedwp" class="btn btn-default">Back to Dashboard</a>';
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Tools and utilities
     * 
     * @return string HTML output
     */
    private function tools()
    {
        // TODO: Implement tools interface
        $output = '<div class="speedwp-admin-tools">';
        $output .= '<h2>SpeedWP Tools</h2>';
        $output .= '<p><em>Tools and utilities coming soon...</em></p>';
        $output .= '<a href="?m=speedwp" class="btn btn-default">Back to Dashboard</a>';
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Get overview statistics
     * 
     * @return array Statistics data
     */
    private function getOverviewStats()
    {
        try {
            $stats = [
                'total_sites' => 0,
                'active_sites' => 0,
                'updates_available' => 0,
                'total_clients' => 0,
                'total_plugins' => 0,
                'total_themes' => 0,
                'total_backups' => 0,
                'disk_usage' => 0
            ];
            
            // Get total sites
            $query = "SELECT COUNT(*) as count FROM mod_speedwp_sites";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_sites'] = $result['count'] ?? 0;
            
            // Get active sites
            $query = "SELECT COUNT(*) as count FROM mod_speedwp_sites WHERE status = 'active'";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['active_sites'] = $result['count'] ?? 0;
            
            // Get unique clients with WordPress
            $query = "SELECT COUNT(DISTINCT client_id) as count FROM mod_speedwp_sites WHERE status != 'inactive'";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_clients'] = $result['count'] ?? 0;
            
            // Get total plugins
            $query = "SELECT COUNT(*) as count FROM mod_speedwp_plugins";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_plugins'] = $result['count'] ?? 0;
            
            // Get total themes
            $query = "SELECT COUNT(*) as count FROM mod_speedwp_themes";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_themes'] = $result['count'] ?? 0;
            
            // Get total backups
            $query = "SELECT COUNT(*) as count FROM mod_speedwp_backups WHERE status = 'completed'";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['total_backups'] = $result['count'] ?? 0;
            
            // Get total disk usage
            $query = "SELECT SUM(disk_usage) as total FROM mod_speedwp_sites WHERE status = 'active'";
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['disk_usage'] = $result['total'] ?? 0;
            
            // TODO: Calculate updates available by checking WordPress versions
            $stats['updates_available'] = 0;
            
            return $stats;
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error getting stats: " . $e->getMessage());
            return [
                'total_sites' => 0,
                'active_sites' => 0,
                'updates_available' => 0,
                'total_clients' => 0,
                'total_plugins' => 0,
                'total_themes' => 0,
                'total_backups' => 0,
                'disk_usage' => 0
            ];
        }
    }

    /**
     * Get recent activity
     * 
     * @return array Recent activity data
     */
    private function getRecentActivity()
    {
        try {
            $query = "SELECT l.*, s.domain, s.wp_path, 
                            CONCAT(c.firstname, ' ', c.lastname) as client_name
                     FROM mod_speedwp_logs l
                     LEFT JOIN mod_speedwp_sites s ON l.site_id = s.id
                     LEFT JOIN tblclients c ON l.client_id = c.id
                     ORDER BY l.created_at DESC
                     LIMIT 20";
            
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error getting recent activity: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Get all WordPress sites
     * 
     * @return array Sites data
     */
    private function getAllWordPressSites()
    {
        try {
            // TODO: Join with client data for better display
            $query = "SELECT s.*, c.firstname, c.lastname,
                            CONCAT(c.firstname, ' ', c.lastname) as client_name
                     FROM mod_speedwp_sites s
                     LEFT JOIN tblclients c ON s.client_id = c.id
                     ORDER BY s.domain ASC
                     LIMIT 100"; // TODO: Add pagination
            
            $stmt = Capsule::connection()->getPdo()->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            logActivity("SpeedWP Error getting all sites: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get CSS class for status
     * 
     * @param string $status Site status
     * @return string CSS class
     */
    private function getStatusClass($status)
    {
        switch ($status) {
            case 'active':
                return 'success';
            case 'suspended':
                return 'warning';
            case 'inactive':
                return 'danger';
            default:
                return 'default';
        }
    }

    /**
     * Get JavaScript for admin interface
     * 
     * @return string JavaScript code
     */
    private function getJavaScript()
    {
        $output = '<script>';
        $output .= 'function scanAllSites() {';
        $output .= '  if (confirm("This will scan all hosting accounts for WordPress installations. Continue?")) {';
        $output .= '    // TODO: Implement AJAX call for bulk scanning';
        $output .= '    alert("Bulk scanning feature coming soon!");';
        $output .= '  }';
        $output .= '}';
        
        $output .= 'function manageSite(siteId) {';
        $output .= '  // TODO: Implement site management interface';
        $output .= '  alert("Site management interface coming soon!");';
        $output .= '}';
        
        $output .= 'function updateSite(siteId) {';
        $output .= '  if (confirm("Update WordPress for this site?")) {';
        $output .= '    // TODO: Implement AJAX call for site update';
        $output .= '    alert("Update feature coming soon!");';
        $output .= '  }';
        $output .= '}';
        $output .= '</script>';
        
        return $output;
    }
}
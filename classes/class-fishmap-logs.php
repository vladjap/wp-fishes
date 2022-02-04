<?php
/**
 * Class for Fishmap logs.
 *
 * @package Fishmap_Logs/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/class-fishmap-db.php';
/**
 * Class Fishmap_Shortcode
 */
class Fishmap_Logs {

    const LOGS_RESULT_LIMIT = 3;
    const LOGS_PAGE_SLUG = 'fish-logs';

    public function init() {
        $this->createLogsMainPage();
    }
    private function createLogsMainPage() {
        $totalLogs = Fishmap_DB::getNumberOfLogs();
        $logs = null;

        $page = $_GET['logs-page'] ? intval($_GET['logs-page']) : 1;
        $nextPage = $page + 1;
        $previousPage = null;

        if ($page * self::LOGS_RESULT_LIMIT >= $totalLogs) {
            $nextPage = null;
        }
        if ($page > 1) {
            $previousPage = $page - 1;
        }

        if ($totalLogs > 0) {
            $logs = Fishmap_DB::getLogs($page, self::LOGS_RESULT_LIMIT);
        }
        $LogsTable = $this->createLogsTable($logs);
        $siteUrl = get_site_url();
        ?>
        <h1>Logs page</h1>
        <div>total (<?php echo $totalLogs ?>)</div>
        <div class="fishmap-logs-table-wrapper">
            <?php echo $LogsTable ?>
        </div>
        <div class="fishmap-logs-pagination-wrapper">
            <?php if ($previousPage): ?>
            <a href="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>&logs-page=<?php echo $previousPage; ?>">previous</a>
            <?php endif; ?>
            <span class="fishmap-logs-pagination-current-page-indicator"><?php echo $page ?></span>
            <?php if ($nextPage): ?>
            <a href="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>&logs-page=<?php echo $nextPage; ?>">next</a>
            <?php endif; ?>
        </div>
        <?php
    }

    private function createLogsTable($logs) {

        $tableRowsHTML = '';
        if($logs) {
            foreach ($logs as $log) {
                $tableRowsHTML .= "
                <tr>
                    <td>$log->fish_name</td>
                    <td>$log->second_fish_name</td>
                    <td>$log->third_fish_name</td>
                    <td>$log->tank_size</td>
                    <td>$log->created</td>
                </tr>
                ";

            }
        }

        return "
        <table id='relations-table-search-table' class='wp-list-table widefat striped fishmap-admin-rules-table'>
            <thead>
            <tr>
                <th>Fish name</th>
                <th>Second fish name</th>
                <th>Third fish name</th>
                <th>Tank size</th>
                <th>Search date</th>
            </tr>
            </thead>
            <tbody>
                $tableRowsHTML
            </tbody>
        </table>
        ";;
    }
}
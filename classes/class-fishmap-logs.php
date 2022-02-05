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

    const LOGS_RESULT_LIMIT = 10;
    const LOGS_PAGE_SLUG = 'fish-logs';

    public function init() {
        $this->createLogsMainPage();
    }
    private function createLogsMainPage() {

        $logs = null;

        $page = $_GET['logs-page'] ? intval($_GET['logs-page']) : 1;
        $nextPage = $page + 1;
        $previousPage = null;

        $filters = null;
        $isFilterActive = isset($_POST['fishmap-filters-submit']);
        if($isFilterActive) {
            $dateFrom = isset($_POST['date_from']) ? $_POST['date_from'] : null;
            $dateTo = isset($_POST['date_to']) ? $_POST['date_to'] : null;
            $tankSize = isset($_POST['tank-size-condition-value']) ? $_POST['tank-size-condition-value'] : null;
            $tankSizeCondition = isset($_POST['tank-size-condition-value']) ? $_POST['tank-size-filter-option'] : null;

            if($dateFrom) {
                $dateFrom = str_replace('T', ' ', $dateFrom) . ':00';
            }
            if($dateTo) {
                $dateTo = str_replace('T', ' ', $dateTo) . ':00';
            }

            $allFilters = [
                'fish' => $_POST['fish'],
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'tank_size' => $tankSize,
                'tank_size_condition' => $tankSize ? $tankSizeCondition : null,
            ];

            $filters = [];
            foreach ($allFilters as $key => $filter) {
                if($filter) {
                    $filters[$key] = $filter;
                }

            }

        }

        $totalLogs = Fishmap_DB::getNumberOfLogs($filters);
        if ($page * self::LOGS_RESULT_LIMIT >= $totalLogs) {
            $nextPage = null;
        }
        if ($page > 1) {
            $previousPage = $page - 1;
        }

        if ($totalLogs > 0) {

            $logs = Fishmap_DB::getLogs($page, self::LOGS_RESULT_LIMIT, $filters);
        }


        $LogsTable = $this->createLogsTable($logs);
        $siteUrl = get_site_url();


        ?>
        <h1>Logs page</h1>
        <?php $this->createFiltersForm($siteUrl, $filters); ?>

        <div>total (<?php echo $totalLogs ?>)</div>
        <div class="fishmap-logs-table-wrapper">
            <?php echo $LogsTable ?>
        </div>
        <div class="fishmap-logs-pagination-wrapper">

            <?php if ($isFilterActive): ?>
                <?php $this->createFilterPagination($siteUrl, $filters, $nextPage, $previousPage, $page); ?>
            <?php else: ?>
                <?php if ($previousPage): ?>
                    <a class="fishmap-logs-pagination-link" href="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>&logs-page=<?php echo $previousPage; ?>">previous</a>
                <?php endif; ?>
                <span class="fishmap-logs-pagination-current-page-indicator"><?php echo $page ?></span>
                <?php if ($nextPage): ?>
                    <a class="fishmap-logs-pagination-link" href="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>&logs-page=<?php echo $nextPage; ?>">next</a>
                <?php endif; ?>
            <?php endif; ?>

        </div>
        <?php
    }

    private function createFiltersForm($siteUrl, $filters) {
        $allFishes = Fishmap_DB::getAllFishes();
        $dateFrom = '';
        $dateTo = '';
        if (isset($filters['date_from'])) {
            $dateFrom = str_replace(' ', 'T', $filters['date_from']);
            $dateFrom = substr($dateFrom, 0, -3);
        }
        if (isset($filters['date_to'])) {
            $dateTo = str_replace(' ', 'T', $filters['date_to']);
            $dateTo = substr($dateTo, 0, -3);

        }

        ?>
        <form class="fishmap-filters-form" method="post" action="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>">
            <div>
                <label class="fishmap-label-settings">
                    <span>Select fish:</span>
                    <select class='fishmap-filter-fish-select' name='fish'>
                        <option value="">Select fish</option>
                        <?php foreach ($allFishes as $print): ?>
                            <option <?php echo $print->fish_id === $filters['fish'] ? 'selected' : '' ?> value='<?php echo $print->fish_id; ?>'><?php echo $print->name; ?></option>
                        <?php endforeach; ?>
                    </select class='fishmap-filter-fish-select'>
                </label>
            </div>

            <div style="margin-top: 20px;">
                <label>
                    <span>Date from:</span>
                    <input type="datetime-local" name="date_from" value="<?php echo $dateFrom; ?>" />
                </label>
                <label>
                    <span>Date to:</span>
                    <input type="datetime-local" name="date_to" value="<?php echo $dateTo; ?>"  />
                </label>
            </div>
            <div style="margin-top: 20px;">
                <span>Tank size </span>
                <select name="tank-size-filter-option">
                    <option <?php echo $filters['tank_size_condition'] === 'eq' ? 'selected' : '' ?> value="eq">Is equal to</option>
                    <option <?php echo $filters['tank_size_condition'] === 'gt' ? 'selected' : '' ?> value="gt">Is greater than</option>
                    <option <?php echo $filters['tank_size_condition'] === 'lt' ? 'selected' : '' ?> value="lt">Is less than </option>
                </select>
                <label>
                    <span> value </span>
                    <input type="text"name="tank-size-condition-value" value="<?php echo $filters['tank_size']; ?>">
                </label>
            </div>
            <input style="margin-top: 20px; margin-bottom: 20px" class="button action" type="submit" name="fishmap-filters-submit">
        </form>
        <?php
    }

    private function createFilterPagination($siteUrl, $filters, $nextPage, $previousPage, $page) {
        $fishFilterValue = '';
        if (isset($filters['fish'])) {
            $fishFilterValue = $filters['fish'];
        }
    ?>
        <?php if($previousPage): ?>
            <form class="fishmap-filters-form" method="post" action="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>&logs-page=<?php echo $previousPage; ?>">
                <input type="hidden" value="<?php echo $fishFilterValue ?>" name="fish">
                <input class="fishmap-pagination-button" type="submit" name="fishmap-filters-submit" value="previous">
            </form>
        <?php endif; ?>

        <span class="fishmap-logs-pagination-current-page-indicator"><?php echo $page; ?></span>
        <?php if($nextPage): ?>
            <form class="fishmap-filters-form" method="post" action="<?php echo $siteUrl; ?>/wp-admin/admin.php?page=<?php echo self::LOGS_PAGE_SLUG ?>&logs-page=<?php echo $nextPage; ?>">
                <input type="hidden" value="<?php echo $fishFilterValue ?>" name="fish">
                <input class="fishmap-pagination-button" type="submit" name="fishmap-filters-submit" value="next">
            </form>
        <?php endif; ?>

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
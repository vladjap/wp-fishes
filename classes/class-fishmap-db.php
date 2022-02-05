<?php
/**
 * Class for Fishmap DB actions.
 *
 * @package Fishmap_DB/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Fishmap_DB
 */
class Fishmap_DB
{

    const TABLE_NAME_FISHES = 'fishes';
    const TABLE_NAME_FISHES_RELATIONS = 'fishes_relations';
    const TABLE_NAME_FISHES_LOGS = 'fishes_logs';

    /**
     * Fishmap_DB constructor.
     */
    private function __construct()
    {
        register_activation_hook(__FILE__, array('Fishmap_DB', 'generateTables'));
    }

    public static function generateTables()
    {
        self::generateFishesTable();
        self::generateFishesRelationsTable();
        self::generateFishesLogsTable();
        self::alterFishesTable();
    }

    public static function generateFishesTable()
    {
        global $wpdb;
//        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "CREATE TABLE `$table_name` (
  `fish_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(220) DEFAULT NULL,
  `short_description` varchar(220) DEFAULT NULL,
  `minimum_volume` varchar(220) DEFAULT NULL,
  `largest_minimum_volume` varchar(220) DEFAULT NULL, 
  PRIMARY KEY(fish_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }
    }

    public static function alterFishesTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "
            ALTER TABLE `wp_fishes` 
            ADD `minimum_tank_volume` INT NULL AFTER `short_description`,
            ADD `required_tank_volume` INT NULL AFTER `minimum_tank_volume`,
            ADD `most_common_tank_volume` INT NULL AFTER `required_tank_volume`;
        ";
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $res = $wpdb->get_results("SHOW COLUMNS FROM `wp_fishes` LIKE 'minimum_tank_volume'");
            if (!$res) {
                $wpdb->get_results($sql);
            }
        }
    }

    public static function generateFishesRelationsTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES_RELATIONS;
        $sql = "CREATE TABLE `$table_name` (
  `fishes_relation_id` int(11) NOT NULL AUTO_INCREMENT,
  `fish_id` int,
  `second_fish_id` int,
  `status` varchar(220) DEFAULT NULL,
  PRIMARY KEY(fishes_relation_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }
    }

    public static function generateFishesLogsTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES_LOGS;
        $sql = "CREATE TABLE `$table_name` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `fish_id` int,
  `second_fish_id` int,
  `third_fish_id` int,
  `tank_size` int,
  `created` timestamp default now(),
  PRIMARY KEY(log_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }
    }

    public static function getAllFishes($orderBy = null)
    {
        global $wpdb;
        $orderByQueryPart = '';
        if ($orderBy) {
            $orderByQueryPart = ' ORDER BY ' . $orderBy;
        }
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;

        return $wpdb->get_results("SELECT * FROM $table_name" . $orderByQueryPart);
    }

    public static function insertNewFish($name, $shortDescription, $minimum_tank_volume, $required_tank_volume, $most_common_tank_volume)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("INSERT INTO $table_name(name,short_description,minimum_tank_volume,required_tank_volume,most_common_tank_volume) VALUES('$name','$shortDescription','$minimum_tank_volume','$required_tank_volume','$most_common_tank_volume')");
    }

    public static function updateFish($id, $name, $shortDescription, $minimum_tank_volume, $required_tank_volume, $most_common_tank_volume)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("UPDATE $table_name SET name='$name',short_description='$shortDescription',minimum_tank_volume='$minimum_tank_volume',required_tank_volume='$required_tank_volume',most_common_tank_volume='$most_common_tank_volume' WHERE fish_id='$id'");

    }

    public static function deleteFish($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("DELETE FROM $table_name WHERE fish_id='$id'");
    }

    public static function getRulesById($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "
            SELECT 
               wp_fishes.fish_id,
               name,
               (SELECT name FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_name,
               (SELECT minimum_tank_volume FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_minimum_tank_size,
               (SELECT fish_id FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_id,
               wp_fishes_relations.status,
               minimum_tank_volume
               FROM wp_fishes JOIN wp_fishes_relations ON wp_fishes.fish_id = wp_fishes_relations.fish_id WHERE wp_fishes.fish_id = " . $id;
        return $wpdb->get_results($sql);
    }

    public static function getRulesByBothIds($id, $secondId)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "SELECT name, (SELECT name FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_name, wp_fishes_relations.status FROM wp_fishes JOIN wp_fishes_relations ON wp_fishes.fish_id = wp_fishes_relations.fish_id WHERE wp_fishes.fish_id = " . $id . " AND wp_fishes_relations.second_fish_id = " . $secondId;
        return $wpdb->get_results($sql);
    }

    public static function getAllRules($orderBy = null)
    {
        global $wpdb;
        $orderByQueryPart = '';
        if ($orderBy) {
            $orderByQueryPart = ' ORDER BY ' . $orderBy;
        }
        $sqlForAllRelations = "
SELECT name, (SELECT name FROM wp_fishes WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_name, wp_fishes_relations.status FROM wp_fishes
JOIN wp_fishes_relations ON wp_fishes.fish_id = wp_fishes_relations.fish_id" . $orderByQueryPart;

        return $wpdb->get_results($sqlForAllRelations);
    }

    public static function getRelationByIds($id, $secondId)
    {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM wp_fishes_relations WHERE fish_id = ' . $id . ' AND second_fish_id = ' . $secondId);
    }

    public static function updateRelation($relationId, $rule)
    {
        global $wpdb;
        $sql = "UPDATE `wp_fishes_relations` SET `status` = '" . $rule . "' WHERE `wp_fishes_relations`.`fishes_relation_id` = " . $relationId;
        $wpdb->query($sql);
    }

    public static function insertRelation($fishId, $secondFishId, $rule)
    {
        global $wpdb;
        $sql = "INSERT INTO `wp_fishes_relations` ( `fish_id`, `second_fish_id`, `status`) VALUES ( '" . $fishId . "', '" . $secondFishId . "', '" . $rule . "')";
        $wpdb->query($sql);
    }

    public static function getFishById($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        return $wpdb->get_results("SELECT * FROM $table_name WHERE fish_id='$id'");
    }

    // LOGS FUNCTIONS START HERE:
    public static function getLogs($page, $limit, $filters)
    {
        if (!$page) {
            $page = 1;
        }
        if (!$limit) {
            $limit = 10;
        }

        global $wpdb;
        $fishFilterQueryPart = null;
        $dateRangeFilterQueryPart = null;
        $tankSizeQueryPart = null;
        $pagination = true;
        $paginationQueryPart = null;
        $offset = ($page  - 1) * $limit;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES_LOGS;
        $fish_table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;

        if ($filters) {
            $dateRangeFilterQueryPart = self::createQueryBetweenPart($filters);
            $tankSizeQueryPart = self::createTankSizeQueryPart($filters);
            $fishFilterQueryPart = self::createQuerySelectedFishPart($filters);
        }

        if ($pagination) {
            $paginationQueryPart = "LIMIT $limit OFFSET $offset";
        }

        $sql = "
        SELECT log_id, fish_id, second_fish_id, third_fish_id, tank_size, created, 
(SELECT name FROM $fish_table_name WHERE fish_id = wp_fishes_logs.fish_id) as fish_name,
(SELECT name FROM $fish_table_name WHERE fish_id = wp_fishes_logs.second_fish_id) as second_fish_name,
(SELECT name FROM $fish_table_name WHERE fish_id = wp_fishes_logs.third_fish_id) as third_fish_name
FROM $table_name 
$dateRangeFilterQueryPart
$tankSizeQueryPart
$fishFilterQueryPart
$paginationQueryPart
        ";

        if($filters) {
//            echo $sql;
//            die;
        }

        return $wpdb->get_results($sql);

    }

    public static function insertLog($fishId, $secondFishId, $thirdFishId, $tankSize)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES_LOGS;
        $sql = "INSERT INTO `" . $table_name . "` (`fish_id`, `second_fish_id`, `third_fish_id`, `tank_size`) VALUES ('" . $fishId . "', '" . $secondFishId . "', '" . $thirdFishId . "', '" . $tankSize . "');";
        $wpdb->query($sql);
    }

    private static function createQueryBetweenPart($filters) {
        $dateRangeFilterQueryPart = '';
        if ($filters['date_from'] && $filters['date_to']) {
            $dateFrom = $filters['date_from'];
            $dateTo = $filters['date_to'];
            $dateRangeFilterQueryPart = "
                    WHERE (created BETWEEN '$dateFrom' AND '$dateTo')
                "; // format: 2010-01-30 14:15:55
        }
        return $dateRangeFilterQueryPart;
    }

    private static function createTankSizeQueryPart($filters) {
        $tankSizeQueryPart = '';
        if ($filters['tank_size'] && $filters['tank_size_condition']) {
            $whereOrAnd = isset($filters['date_from']) && isset($filters['date_to']) ? 'AND' : 'WHERE';
            $tankSize = $filters['tank_size'];
            $condition = $filters['tank_size_condition'];
            if ($condition === 'eq') {
                $tankSizeQueryPart = "
                    $whereOrAnd tank_size=$tankSize
                ";
            } else if ($condition === 'lt') {
                $tankSizeQueryPart = "
                    $whereOrAnd tank_size<$tankSize AND tank_size>0 
                ";
            } else if ($condition === 'gt') {
                $tankSizeQueryPart = "
                    $whereOrAnd tank_size>$tankSize AND tank_size>0 
                ";
            }

        }

        return $tankSizeQueryPart;
    }

    private static function createQuerySelectedFishPart($filters) {
        $fishFilterQueryPart = '';
        $fishFilter = null;
        if ($filters['fish']) {
//            $whereOrOr = count($filters) === 1 ? 'WHERE' : 'AND';
            $fishFilter = $filters['fish'];
            $whereOrAnd = (isset($filters['date_from']) && isset($filters['date_to'])) || isset($filters['tank_size']) ? 'AND' : 'WHERE';
            $fishFilterQueryPart = "
$whereOrAnd (fish_id=$fishFilter
OR second_fish_id=$fishFilter
OR third_fish_id=$fishFilter)
            ";
        }

        return $fishFilterQueryPart;
    }

    public static function getNumberOfLogs($filters) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES_LOGS;
        $fishFilterQueryPart = null;
        $dateRangeFilterQueryPart = null;
        $tankSizeQueryPart = null;
        if ($filters) {
            $dateRangeFilterQueryPart = self::createQueryBetweenPart($filters);
            $tankSizeQueryPart = self::createTankSizeQueryPart($filters);
            $fishFilterQueryPart = self::createQuerySelectedFishPart($filters);
        }

        $sql = "SELECT COUNT(log_id) as length FROM $table_name $dateRangeFilterQueryPart $tankSizeQueryPart $fishFilterQueryPart";

        $res = $wpdb->get_results($sql);
        if (!$res || count($res) <= 0) {
            return 0;
        }
        return intval($res[0]->length);
    }
    public static function getFishIdByFishName($name) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "SELECT fish_id FROM $table_name WHERE name='$name'";
        $res = $wpdb->get_results($sql);
        if (!$res || count($res) <= 0) {
            return null;
        }
        return $res[0]->fish_id;
    }

    public static function truncateFishesTable() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "TRUNCATE TABLE $table_name";
        $wpdb->query($sql);
    }
    public static function truncateRelationsTable() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES_RELATIONS;
        $sql = "TRUNCATE TABLE $table_name";
        $wpdb->query($sql);
    }

    public static function setTankSizesForFishByName($name, $minTankVolume, $requiredTankVolume, $mostCommonTankVolume) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("UPDATE `$table_name` SET `minimum_tank_volume` = '$minTankVolume', `required_tank_volume` = '$requiredTankVolume', `most_common_tank_volume` = '$mostCommonTankVolume' WHERE `wp_fishes`.`name` = '$name'");

    }

}
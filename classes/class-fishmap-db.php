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
class Fishmap_DB {

    const TABLE_NAME_FISHES = 'fishes';
    const TABLE_NAME_FISHES_RELATIONS = 'fishes_relations';

    /**
     * Fishmap_DB constructor.
     */
    private function __construct() {
        register_activation_hook( __FILE__, array( 'Fishmap_DB', 'generateTables' ) );
    }

    public static function generateTables() {
        self::generateFishesTable();
        self::generateFishesRelationsTable();
    }

    public static function generateFishesTable() {
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

    public static function generateFishesRelationsTable() {
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

    public static function getAllFishes() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;

        return $wpdb->get_results("SELECT * FROM $table_name");
    }

    public static function insertNewFish($name, $shortDescription, $minimum_volume, $largest_minimum_volume) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("INSERT INTO $table_name(name,short_description,minimum_volume,largest_minimum_volume) VALUES('$name','$shortDescription','$minimum_volume','$largest_minimum_volume')");
    }
    public static function updateFish($id, $name, $shortDescription, $minimum_volume, $largest_minimum_volume) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("UPDATE $table_name SET name='$name',short_description='$shortDescription',minimum_volume='$minimum_volume',largest_minimum_volume='$largest_minimum_volume' WHERE fish_id='$id'");

    }

    public static function deleteFish($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $wpdb->query("DELETE FROM $table_name WHERE fish_id='$id'");
    }

    public static function getRulesById($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "SELECT wp_fishes.fish_id, name, (SELECT name FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_name, (SELECT fish_id FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_id, wp_fishes_relations.status FROM wp_fishes JOIN wp_fishes_relations ON wp_fishes.fish_id = wp_fishes_relations.fish_id WHERE wp_fishes.fish_id = ". $id;
        return $wpdb->get_results($sql);
    }
    public static function getRulesByBothIds($id, $secondId) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        $sql = "SELECT name, (SELECT name FROM $table_name WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_name, wp_fishes_relations.status FROM wp_fishes JOIN wp_fishes_relations ON wp_fishes.fish_id = wp_fishes_relations.fish_id WHERE wp_fishes.fish_id = ". $id . " AND wp_fishes_relations.second_fish_id = " . $secondId;
        return $wpdb->get_results($sql);
    }

    public static function getAllRules() {
        global $wpdb;
        $sqlForAllRelations = "
SELECT name, (SELECT name FROM wp_fishes WHERE fish_id = wp_fishes_relations.second_fish_id) as second_fish_name, wp_fishes_relations.status FROM wp_fishes
JOIN wp_fishes_relations ON wp_fishes.fish_id = wp_fishes_relations.fish_id";

        return $wpdb->get_results($sqlForAllRelations);
    }

    public static function getRelationByIds($id, $secondId) {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM wp_fishes_relations WHERE fish_id = ' . $id . ' AND second_fish_id = ' . $secondId);
    }

    public static function updateRelation($relationId, $rule) {
        global $wpdb;
        $sql = "UPDATE `wp_fishes_relations` SET `status` = '" . $rule . "' WHERE `wp_fishes_relations`.`fishes_relation_id` = " . $relationId;
        $wpdb->query($sql);
    }
    public static function insertRelation($fishId, $secondFishId, $rule) {
        global $wpdb;
        $sql = "INSERT INTO `wp_fishes_relations` ( `fish_id`, `second_fish_id`, `status`) VALUES ( '" . $fishId . "', '" . $secondFishId . "', '" . $rule . "')";
        $wpdb->query($sql);
    }

    public static function getFishById($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FISHES;
        return $wpdb->get_results("SELECT * FROM $table_name WHERE fish_id='$id'");
    }
}
<?php
/**
 * Class for Fishmap admin section.
 *
 * @package Fishmap/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/class-fishmap-db.php';

/**
 * Class Fishmap_Admin
 */
class Fishmap_Admin {

    /**
     * Fishmap_Admin constructor.
     */
    public function __construct() {
        $this->setHooks();
    }

    private function setHooks() {
        add_action('admin_menu', [$this, 'addAdminPageContent']);

    }

    public function addAdminPageContent() {
        add_menu_page('Fish map', 'Fish map', 'manage_options' ,__FILE__, [$this, 'fishesAdminPage'], 'dashicons-palmtree');
        add_submenu_page( __FILE__, 'Fish relations', 'Fish relations', 'manage_options', 'fish-relations', [$this, 'fishRelationsPage']);
        add_submenu_page( __FILE__, 'Fish settings', 'Fish settings', 'manage_options', 'fish-settings', [$this, 'fishSettingsPage']);
    }

    public function fishSettingsPage() {
        if (isset($_POST['fish-tank-warning-message-submit'])) {
            update_option('fish_tank_warning_message', $_POST['fish-tank-warning-message']);
        }
        // TABLE
        if (isset($_POST['fishmap-table-header-text-compatible-submit'])) {
            update_option('fishmap_table_header_text_compatible', $_POST['fishmap-table-header-text-compatible']);
        }
        if (isset($_POST['fishmap-table-header-text-incompatible-submit'])) {
            update_option('fishmap_table_header_text_incompatible', $_POST['fishmap-table-header-text-incompatible']);
        }
        if (isset($_POST['fishmap-table-header-text-caution-submit'])) {
            update_option('fishmap_table_header_text_caution', $_POST['fishmap-table-header-text-caution']);
        }
        // SELECTED FISH
        if (isset($_POST['fishmap-selected-fish-min-tank-volume-label-submit'])) {
            update_option('fishmap_selected_fish_min_tank_volume', $_POST['fishmap-selected-fish-min-tank-volume-label']);
        }
        if (isset($_POST['fishmap-selected-fish-largest-required-min-tank-volume-label-submit'])) {
            update_option('fishmap_selected_fish_largest_required_min_tank_volume', $_POST['fishmap-selected-fish-largest-required-min-tank-volume-label']);
        }
        if (isset($_POST['fishmap-selected-fish-min-most-common-tank-volume-label-submit'])) {
            update_option('fishmap_selected_fish_min_most_common_tank_volume', $_POST['fishmap-selected-fish-min-most-common-tank-volume-label']);
        }

        ?>
            <h1>Fishmap plugin settings</h1>
        <?php
        $this->createFishTankWarningMessageForm();
        $this->createTableHeaderTextForms();
        $this->createSelectedFishLabelForms();
    }

    private function createFishTankWarningMessageForm() {
        $fishTankWarningMessageFromStore = get_option( 'fish_tank_warning_message' );
        if (!$fishTankWarningMessageFromStore) {
            update_option('fish_tank_warning_message', 'Warning for fish tank size');
        }
        ?>
        <div class="fishmap-fish-tank-warning-message-form-wrapper">
            <form class="fishmap-fish-tank-warning-message-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-settings-labels-label">Fish tank warning message:</span>
                    <input type="text" name="fish-tank-warning-message" value="<?php echo $fishTankWarningMessageFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fish-tank-warning-message-submit">
            </form>
        </div>
        <?php
    }
    private function createSelectedFishLabelForms() {
        $selectedFishMinTankVolumeLabelFromStore = get_option( 'fishmap_selected_fish_min_tank_volume' );
        if (!$selectedFishMinTankVolumeLabelFromStore) {
            update_option('fishmap_selected_fish_min_tank_volume', 'Smallest Minimum volume by any member:');
        }
        $selectedFishLargestRequiredMinTankVolumeLabelFromStore = get_option( 'fishmap_selected_fish_largest_required_min_tank_volume' );
        if (!$selectedFishLargestRequiredMinTankVolumeLabelFromStore) {
            update_option('fishmap_selected_fish_largest_required_min_tank_volume', 'Largest required minimum volume by any member:');
        }
        $selectedFishMinMostCommonTankVolumeLabelFromStore = get_option( 'fishmap_selected_fish_min_most_common_tank_volume' );
        if (!$selectedFishMinMostCommonTankVolumeLabelFromStore) {
            update_option('fishmap_selected_fish_min_most_common_tank_volume', 'Minimum volume most common for the group:');
        }
        ?>
        <h2>Selected fish labels:</h2>
        <div class="fishmap-selected-fish-labels-form-wrapper">
            <form class="fishmap-selected-fish-min-tank-volume-label-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-selected-fish-label">Smallest Minimum volume by any member</span>
                    <input type="text" name="fishmap-selected-fish-min-tank-volume-label" value="<?php echo $selectedFishMinTankVolumeLabelFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fishmap-selected-fish-min-tank-volume-label-submit">
            </form>
        </div>
        <div class="fishmap-selected-fish-labels-form-wrapper">
            <form class="fishmap-selected-fish-largest-required-min-tank-volume-label-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-selected-fish-label">Largest required minimum volume by any member</span>
                    <input type="text" name="fishmap-selected-fish-largest-required-min-tank-volume-label" value="<?php echo $selectedFishLargestRequiredMinTankVolumeLabelFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fishmap-selected-fish-largest-required-min-tank-volume-label-submit">
            </form>
        </div>
        <div class="fishmap-selected-fish-labels-form-wrapper">
            <form class="fishmap-selected-fish-min-most-common-tank-volume-label-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-selected-fish-label">Minimum volume most common for the group</span>
                    <input type="text" name="fishmap-selected-fish-min-most-common-tank-volume-label" value="<?php echo $selectedFishMinMostCommonTankVolumeLabelFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fishmap-selected-fish-min-most-common-tank-volume-label-submit">
            </form>
        </div>

        <?php
    }

    private function createTableHeaderTextForms() {
        $tableHeaderTextCompatibleFromStore = get_option( 'fishmap_table_header_text_compatible' );
        if (!$tableHeaderTextCompatibleFromStore) {
            update_option('fishmap_table_header_text_compatible', 'Compatible with');
        }
        $tableHeaderTextIncompatibleFromStore = get_option( 'fishmap_table_header_text_incompatible' );
        if (!$tableHeaderTextIncompatibleFromStore) {
            update_option('fishmap_table_header_text_incompatible', 'Incompatible with');
        }
        $tableHeaderTextCautionFromStore = get_option( 'fishmap_table_header_text_caution' );
        if (!$tableHeaderTextCautionFromStore) {
            update_option('fishmap_table_header_text_caution', 'Caution');
        }
        ?>
        <h2>Table header labels:</h2>
        <div class="fishmap-table-header-text-form-wrapper">
            <form class="fishmap-table-header-text-compatible-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-settings-labels-label">Label for compatible</span>
                    <input type="text" name="fishmap-table-header-text-compatible" value="<?php echo $tableHeaderTextCompatibleFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fishmap-table-header-text-compatible-submit">
            </form>
        </div>
        <div class="fishmap-table-header-text-form-wrapper">
            <form class="fishmap-table-header-text-incompatible-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-settings-labels-label">Label for incompatible</span>
                    <input type="text" name="fishmap-table-header-text-incompatible" value="<?php echo $tableHeaderTextIncompatibleFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fishmap-table-header-text-incompatible-submit">
            </form>
        </div>
        <div class="fishmap-table-header-text-form-wrapper">
            <form class="fishmap-table-header-text-caution-form" method="post" action="">
                <label class="fishmap-label-settings">
                    <span class="fishmap-settings-labels-label">Label for caution</span>
                    <input type="text" name="fishmap-table-header-text-caution" value="<?php echo $tableHeaderTextCautionFromStore ?>">
                </label>
                <input class="button action" type="submit" name="fishmap-table-header-text-caution-submit">
            </form>
        </div>
        <?php
    }

    public function fishesAdminPage() {
        $siteUrl = get_site_url();
        if (isset($_POST['newsubmit'])) {
            $name = $_POST['newname'];
            $shortDescription = $_POST['newshort_description'];
            $minimum_tank_volume = $_POST['newminimum_tank_volume'];
            $required_tank_volume = $_POST['newrequired_tank_volume'];
            $most_common_tank_volume = $_POST['newmost_common_tank_volume'];
            Fishmap_DB::insertNewFish($name, $shortDescription, $minimum_tank_volume, $required_tank_volume, $most_common_tank_volume);
            echo "<script>location.replace('admin.php?page=" . __FILE__ . "');</script>";
        }
        if (isset($_POST['uptsubmit'])) {
            $id = $_POST['uptid'];
            $name = $_POST['uptname'];
            $shortDescription = $_POST['uptnewshort_description'];
            $minimum_tank_volume = $_POST['uptnewminimum_tank_volume'];
            $required_tank_volume = $_POST['uptnewrequired_tank_volume'];
            $most_common_tank_volume = $_POST['uptnewmost_common_tank_volume'];
            Fishmap_DB::updateFish($id, $name, $shortDescription, $minimum_tank_volume, $required_tank_volume, $most_common_tank_volume);
            echo "<script>location.replace('admin.php?page=" . __FILE__ . "');</script>";
        }
        if (isset($_GET['del'])) {
            $del_id = $_GET['del'];
            Fishmap_DB::deleteFish($del_id);
            echo "<script>location.replace('admin.php?page=" . __FILE__ . "');</script>";
        }
        ?>
        <div class="wrap">
            <h2>Add new fish</h2>
            <form class="fishmap-admin-add-new-fish-form" action="" method="post">
                <div class='fishmap-admin-add-fish-form-description'>*(This is for adding new fish)</div>
                <div class="fishmap-add-new-fish-input-wrapper"><input type="text" id="newname" name="newname" placeholder="Name"></div>
                <div class="fishmap-add-new-fish-input-wrapper"><input type="text" id="newshort_description" name="newshort_description" placeholder="Short description"></div>
                <div class="fishmap-add-new-fish-input-wrapper"><input type="number" id="newminimum_tank_volume" name="newminimum_tank_volume" placeholder="Minimum tank volume"></div>
                <div class="fishmap-add-new-fish-input-wrapper"><input type="number" id="newrequired_tank_volume" name="newrequired_tank_volume" placeholder="Required tank volume"></div>
                <div class="fishmap-add-new-fish-input-wrapper"><input type="number" id="newmost_common_tank_volume" name="newmost_common_tank_volume" placeholder="Most common tank volume"></div>
                <div class="fishmap-add-new-fish-input-wrapper"><button class="button action" id="newsubmit" name="newsubmit" type="submit">Insert</button></div>
            </form>

            <br>
            <br>
            <?php
            if (isset($_GET['upt'])) {
                $upt_id = $_GET['upt'];
                $result = Fishmap_DB::getFishById($upt_id);
                foreach($result as $print) {
                    $name = $print->name;
                    $shortDescription = $print->short_description;
                    $minimum_tank_volume = $print->minimum_tank_volume;
                    $required_tank_volume = $print->required_tank_volume;
                    $most_common_tank_volume = $print->most_common_tank_volume;}
                echo "
        <h2>Update fish</h2>
        <div class='update-form-description'>*(Update selected fish)</div>
        <table class='wp-list-table widefat striped fishmap-admin-update-fish-form-table'>
          <thead>
            <tr>
              <th>Fish ID</th>
              <th>Name</th>
              <th>Short description</th>
              <th >Minimum tank volume</th>
              <th >Required tank volume</th>
              <th >Most common tank volume</th>
              <th >Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td >$print->fish_id <input type='hidden' id='uptid' name='uptid' value='$print->fish_id'></td>
                <td ><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                <td ><input type='text' id='uptnewshort_description' name='uptnewshort_description' value='$print->short_description'></td>
                <td ><input type='number' id='uptnewminimum_tank_volume' name='uptnewminimum_tank_volume' value='$print->minimum_tank_volume'></td>
                <td ><input type='number' id='uptnewrequired_tank_volume' name='uptnewrequired_tank_volume' value='$print->required_tank_volume'></td>
                <td ><input type='number' id='uptnewmost_common_tank_volume' name='uptnewmost_common_tank_volume' value='$print->most_common_tank_volume'></td>
                <td ><button id='uptsubmit' name='uptsubmit' type='submit' class='button action'>Update</button> <a href='admin.php?page=" . __FILE__ . "'><button class='action button' type='button'>Cancel</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
            }
            ?>
            <h2>Fish table</h2>
            <input id='fishmap-fish-table-search-input' type='text' onkeyup='searchHTMLTableNames(0, "fishmap-fish-table-search-input", "fishmap-fish-table")' placeholder='Search for name' />
            <table id="fishmap-fish-table" class="wp-list-table widefat striped">
                <thead>
                <tr>
<!--                    <th >Fish ID</th>-->
                    <th ><a href="<?php echo $siteUrl . "/wp-admin/admin.php?page=" . __FILE__ . "&orderBy=name" ?>">Name</a></th>
                    <th >Short description</th>
                    <th >Minimum tank volume</th>
                    <th >Required tank volume</th>
                    <th >Most common tank volume</th>
                    <th >Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $result = Fishmap_DB::getAllFishes(isset($_GET['orderBy']) ? $_GET['orderBy'] : 'name');
                foreach ($result as $print) {
                    echo "
              <tr>
<!--                 <td >$print->fish_id</td>-->
                <td >$print->name</td>
                <td >$print->short_description</td>
                <td >$print->minimum_tank_volume</td>
                <td >$print->required_tank_volume</td>
                <td >$print->most_common_tank_volume</td>
                <td ><a href='admin.php?page=" . __FILE__ . "&upt=$print->fish_id'><button class='action button' type='button'>Update</button></a> <a href='admin.php?page=" . __FILE__ . "&del=$print->fish_id'><button class='fishmap-delete-button' type='button'>Delete</button></a></td>
              </tr>
            ";
                }
                ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    public function fishRelationsPage() {
        $result = Fishmap_DB::getAllFishes();
        $result1 = Fishmap_DB::getAllRules($_GET['orderBy']);
        $siteUrl = get_site_url();
        $htmlFishRelations = '';

        foreach ($result1 as $print) {
            $htmlFishRelations .= "
              <tr>
                <td>$print->name</td>
                <td>$print->second_fish_name</td>
                <td>$print->status</td>
              </tr>
            ";
        }

        echo "<h2 class='fishmap-admin-add-rule-form-title'>Add rule</h2>";
        echo "<span class='fishmap-admin-add-rule-form-description'>*(if you choose existing combination it will be updated)</span>";
        echo '<form class="fishmap-admin-add-rule-form" action="" method="post">';
        echo "<select class='fishmap-rule-fish-select-input' name='fish1'>";
        foreach ($result as $print) {
            echo "<option value='$print->fish_id'>$print->name</option>";
        }
        echo "</select>";

        echo "<div class='fishmap-rule-fish-select-input-fish2'><select class='fishmap-rule-fish-select-input fishmap-rule-fish-select-input-fish2' name='fish2'>";
        foreach ($result as $print) {
            echo "<option value='$print->fish_id'>$print->name</option>";
        }
        echo "</select></div>";

        echo "<div class='fishmap-rule-fish-select-input-rule'><select class='fishmap-rule-fish-select-input' name='rule'>";
        echo "<option value='yes'>Yes</option>";
        echo "<option value='no'>No</option>";
        echo "<option value='caution'>Caution</option>";
        echo "</select></div>";


        echo "<button class='button action' id='set_new_relation' name='set_new_relation' type='submit'>Set rule</button>";

        echo "</form>";

        echo "
        <div class='fishmap-relations-search-wrapper'>
            <input id='relations-table-search-input' type='text' onkeyup='searchHTMLTableNames(0, \"relations-table-search-input\", \"relations-table-search-table\")' placeholder='Search for name' />
            <input id='relations-table-search-input-second' type='text' onkeyup='searchHTMLTableNames(1, \"relations-table-search-input-second\", \"relations-table-search-table\")' placeholder='Search for second names..' />
        </div>
        
        <table id='relations-table-search-table' class='wp-list-table widefat striped fishmap-admin-rules-table'>
            <thead>
            <tr>
                <th ><a href='$siteUrl/wp-admin/admin.php?page=fish-relations&orderBy=name'>Name</a></th>
                <th ><a href='$siteUrl/wp-admin/admin.php?page=fish-relations&orderBy=second_fish_name'>Second fish name</a></th>
                <th ><a href='$siteUrl/wp-admin/admin.php?page=fish-relations&orderBy=status'>Status</a></th>
            </tr>
            </thead>
            <tbody>
                $htmlFishRelations
            </tbody>
        </table>";

        if (isset($_POST['set_new_relation'])) {
            $result2 = Fishmap_DB::getRelationByIds($_POST['fish1'], $_POST['fish2']);
            $result2Reversed = Fishmap_DB::getRelationByIds($_POST['fish2'], $_POST['fish1']);
            if ($result2 && $result2Reversed) {
                Fishmap_DB::updateRelation($result2[0]->fishes_relation_id, $_POST['rule']);
                if ($_POST['fish2'] !== $_POST['fish1']) {
                    Fishmap_DB::updateRelation($result2Reversed[0]->fishes_relation_id, $_POST['rule']);
                }
            } else {
                Fishmap_DB::insertRelation($_POST['fish1'], $_POST['fish2'], $_POST['rule']);
                if ($_POST['fish2'] !== $_POST['fish1']) {
                    Fishmap_DB::insertRelation($_POST['fish2'], $_POST['fish1'], $_POST['rule']);
                }
            }
            echo "<script>location.replace('admin.php?page=fish-relations');</script>";

        }

    }


} new Fishmap_Admin();
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
    }

    public function fishesAdminPage() {
        if (isset($_POST['newsubmit'])) {
            $name = $_POST['newname'];
            $shortDescription = $_POST['newshort_description'];
            $minimum_volume = $_POST['newminimum_volume'];
            $largest_minimum_volume = $_POST['newlargest_minimum_volume'];
            Fishmap_DB::insertNewFish($name, $shortDescription, $minimum_volume, $largest_minimum_volume);
            echo "<script>location.replace('admin.php?page=" . __FILE__ . "');</script>";
        }
        if (isset($_POST['uptsubmit'])) {
            $id = $_POST['uptid'];
            $name = $_POST['uptname'];
            $shortDescription = $_POST['uptnewshort_description'];
            $minimum_volume = $_POST['uptnewminimum_volume'];
            $largest_minimum_volume = $_POST['uptnewlargest_minimum_volume'];
            Fishmap_DB::updateFish($id, $name, $shortDescription, $minimum_volume, $largest_minimum_volume);
            echo "<script>location.replace('admin.php?page=" . __FILE__ . "');</script>";
        }
        if (isset($_GET['del'])) {
            $del_id = $_GET['del'];
            Fishmap_DB::deleteFish($del_id);
            echo "<script>location.replace('admin.php?page=" . __FILE__ . "');</script>";
        }
        ?>
        <div class="wrap">
            <h2>Fish Operations</h2>
            <table class="wp-list-table widefat striped">
                <thead>
                <tr>
                    <th width="25%">Fish ID</th>
                    <th width="25%">Name</th>
                    <th width="25%">Short description</th>
                    <th width="25%">Minimum volume</th>
                    <th width="25%">Largest minimum volume</th>
                    <th width="25%">Actions</th>
                </tr>
                </thead>
                <tbody>
                <form action="" method="post">
                    <tr>
                        <td><input type="text" value="AUTO_GENERATED" disabled></td>
                        <td><input type="text" id="newname" name="newname"></td>
                        <td><input type="text" id="newshort_description" name="newshort_description"></td>
                        <td><input type="text" id="newminimum_volume" name="newminimum_volume"></td>
                        <td><input type="text" id="newlargest_minimum_volume" name="newlargest_minimum_volume"></td>
                        <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
                    </tr>
                </form>
                <?php
                $result = Fishmap_DB::getAllFishes();
                foreach ($result as $print) {
                    echo "
              <tr>
                <td width='25%'>$print->fish_id</td>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->short_description</td>
                <td width='25%'>$print->minimum_volume</td>
                <td width='25%'>$print->largest_minimum_volume</td>
                <td width='25%'><a href='admin.php?page=" . __FILE__ . "&upt=$print->fish_id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=" . __FILE__ . "&del=$print->fish_id'><button type='button'>DELETE</button></a></td>
              </tr>
            ";
                }
                ?>
                </tbody>
            </table>
            <br>
            <br>
            <?php
            if (isset($_GET['upt'])) {
                $upt_id = $_GET['upt'];
                $result = Fishmap_DB::getFishById($upt_id);
                foreach($result as $print) {
                    $name = $print->name;
                    $shortDescription = $print->short_description;
                    $minimum_volume = $print->minimum_volume;
                    $largest_minimum_volume = $print->largest_minimum_volume;
                }
                echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='25%'>Fish ID</th>
              <th width='25%'>Name</th>
              <th width='25%'>Short description</th>
              <th width='25%'>Minimum volume</th>
              <th width='25%'>Largest minimum volume</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='25%'>$print->fish_id <input type='hidden' id='uptid' name='uptid' value='$print->fish_id'></td>
                <td width='25%'><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                <td width='25%'><input type='text' id='uptnewshort_description' name='uptnewshort_description' value='$print->short_description'></td>
                <td width='25%'><input type='text' id='uptnewminimum_volume' name='uptnewminimum_volume' value='$print->minimum_volume'></td>
                <td width='25%'><input type='text' id='uptnewlargest_minimum_volume' name='uptnewlargest_minimum_volume' value='$print->largest_minimum_volume'></td>
                <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=" . __FILE__ . "'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
            }
            ?>
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
        echo "<option value='da'>Moze</option>";
        echo "<option value='ne'>Ne moze</option>";
        echo "<option value='maybe'>Mozda</option>";
        echo "</select></div>";


        echo "<button id='set_new_relation' name='set_new_relation' type='submit'>Set rule</button>";

        echo "</form>";

        echo "<table class='wp-list-table widefat striped fishmap-admin-rules-table'>
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
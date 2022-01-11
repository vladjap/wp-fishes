<?php
/*
Plugin Name: Fish map
Plugin URI: https://appup.xyz/
Description: Aquarium fish mapper
Version: 1.0.0
Author: Ajvan
Author URI: https://appup.xyz/
License: GPL2
*/
register_activation_hook( __FILE__, 'fishesTable');
function fishesTable() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'fishes';
    $sql = "CREATE TABLE `$table_name` (
  `fish_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(220) DEFAULT NULL,
  `short_description` varchar(220) DEFAULT NULL,
  PRIMARY KEY(fish_id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
add_action('admin_menu', 'addAdminPageContent');
function addAdminPageContent() {
    add_menu_page('Fish map', 'Fish map', 'manage_options' ,__FILE__, 'fishesAdminPage', 'dashicons-palmtree');
}
function fishesAdminPage() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'fishes';
    if (isset($_POST['newsubmit'])) {
        $name = $_POST['newname'];
        $shortDescription = $_POST['newshort_description'];
        $wpdb->query("INSERT INTO $table_name(name,short_description) VALUES('$name','$shortDescription')");
        echo "<script>location.replace('admin.php?page=fishmap/fishmap.php');</script>";
    }
    if (isset($_POST['uptsubmit'])) {
        $id = $_POST['uptid'];
        $name = $_POST['uptname'];
        $shortDescription = $_POST['uptnewshort_description'];
        $wpdb->query("UPDATE $table_name SET name='$name',short_description='$shortDescription' WHERE fish_id='$id'");
        echo "<script>location.replace('admin.php?page=fishmap/fishmap.php');</script>";
    }
    if (isset($_GET['del'])) {
        $del_id = $_GET['del'];
        $wpdb->query("DELETE FROM $table_name WHERE fish_id='$del_id'");
        echo "<script>location.replace('admin.php?page=fishmap/fishmap.php');</script>";
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
                <th width="25%">Actions</th>
            </tr>
            </thead>
            <tbody>
            <form action="" method="post">
                <tr>
                    <td><input type="text" value="AUTO_GENERATED" disabled></td>
                    <td><input type="text" id="newname" name="newname"></td>
                    <td><input type="text" id="newshort_description" name="newshort_description"></td>
                    <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
                </tr>
            </form>
            <?php
            $result = $wpdb->get_results("SELECT * FROM $table_name");
            foreach ($result as $print) {
                echo "
              <tr>
                <td width='25%'>$print->fish_id</td>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->short_description</td>
                <td width='25%'><a href='admin.php?page=fishmap/fishmap.php&upt=$print->fish_id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=fishmap/fishmap.php&del=$print->fish_id'><button type='button'>DELETE</button></a></td>
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
            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE fish_id='$upt_id'");
            foreach($result as $print) {
                $name = $print->name;
                $shortDescription = $print->short_description;
            }
            echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='25%'>Fish ID</th>
              <th width='25%'>Name</th>
              <th width='25%'>Short description</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='25%'>$print->fish_id <input type='hidden' id='uptid' name='uptid' value='$print->fish_id'></td>
                <td width='25%'><input type='text' id='uptname' name='uptname' value='$print->name'></td>
                <td width='25%'><input type='text' id='uptnewshort_description' name='uptnewshort_description' value='$print->short_description'></td>
                <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=fishmap/fishmap.php'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
        }
        ?>
    </div>
    <?php
}
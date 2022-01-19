<?php
/**
 * Class for Fishmap shortcode.
 *
 * @package Fishmap_Shortcode/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/class-fishmap-db.php';

/**
 * Class Fishmap_Shortcode
 */
class Fishmap_Shortcode {

    /**
     * Fishmap_Shortcode constructor.
     */
    public function __construct() {
        add_shortcode('fishes_map', [$this, 'fishesMapShortcodeCallback']);
    }

    private function handleSingleSelectSelected($selectValue) {
        $htmlFishRelationsTable = '';
        $selectedResult = Fishmap_DB::getRulesById($selectValue);
        $htmlFishRelations = '';
        foreach ($selectedResult as $print) {
            $htmlFishRelations .= "
              <tr>
                <td>$print->name</td>
                <td>$print->second_fish_name</td>
                <td>$print->status</td>
              </tr>
            ";
        }
        $htmlFishRelationsTable .= "
            <table>
                <thead>
                <tr>
                    <th >Name</th>
                    <th >Second fish name</th>
                    <th >Status</th>
                </tr>
                </thead>
                <tbody>
                    $htmlFishRelations
                </tbody>
            </table>";
        return $htmlFishRelationsTable;
    }

    private function handleBothSelectSelected($selectValue, $secondSelectValue) {

        $htmlFishRelationsTable = '';
        $selectedResult = Fishmap_DB::getRulesByBothIds($selectValue, $secondSelectValue);
        $htmlFishRelations = '';
        foreach ($selectedResult as $print) {
            $htmlFishRelations .= "
              <tr>
                <td>$print->name</td>
                <td>$print->second_fish_name</td>
                <td>$print->status</td>
              </tr>
            ";
        }
        $htmlFishRelationsTable .= "
            <table>
                <thead>
                <tr>
                    <th >Name</th>
                    <th >Second fish name</th>
                    <th >Status</th>
                </tr>
                </thead>
                <tbody>
                    $htmlFishRelations
                </tbody>
            </table>";
        return $htmlFishRelationsTable;
    }

    private function createTableRow($result) {
        $htmlFishes = '';
        foreach ($result as $print) {
            $htmlFishes .= "
              <tr>
                <td>$print->fish_id</td>
                <td>$print->name</td>
                <td>$print->short_description</td>
                <td>$print->minimum_volume</td>
                <td>$print->largest_minimum_volume</td>
              </tr>
            ";
        }
        return $htmlFishes;
    }

    private function generateForm($result) {
        $selectOptions = '';
        $secondSelectOptions = '<option value="none"></option>';

        foreach ($result as $print) {
            $selectOptions .= "<option value='$print->fish_id'>$print->name</option>";
            $secondSelectOptions .= "<option value='$print->fish_id'>$print->name</option>";
        }

        return "
            <form action='' method='post'>
                <select name='test-select'>
                    $selectOptions
                </select>
                <select name='second-select'>
                    $secondSelectOptions
                </select>
                <button type='submit' name='submit-f' value='submited'>submit</button>
            </form>
            ";
    }

    public function fishesMapShortcodeCallback() {
        $result = Fishmap_DB::getAllFishes();
        $htmlFishRelationsTable = '';

        if($_POST['test-select']  && ($_POST['second-select'] === 'none' || !$_POST['second-select'])) {
            $htmlFishRelationsTable = $this->handleSingleSelectSelected($_POST['test-select']);
        }
        if($_POST['test-select']  && $_POST['second-select'] !== 'none') {
            $htmlFishRelationsTable = $this->handleBothSelectSelected($_POST['test-select'], $_POST['second-select']);
        }

        $htmlFishes = $this->createTableRow($result);
        $htmlSelectForm =$this->generateForm($result);

        return "
        $htmlFishRelationsTable
        $htmlSelectForm
        <table>
            <thead>
            <tr>
                <th >Fish ID</th>
                <th >Name</th>
                <th >Short description</th>
                <th >Minimum volume</th>
                <th >Largest minimum volume</th>
            </tr>
            </thead>
            <tbody>
                $htmlFishes
            </tbody>
        </table>";
    }
} new Fishmap_Shortcode();
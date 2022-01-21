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

    private function createRuleTableTR($rule, $text) {
        return  "
          <tr class='fishmap-rule-table-tr fishmap-$rule-tr'>
            <td>$text</td>
          </tr>
        ";
    }

    private function createSelectedFishHtml($selectedFish) {
        if (!$selectedFish) {
            return '';
        }
        return "
        <div>
            <div>name: $selectedFish->name</div>
            <div>short_description: $selectedFish->short_description</div>
            <div>minimum_volume: $selectedFish->minimum_volume</div>
            <div>largest_minimum_volume: $selectedFish->largest_minimum_volume</div>
        </div>
        ";
    }

    private function createRuleTable($fishsTRTagsHtml, $thTitle, $rule) {
        return "
        <div class='fishmap-rule-table-wrapper fishmap-rule-table-wrapper-$rule'>
            <table class='fishmap-rule-tables-table'>
              <tr>
                <th>$thTitle</th>
              </tr>
              $fishsTRTagsHtml
            </table>
        </div>
        ";
    }

    private function handleSingleSelectSelected($selectValue) {
        $selectedFish = Fishmap_DB::getFishById($selectValue);
        if (!$selectedFish) {
            return "Selected fish not exists";
        }
        $selectedFish = $selectedFish[0];
        $selectedResult = Fishmap_DB::getRulesById($selectValue);
        $compatibleFishsTRTagsHtml = '';
        $incompatibleFishsTRTagsHtml = '';
        $maybeFishesTRTagsHtml = '';
        foreach ($selectedResult as $print) {
            if ($print->status === 'da') {
                $compatibleFishsTRTagsHtml .= $this->createRuleTableTR('compatible', $print->second_fish_name);
            } else if ($print->status === 'ne') {
                $incompatibleFishsTRTagsHtml .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
            } else if ($print->status === 'maybe') {
                $maybeFishesTRTagsHtml .= $this->createRuleTableTR('maybe', $print->second_fish_name);
            }
        }

        $selectedFishHtml = $this->createSelectedFishHtml($selectedFish);
        $compatibleRuleTable = $this->createRuleTable($compatibleFishsTRTagsHtml, 'Compatible with', 'compatible');
        $incompatibleRuleTable = $this->createRuleTable($incompatibleFishsTRTagsHtml, 'Incompatible with', 'incompatible');
        $maybeRuleTable = $this->createRuleTable($maybeFishesTRTagsHtml, 'Caution', 'maybe');

        return  "
            $selectedFishHtml
            <div class='fishmap-rule-tables-wrapper'>
                $compatibleRuleTable
                $incompatibleRuleTable
                $maybeRuleTable
            </div>
            <style>
                .fishmap-rule-tables-wrapper {
                    display: flex;
                }
                .fishmap-rule-table-tr  {
                    
                }
                .fishmap-rule-table-wrapper {
                    margin: 5px;
                }
            </style>
        ";
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
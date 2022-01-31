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

    private function createRuleTableTR($rule, $text, $tankWarning = false) {
        $tankWarningHTML = '';
        $tankWarningCSSClass = '';
        $warningMessageText = get_option( 'fish_tank_warning_message' );
        if ($tankWarning && $rule !== 'incompatible') {
            $tankWarningHTML = "
                <div class='fishmap-tooltip'><span class='fishmap-tooltip-i'>i</span>
                  <span class='fishmap-tooltiptext'>$warningMessageText</span>
                </div>
            ";
            $tankWarningCSSClass = 'fishmap-rule-table-tr-tank-warning';
        }
        return  "
          <tr class='fishmap-rule-table-tr fishmap-$rule-tr $tankWarningCSSClass'>
            <td>$text $tankWarningHTML</td>
          </tr>
        ";
    }

    private function createSelectedFishHtml($selectedFish, $tankWarning) {
        $warningMessageText = get_option( 'fish_tank_warning_message' );
        if (!$selectedFish) {
            return '';
        }
        $tankWarningHTML = '';
        if ($tankWarning) {
            $tankWarningHTML = "<div class='fishmap-selected-fish-tank-warning'>$warningMessageText</div>";
        }
        $shortDescHTML = '';
        if ($selectedFish->short_description) {
            $shortDescHTML = "<p class='fishmap-selected-fish-box-item'>$selectedFish->short_description</p>";
        }
        $minTankVolHTML = '';
        if ($selectedFish->minimum_tank_volume) {
            $minTankVolLabel = get_option('fishmap_selected_fish_min_tank_volume');
            $minTankVolHTML = "<div class='fishmap-selected-fish-box-item'>$minTankVolLabel $selectedFish->minimum_tank_volume gallons</div>";
        }
        $requiredTankVolHTML = '';
        if ($selectedFish->required_tank_volume) {
            $reqTankVolLabel = get_option('fishmap_selected_fish_largest_required_min_tank_volume');
            $requiredTankVolHTML = "<div class='fishmap-selected-fish-box-item'>$reqTankVolLabel $selectedFish->required_tank_volume gallons</div>";
        }
        $mostCommonTankVolHTML = '';
        if ($selectedFish->most_common_tank_volume) {
            $mostCommonMinTankVolLabel = get_option('fishmap_selected_fish_min_most_common_tank_volume');
            $mostCommonTankVolHTML = "<div class='fishmap-selected-fish-box-item'>$mostCommonMinTankVolLabel $selectedFish->most_common_tank_volume gallons</div>";
        }
        return "
        <div class='fishmap-selected-fish-box'>
            <h3>$selectedFish->name</h3>
            $shortDescHTML
            $minTankVolHTML
            $requiredTankVolHTML
            $mostCommonTankVolHTML
            $tankWarningHTML
        </div>
        ";
    }

    private function createRuleTable($fishsTRTagsHtml, $thTitle, $rule) {
        return "
        <div class='fishmap-rule-table-wrapper fishmap-rule-table-wrapper-$rule'>
            <table class='fishmap-rule-tables-table'>
              <thead>
                  <tr>
                    <th>$thTitle</th>
                  </tr>
              </thead>
              $fishsTRTagsHtml
            </table>
        </div>
        ";
    }

    private function handleSingleSelectSelected($selectValue, $tankSize) {
        $selectedFish = Fishmap_DB::getFishById($selectValue);
        if (!$selectedFish) {
            return "Selected fish not exists";
        }
        $selectedFish = $selectedFish[0];
        $selectedResult = Fishmap_DB::getRulesById($selectValue);
        $compatibleFishsTRTagsHtmlFirstFish = '';
        $incompatibleFishsTRTagsHtmlFirstFish = '';
        $maybeFishesTRTagsHtmlFirstFish = '';
        foreach ($selectedResult as $print) {
            $currentFishTankWarning = false;
            if ($print->second_fish_minimum_tank_size && $tankSize) {
                $currentFishTankWarning = intval($tankSize) < intval($print->second_fish_minimum_tank_size);
            }
            if ($print->status === 'yes') {
                $compatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('compatible', $print->second_fish_name, $currentFishTankWarning);
            } else if ($print->status === 'no') {
                $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
            } else if ($print->status === 'caution') {
                $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name, $currentFishTankWarning);
            }
        }

        $selectedFishHtml = $this->createSelectedFishHtml($selectedFish);
        $compatibleRuleTable = $this->createRuleTable($compatibleFishsTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_compatible'), 'compatible');
        $incompatibleRuleTable = $this->createRuleTable($incompatibleFishsTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_incompatible'), 'incompatible');
        $maybeRuleTable = $this->createRuleTable($maybeFishesTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_caution'), 'caution');

        return  "
            <div class='fishmap-selected-first-fish'>
                $selectedFishHtml
            </div>
            <div class='fishmap-rule-tables-wrapper'>
                $compatibleRuleTable
                $incompatibleRuleTable
                $maybeRuleTable
            </div>
        ";
    }

    private function getRelationRule($arrayToSearch, $secondFishId) {
        $rule = 'yes';
        for($i = 0; $i < count($arrayToSearch); $i++) {
            if ($arrayToSearch[$i]->second_fish_id === $secondFishId) {
                $rule = $arrayToSearch[$i]->status;
                break;
            }
        }
        return $rule;
    }
    private function groupRules($arrayOfRules = []) {
        $isNo = false;
        $isCaution = false;
        for($i = 0; $i < count($arrayOfRules); $i++) {
            if ($arrayOfRules[$i] === 'no') {
                $isNo = true;
                break;
            }
            if ($arrayOfRules[$i] === 'caution') {
                $isCaution = true;
            }
        }
        if ($isNo) {
            return 'no';
        }
        if ($isCaution) {
            return 'caution';
        }
        return 'yes';
    }
    private function isAllIncompatible($selectedFirstFishResultResult, $selectedSecondFishResultResult, $selectedThirdFishResultResult = null) {
        if ($selectedThirdFishResultResult) {
            for($j = 0; $j < count($selectedFirstFishResultResult); $j++ ) {
                for ($i = 0; $i < count($selectedSecondFishResultResult); $i++) {
                    if ($selectedFirstFishResultResult[$j]->second_fish_id === $selectedSecondFishResultResult[$i]->fish_id && $selectedFirstFishResultResult[$j]->status === 'no') {
                        return true;
                    }
                }
            }
            for($j = 0; $j < count($selectedFirstFishResultResult); $j++ ) {
                for ($i = 0; $i < count($selectedThirdFishResultResult); $i++) {
                    if ($selectedFirstFishResultResult[$j]->second_fish_id === $selectedThirdFishResultResult[$i]->fish_id && $selectedFirstFishResultResult[$j]->status === 'no') {
                        return true;
                    }
                }
            }
            for($j = 0; $j < count($selectedSecondFishResultResult); $j++ ) {
                for ($i = 0; $i < count($selectedThirdFishResultResult); $i++) {
                    if ($selectedSecondFishResultResult[$j]->second_fish_id === $selectedThirdFishResultResult[$i]->fish_id && $selectedSecondFishResultResult[$j]->status === 'no') {
                        return true;
                    }
                }
            }
        } else {
            for($j = 0; $j < count($selectedFirstFishResultResult); $j++ ) {
                for ($i = 0; $i < count($selectedSecondFishResultResult); $i++) {
                    if ($selectedFirstFishResultResult[$j]->second_fish_id === $selectedSecondFishResultResult[$i]->fish_id && $selectedFirstFishResultResult[$j]->status === 'no') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function handleBothSelectSelected($selectValue, $secondSelectValue, $tankSize) {
        $selectedFirstFish = Fishmap_DB::getFishById($selectValue);
        $selectedSecondFish = Fishmap_DB::getFishById($secondSelectValue);
        if (!$selectedFirstFish) {
            return "Selected fish not exists";
        }
        if (!$selectedSecondFish) {
            return "Selected second fish not exists";
        }

        $selectedFirstFish = $selectedFirstFish[0];
        $selectedSecondFish = $selectedSecondFish[0];
        $selectedFirstFishResultResult = Fishmap_DB::getRulesById($selectValue);
        $selectedSecondFishResultResult = Fishmap_DB::getRulesById($secondSelectValue);
        $compatibleFishsTRTagsHtmlFirstFish = '';
        $incompatibleFishsTRTagsHtmlFirstFish = '';
        $maybeFishesTRTagsHtmlFirstFish = '';

        // ako prva dva izabrana nisu kompatibilna sve ulazi u ne
        // ako su prva dva kompatibilna onda poredimo dalje
        // kada u pricu udje treca riba, poredi se sa prve 2 i jedno ne je ne.
        // Jedno mozda i jedno da su mozda.
        // Mozda i ne su ne
        $isAllIncompatible = $this->isAllIncompatible($selectedFirstFishResultResult, $selectedSecondFishResultResult);
        foreach ($selectedFirstFishResultResult as $print) {
            if ($print->status === 'no' || $isAllIncompatible) {
                $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
            }
            if ($print->status !== 'no' && !$isAllIncompatible) {
                $currentFishTankWarning = false;
                if ($print->second_fish_minimum_tank_size && $tankSize) {
                    $currentFishTankWarning = intval($tankSize) < intval($print->second_fish_minimum_tank_size);
                }
                for ($i = 0; $i < count($selectedSecondFishResultResult); $i++) {
                    if ($print->status === 'yes' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        if ($selectedSecondFishResultResult[$i]->status === 'yes') {
                            $compatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('compatible', $print->second_fish_name, $currentFishTankWarning);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'no') {
                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'caution') {
                            $maybeFishesTRTagsHtmlFirstFish .=$this->createRuleTableTR('caution', $print->second_fish_name, $currentFishTankWarning);
                        }
                    } else if ($print->status === 'no' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                        break;
                    } else if ($print->status === 'caution' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        if ($selectedSecondFishResultResult[$i]->status === 'yes') {
                            $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name, $currentFishTankWarning);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'no') {
                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'caution') {
                            $maybeFishesTRTagsHtmlFirstFish .=$this->createRuleTableTR('caution', $print->second_fish_name, $currentFishTankWarning);
                        }
                    }
                }
            }
        }

        $selectedFirstFishHtml = $this->createSelectedFishHtml($selectedFirstFish);
        $selectedSecondFishHtml = $this->createSelectedFishHtml($selectedSecondFish);
        $compatibleRuleTableFirstFish = $this->createRuleTable($compatibleFishsTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_compatible'), 'compatible');
        $incompatibleRuleTableFirstFish = $this->createRuleTable($incompatibleFishsTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_incompatible'), 'incompatible');
        $maybeRuleTableFirstFish = $this->createRuleTable($maybeFishesTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_caution'), 'caution');

        return  "
            <div class='fishmap-selected-fishes-wrapper'>
                <div class='fishmap-selected-first-fish'>
                    $selectedFirstFishHtml
                </div>
                <div class='fishmap-selected-second-fish'>
                    $selectedSecondFishHtml
                </div>
            </div>
            <div class='fishmap-rule-tables-wrapper'>
                $compatibleRuleTableFirstFish
                $incompatibleRuleTableFirstFish
                $maybeRuleTableFirstFish
            </div>
        ";
    }

    private function handle3SelectSelected($selectValue, $secondSelectValue, $thirdSelectValue, $tankSize) {
        $selectedFirstFish = Fishmap_DB::getFishById($selectValue);
        $selectedSecondFish = Fishmap_DB::getFishById($secondSelectValue);
        $selectedThirdFish = Fishmap_DB::getFishById($thirdSelectValue);

        if (!$selectedFirstFish) {
            return "Selected fish not exists";
        }
        if (!$selectedSecondFish) {
            return "Selected second fish not exists";
        }
        if (!$selectedThirdFish) {
            return "Selected third fish not exists";
        }

        $selectedFirstFish = $selectedFirstFish[0];
        $selectedSecondFish = $selectedSecondFish[0];
        $selectedThirdFish = $selectedThirdFish[0];
        $selectedFirstFishResultResult = Fishmap_DB::getRulesById($selectValue);
        $selectedSecondFishResultResult = Fishmap_DB::getRulesById($secondSelectValue);
        $selectedThirdFishResultResult = Fishmap_DB::getRulesById($thirdSelectValue);
        $compatibleFishsTRTagsHtmlFirstFish = '';
        $incompatibleFishsTRTagsHtmlFirstFish = '';
        $maybeFishesTRTagsHtmlFirstFish = '';

        $firstAndSecond = Fishmap_DB::getRelationByIds($selectValue, $secondSelectValue);
        if ($firstAndSecond) {
            $firstAndSecond = $firstAndSecond[0];
        }
        $firstAndThird = Fishmap_DB::getRelationByIds($selectValue, $thirdSelectValue);
        if ($firstAndThird) {
            $firstAndThird = $firstAndThird[0];
        }
        $secondAndThird = Fishmap_DB::getRelationByIds($secondSelectValue, $thirdSelectValue);
        if ($secondAndThird) {
            $secondAndThird = $secondAndThird[0];
        }
        $firstSelectedTankWarning = false;
        $secondSelectedTankWarning = false;
        $thirdSelectedTankWarning = false;

        if ($tankSize) {
            $firstSelectedTankWarning = $this->isTankSizeForFishTooSmall($tankSize, $selectedFirstFish);
            $secondSelectedTankWarning = $this->isTankSizeForFishTooSmall($tankSize, $selectedSecondFish);
            $thirdSelectedTankWarning = $this->isTankSizeForFishTooSmall($tankSize, $selectedThirdFish);
        }

        $ruleGroupedForSelected = $this->groupRules([$firstAndSecond->status, $firstAndThird->status, $secondAndThird->status]);

        $compArr = [];
        $cautionArr = [];
        foreach ($selectedFirstFishResultResult as $print) {
            if ($print->status === 'no' || $ruleGroupedForSelected === 'no') {
                $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
            }
            if ($print->status !== 'no' && $ruleGroupedForSelected !== 'no') {
                $currentFishTankWarning = false;
                if ($print->second_fish_minimum_tank_size && $tankSize) {
                    $currentFishTankWarning = intval($tankSize) < intval($print->second_fish_minimum_tank_size);
                }

                $firstWithSecond = $this->getRelationRule($selectedSecondFishResultResult, $print->second_fish_id);
                $firstWithThird = $this->getRelationRule($selectedThirdFishResultResult, $print->second_fish_id);
                $secondWithThird = $this->getRelationRule($selectedThirdFishResultResult, $secondSelectValue);

                $groupRule = $this->groupRules([
                    $print->status, $firstWithSecond, $firstWithThird, $secondWithThird
                ]);

                if ($groupRule === 'yes') {
                    $compArr[] = ['status' => 'compatible', 'fish_name' => $print->second_fish_name, 'tankWarning' => $currentFishTankWarning];
                }
                if ($groupRule === 'no') {
                    $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                }
                if ($groupRule === 'caution') {
                    $cautionArr[] = ['status' => 'caution', 'fish_name' => $print->second_fish_name, 'tankWarning' => $currentFishTankWarning];
                }
            }
        }
        usort($compArr, function ($item1, $item2) {
            if ($item1['tankWarning'] == $item2['tankWarning']) return 0;
            return $item1['tankWarning'] < $item2['tankWarning'] ? -1 : 1;
        });
        usort($cautionArr, function ($item1, $item2) {
            if ($item1['tankWarning'] == $item2['tankWarning']) return 0;
            return $item1['tankWarning'] < $item2['tankWarning'] ? -1 : 1;
        });
        for($i = 0; $i < count($compArr); $i++) {
            $compatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('compatible', $compArr[$i]['fish_name'], $compArr[$i]['tankWarning']);
        }
        for($i = 0; $i < count($cautionArr); $i++) {
            $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $cautionArr[$i]['fish_name'], $cautionArr[$i]['tankWarning']);
        }
        $selectedFirstFishHtml = $this->createSelectedFishHtml($selectedFirstFish, $firstSelectedTankWarning);
        $selectedSecondFishHtml = $this->createSelectedFishHtml($selectedSecondFish, $secondSelectedTankWarning);
        $selectedThirdFishHtml = $this->createSelectedFishHtml($selectedThirdFish, $thirdSelectedTankWarning);
        $compatibleRuleTableFirstFish = $this->createRuleTable($compatibleFishsTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_compatible'), 'compatible');
        $incompatibleRuleTableFirstFish = $this->createRuleTable($incompatibleFishsTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_incompatible'), 'incompatible');
        $maybeRuleTableFirstFish = $this->createRuleTable($maybeFishesTRTagsHtmlFirstFish, get_option('fishmap_table_header_text_caution'), 'caution');

        $tankSelectedHTML = '';
        if ($tankSize) {
//            $tankSelectedHTML = 'Selected tank size is: ' . $tankSize;
        }
        return  "
            <div class='fishmap-selected-fishes-wrapper'>
                <div class='fishmap-selected-first-fish'>
                    $selectedFirstFishHtml
                </div>
                <div class='fishmap-selected-second-fish'>
                    $selectedSecondFishHtml
                </div>
                <div class='fishmap-selected-second-fish'>
                    $selectedThirdFishHtml
                </div>
            </div>
            $tankSelectedHTML
            <div class='fishmap-rule-tables-wrapper'>
                $compatibleRuleTableFirstFish
                $incompatibleRuleTableFirstFish
                $maybeRuleTableFirstFish
            </div>
        ";
    }

    private function generateForm($result) {
        $selectOptions = '';
        $secondSelectOptions = '<option value="none"></option>';
        $thirdSelectOptions = '<option value="none"></option>';

        foreach ($result as $print) {
            $selectOptions .= "<option value='$print->fish_id'>$print->name</option>";
            $secondSelectOptions .= "<option value='$print->fish_id'>$print->name</option>";
            $thirdSelectOptions .= "<option value='$print->fish_id'>$print->name</option>";
        }

        return "
            <form action='' method='post'>
                <div class='fishmap-sc-selects-wrapper'>
                    <select name='test-select'>
                        $selectOptions
                    </select>
                    <select class='not-first-select' name='second-select'>
                        $secondSelectOptions
                    </select>
                    <select class='not-first-select' name='third-select'>
                        $thirdSelectOptions
                    </select>     
                </div>
                <div class='fishmap-sc-other-wrapper'>
                    <input class='fishmap-tank-size-input' type='number' name='tank-size' placeholder='Tank size'>
                    <button type='submit' name='submit-f' value='submited'>Submit</button>
                </div>
                
            </form>
            ";
    }

    private function isTankSizeForFishTooSmall($tankSize, $fish) {
        $tankSizeInt = intval($tankSize);
        if ($fish->minimum_tank_volume) {
            $firstMinimumTankVolume = intval($fish->minimum_tank_volume);
            if ($tankSizeInt < $firstMinimumTankVolume) {
                return true;
            }
        }
        return false;
    }

    public function fishesMapShortcodeCallback() {
        $result = Fishmap_DB::getAllFishes();
        $htmlFishRelationsTable = '';

        if($_POST['test-select']  && ($_POST['second-select'] === 'none' || !$_POST['second-select'])) {
            $htmlFishRelationsTable = $this->handleSingleSelectSelected($_POST['test-select'], $_POST['tank-size']);
        }
        if($_POST['test-select']  && $_POST['second-select'] !== 'none' && $_POST['third-select'] === 'none') {
            $htmlFishRelationsTable = $this->handleBothSelectSelected($_POST['test-select'], $_POST['second-select'], $_POST['tank-size']);
        }
        if($_POST['test-select']  && $_POST['second-select'] !== 'none' && $_POST['third-select'] !== 'none') {
            $htmlFishRelationsTable = $this->handle3SelectSelected($_POST['test-select'], $_POST['second-select'], $_POST['third-select'], $_POST['tank-size']);
        }

        $htmlSelectForm = $this->generateForm($result);

        return "
            $htmlSelectForm
            $htmlFishRelationsTable
       ";
    }
} new Fishmap_Shortcode();
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
        $compatibleFishsTRTagsHtmlFirstFish = '';
        $incompatibleFishsTRTagsHtmlFirstFish = '';
        $maybeFishesTRTagsHtmlFirstFish = '';
        foreach ($selectedResult as $print) {
            if ($print->status === 'yes') {
                $compatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('compatible', $print->second_fish_name);
            } else if ($print->status === 'no') {
                $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
            } else if ($print->status === 'caution') {
                $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
            }
        }

        $selectedFishHtml = $this->createSelectedFishHtml($selectedFish);
        $compatibleRuleTable = $this->createRuleTable($compatibleFishsTRTagsHtmlFirstFish, 'Compatible with', 'compatible');
        $incompatibleRuleTable = $this->createRuleTable($incompatibleFishsTRTagsHtmlFirstFish, 'Incompatible with', 'incompatible');
        $maybeRuleTable = $this->createRuleTable($maybeFishesTRTagsHtmlFirstFish, 'Caution', 'caution');

        return  "
            <div class='fishmap-selected-first-fish'>
                <h3>Selected fish</h3>
                $selectedFishHtml
            </div>
            <div class='fishmap-rule-tables-wrapper'>
                $compatibleRuleTable
                $incompatibleRuleTable
                $maybeRuleTable
            </div>
        ";
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

    private function handleBothSelectSelected($selectValue, $secondSelectValue) {
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
                for ($i = 0; $i < count($selectedSecondFishResultResult); $i++) {
                    if ($print->status === 'yes' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        if ($selectedSecondFishResultResult[$i]->status === 'yes') {
                            $compatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('compatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'no') {
                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'caution') {
                            $maybeFishesTRTagsHtmlFirstFish .=$this->createRuleTableTR('caution', $print->second_fish_name);
                        }
                    } else if ($print->status === 'no' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                        break;
                    } else if ($print->status === 'caution' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        if ($selectedSecondFishResultResult[$i]->status === 'yes') {
                            $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'no') {
                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'caution') {
                            $maybeFishesTRTagsHtmlFirstFish .=$this->createRuleTableTR('caution', $print->second_fish_name);
                        }
                    }
                }
            }
        }

        $selectedFirstFishHtml = $this->createSelectedFishHtml($selectedFirstFish);
        $selectedSecondFishHtml = $this->createSelectedFishHtml($selectedSecondFish);
        $compatibleRuleTableFirstFish = $this->createRuleTable($compatibleFishsTRTagsHtmlFirstFish, 'Compatible with', 'compatible');
        $incompatibleRuleTableFirstFish = $this->createRuleTable($incompatibleFishsTRTagsHtmlFirstFish, 'Incompatible with', 'incompatible');
        $maybeRuleTableFirstFish = $this->createRuleTable($maybeFishesTRTagsHtmlFirstFish, 'Caution', 'caution');

        return  "
            <div class='fishmap-selected-fishes-wrapper'>
                <div class='fishmap-selected-first-fish'>
                    <h3>First selected fish</h3>
                    $selectedFirstFishHtml
                </div>
                <div class='fishmap-selected-second-fish'>
                    <h3>Second selected fish</h3>
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

    private function handle3SelectSelected($selectValue, $secondSelectValue, $thirdSelectValue) {
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

        // ako prva dva izabrana nisu kompatibilna sve ulazi u ne
        // ako su prva dva kompatibilna onda poredimo dalje
        // kada u pricu udje treca riba, poredi se sa prve 2 i jedno ne je ne.
        // Jedno mozda i jedno da su mozda.
        // Mozda i ne su ne
        $isAllIncompatible = $this->isAllIncompatible($selectedFirstFishResultResult, $selectedSecondFishResultResult, $selectedThirdFishResultResult);
        foreach ($selectedFirstFishResultResult as $print) {
            if ($print->status === 'no' || $isAllIncompatible) {
                $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
            }
            if ($print->status !== 'no' && !$isAllIncompatible) {
                for ($i = 0; $i < count($selectedSecondFishResultResult); $i++) {
                    if ($print->status === 'yes' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) { // ovde proveravamo prvu i drugu
                        if ($selectedSecondFishResultResult[$i]->status === 'yes') { // yes yes
                            // sad ovde jos proveriti sa third fish - porediti i sa prvim i sa drugim.
                            $statusAfterCheckFirstAndThird = null; // ovde cemo da upisemo rezultat kombinacije prva - treca
                            // ovde krecemo da vrtimo treci da ga poredimo sa prvim
                            for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                if ($selectedThirdFishResultResult[$j]->second_fish_id === $print->second_fish_id) { // ovde proveravamo da li je to bas ta sto nam treba
                                    if ($selectedThirdFishResultResult[$j]->status === 'yes') { // yes yes yes - za prvu i trecu
                                        $statusAfterCheckFirstAndThird = 'yes';
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'no') { // yes yes no - za prvu i trecu
                                        $statusAfterCheckFirstAndThird = 'no';
                                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'caution') { // yes yes caution - za prvu i trecu
                                        $statusAfterCheckFirstAndThird = 'caution';
                                        break;
                                    }
                                }
                            }
                            $statusAfterCheckSecondAndThird = null; // ovde cemo da upisemo rezultat kombinacije druga - treca
                            // ovde krecemo da vrtimo treci da ga poredimo sa drugim, ali samo ako nije NO kombinacija prvog i treceg
                            if ($statusAfterCheckFirstAndThird === 'yes' || $statusAfterCheckFirstAndThird === 'caution') {
                                for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                    if ($selectedThirdFishResultResult[$j]->second_fish_id === $selectedSecondFishResultResult[$i]->fish_id) { // ovde proveravamo drugu i trecu
                                        if ($selectedThirdFishResultResult[$j]->status === 'yes') { // yes yes yes - za drugu i trecu
                                            $statusAfterCheckSecondAndThird = 'yes';
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'no') { // yes yes no - za drugu i trecu
                                            $statusAfterCheckSecondAndThird = 'no';
                                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'caution') { // yes yes caution - za drugu i trecu
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                    }
                                }
                            }
                            // Ovde je krajnja provera. Sada imamo informacije o svim kombinacijama:
                            // Prva i druga znamo da su YES, pa ispitujemo dalje:
                            if ($statusAfterCheckSecondAndThird !== 'no' && $statusAfterCheckFirstAndThird !== 'no') { // ako su oba statusa NO, vec smo ga dodali u tabelu ranije i ne trebamo sad nista
                                if ($statusAfterCheckSecondAndThird === 'yes' && $statusAfterCheckFirstAndThird === 'yes') { // YES YES YES - prvi i drugi YES, prvi i treci YES, drugi i treci YES
                                    $compatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('compatible', $print->second_fish_name);
                                }
                                if ($statusAfterCheckSecondAndThird === 'yes' && $statusAfterCheckFirstAndThird === 'caution') { // YES CAUTION YES - prvi i drugi YES, prvi i treci CAUTION, drugi i treci YES
                                    $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                                }
                                if ($statusAfterCheckSecondAndThird === 'caution' && $statusAfterCheckFirstAndThird === 'yes') { // YES YES CAUTION - prvi i drugi YES, prvi i treci YES, drugi i treci CAUTION
                                    $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                                }
                                if ($statusAfterCheckSecondAndThird === 'caution' && $statusAfterCheckFirstAndThird === 'caution') { // YES CAUTION CAUTION - prvi i drugi YES, prvi i treci CAUTION, drugi i treci CAUTION
                                    $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                                }

                            }
                            if ($statusAfterCheckSecondAndThird) {
                                break;
                            }
                        } else if ($selectedSecondFishResultResult[$i]->status === 'no') {
                            // Ako je ovde no, svakako je no i nema dalje
                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'caution') {
                            // ako je caution prvi i drugi, proveravamo dalje za third
                            $statusAfterCheckFirstAndThird = null; // ovde cemo da upisemo rezultat kombinacije prva - treca
                            for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                if ($selectedThirdFishResultResult[$j]->second_fish_id === $print->second_fish_id) { // ovde proveravamo da li je to bas ta sto nam treba
                                    if ($selectedThirdFishResultResult[$j]->status === 'yes') { // yes caution yes - za prvu i trecu
                                        $statusAfterCheckFirstAndThird = 'caution';
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'no') { // yes caution no - za prvu i trecu
                                        $statusAfterCheckFirstAndThird = 'no';
                                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'caution') { // yes caution caution - za prvu i trecu
                                        $statusAfterCheckFirstAndThird = 'caution';
                                    }
                                }
                            }
                            $statusAfterCheckSecondAndThird = null; // ovde cemo da upisemo rezultat kombinacije druga - treca
                            // ovde krecemo da vrtimo treci da ga poredimo sa drugim, ali samo ako je caution kombinacija prvog i treceg. No je resen iznad, a YES nemamo nikad jer je prvi i drugi CAUTION. Pa ce svaki YES od pre biti CAUTION
                            if ($statusAfterCheckFirstAndThird === 'caution'){
                                for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                    if ($selectedThirdFishResultResult[$j]->second_fish_id === $selectedSecondFishResultResult[$i]->fish_id) { // ovde proveravamo da li je to bas ta sto nam treba
                                        if ($selectedThirdFishResultResult[$j]->status === 'yes') { // yes caution yes - za drugu i trecu
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'no') { // yes caution no - za drugu i trecu
                                            $statusAfterCheckSecondAndThird = 'no';
                                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'caution') { // yes caution caution - za drugu i trecu
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($statusAfterCheckSecondAndThird !== 'no' && $statusAfterCheckFirstAndThird !== 'no') {
                                $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                            }
                            if ($statusAfterCheckSecondAndThird) {
                                break;
                            }
                            // CAUTION END
                            $maybeFishesTRTagsHtmlFirstFish .=$this->createRuleTableTR('caution', $print->second_fish_name);
                        }
                    } else if ($print->status === 'no' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        // ako je no to svakako vazi za trecu, nema sta da je gledamo uopste
                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                        break;
                    } else if ($print->status === 'caution' && $selectedSecondFishResultResult[$i]->second_fish_id === $print->second_fish_id) {
                        if ($selectedSecondFishResultResult[$i]->status === 'yes') {
                            // ako je caution i yes proveravamo sa prvom i drugom za trecu
                            // CAUTION YES START

                            $statusAfterCheckFirstAndThird = null;
                            for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                if ($selectedThirdFishResultResult[$j]->second_fish_id === $print->second_fish_id) { // ovde proveravamo prvu i trecu
                                    if ($selectedThirdFishResultResult[$j]->status === 'yes') { // caution yes yes
                                        $statusAfterCheckFirstAndThird = 'caution';
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'no') { // caution yes no
                                        $statusAfterCheckFirstAndThird = 'no';
                                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'caution') { // caution yes caution
                                        $statusAfterCheckFirstAndThird = 'caution';
                                    }
                                }
                            }
                            $statusAfterCheckSecondAndThird = null;
                            if ($statusAfterCheckFirstAndThird === 'caution'){
                                for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                    if ($selectedThirdFishResultResult[$j]->second_fish_id === $selectedSecondFishResultResult[$i]->fish_id) { // ovde proveravamo drugu i trecu
                                        if ($selectedThirdFishResultResult[$j]->status === 'yes') { // caution yes yes
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'no') { // caution yes no
                                            $statusAfterCheckSecondAndThird = 'no';
                                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'caution') { // caution yes caution
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($statusAfterCheckSecondAndThird !== 'no' && $statusAfterCheckFirstAndThird !== 'no') {
                                $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                            }
                            if ($statusAfterCheckSecondAndThird) {
                                break;
                            }
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'no') {
                            // no je no do kraja
                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                            break;
                        } else if ($selectedSecondFishResultResult[$i]->status === 'caution') {
                            $statusAfterCheckFirstAndThird = null;
                            for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                if ($selectedThirdFishResultResult[$j]->second_fish_id === $print->second_fish_id) { // ovde proveravamo prvu i trecu
                                    if ($selectedThirdFishResultResult[$j]->status === 'yes') { // caution caution yes
                                        $statusAfterCheckFirstAndThird = 'caution';
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'no') { // caution caution no
                                        $statusAfterCheckFirstAndThird = 'no';
                                        $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                        break;
                                    }
                                    if ($selectedThirdFishResultResult[$j]->status === 'caution') { // caution caution caution
                                        $statusAfterCheckFirstAndThird = 'caution';
                                    }
                                }
                            }
                            $statusAfterCheckSecondAndThird = null;
                            if ($statusAfterCheckFirstAndThird === 'caution'){
                                for ($j = 0; $j < count($selectedThirdFishResultResult); $j++) {
                                    if ($selectedThirdFishResultResult[$j]->second_fish_id === $selectedSecondFishResultResult[$i]->fish_id) { // ovde proveravamo drugu i trecu
                                        if ($selectedThirdFishResultResult[$j]->status === 'yes') { // caution caution yes
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'no') { // caution caution no
                                            $statusAfterCheckSecondAndThird = 'no';
                                            $incompatibleFishsTRTagsHtmlFirstFish .= $this->createRuleTableTR('incompatible', $print->second_fish_name);
                                            break;
                                        }
                                        if ($selectedThirdFishResultResult[$j]->status === 'caution') { // caution caution caution
                                            $statusAfterCheckSecondAndThird = 'caution';
                                            break;
                                        }
                                    }
                                }
                            }
                            if ($statusAfterCheckSecondAndThird !== 'no' && $statusAfterCheckFirstAndThird !== 'no') {
                                $maybeFishesTRTagsHtmlFirstFish .= $this->createRuleTableTR('caution', $print->second_fish_name);
                            }
                            if ($statusAfterCheckSecondAndThird) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        $selectedFirstFishHtml = $this->createSelectedFishHtml($selectedFirstFish);
        $selectedSecondFishHtml = $this->createSelectedFishHtml($selectedSecondFish);
        $selectedThirdFishHtml = $this->createSelectedFishHtml($selectedThirdFish);
        $compatibleRuleTableFirstFish = $this->createRuleTable($compatibleFishsTRTagsHtmlFirstFish, 'Compatible with', 'compatible');
        $incompatibleRuleTableFirstFish = $this->createRuleTable($incompatibleFishsTRTagsHtmlFirstFish, 'Incompatible with', 'incompatible');
        $maybeRuleTableFirstFish = $this->createRuleTable($maybeFishesTRTagsHtmlFirstFish, 'Caution', 'caution');

        return  "
            <div class='fishmap-selected-fishes-wrapper'>
                <div class='fishmap-selected-first-fish'>
                    <h3>First selected fish</h3>
                    $selectedFirstFishHtml
                </div>
                <div class='fishmap-selected-second-fish'>
                    <h3>Second selected fish</h3>
                    $selectedSecondFishHtml
                </div>
                <div class='fishmap-selected-second-fish'>
                    <h3>Third selected fish</h3>
                    $selectedThirdFishHtml
                </div>
            </div>
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
                <select name='test-select'>
                    $selectOptions
                </select>
                <select name='second-select'>
                    $secondSelectOptions
                </select>
                <select name='third-select'>
                    $thirdSelectOptions
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
        if($_POST['test-select']  && $_POST['second-select'] !== 'none' && $_POST['third-select'] === 'none') {
            $htmlFishRelationsTable = $this->handleBothSelectSelected($_POST['test-select'], $_POST['second-select']);
        }
        if($_POST['test-select']  && $_POST['second-select'] !== 'none' && $_POST['third-select'] !== 'none') {
            $htmlFishRelationsTable = $this->handle3SelectSelected($_POST['test-select'], $_POST['second-select'], $_POST['third-select']);
        }

        $htmlSelectForm =$this->generateForm($result);

        return "
            $htmlSelectForm
            $htmlFishRelationsTable
       ";
    }
} new Fishmap_Shortcode();
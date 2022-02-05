<?php
/**
 * Class for Fishmap import.
 *
 * @package Fishmap_Import/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
require_once __DIR__ . '/class-fishmap-db.php';
/**
 * Class Fishmap_Shortcode
 */
class Fishmap_Import {

    const FISH_COMPATIBILITY_STATUSES = [
        'C' => 'caution',
        'Y' => 'yes',
        'N' => 'no',
    ];
    public function init() {
        if(isset($_POST['submit-upload'])){
            $this->handleUploadFishForm();
        }

        if(isset($_POST['submit-tank-size-upload'])){
            $this->handleUploadTankSizeForm();
        }
        $this->createUploadFishForm();
        $this->createUploadTankSizeForm();
    }

    private function createUploadTankSizeForm() {
    ?>
        <h2>Import tank sizes</h2>
        <form method='post' action='' name='upload-csv-tank-size-form' enctype='multipart/form-data'>
            <table>
                <tr>
                    <td>Upload tank size file</td>
                    <td><input type='file' name='file'></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type='submit' name='submit-tank-size-upload' value='Submit'></td>
                </tr>
            </table>
        </form>
    <?php
    }

    private function handleUploadTankSizeForm() {
        if($_FILES['file']['name'] != ''){
            $uploadedFile = $_FILES['file'];
            $row = 1;
//            $rowFishNames = [];
            if (($handle = fopen($uploadedFile['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($row === 1) {
                        echo "<pre>";
                        print_r($data);
                        echo "</pre>";
                    } else {
                        echo "Imported: $data[0], $data[3], $data[5], $data[1]" . '<br>';
                        Fishmap_DB::setTankSizesForFishByName($data[0], $data[3], $data[5], $data[1]);
                    }
//                    $num = count($data);
                    $row++;

                }
            }
        }
    }

    private function handleUploadFishForm() {

        if($_FILES['file']['name'] != ''){
            $uploadedfile = $_FILES['file'];

            echo "TRUNCATE FISHES TABLE<br>";
            Fishmap_DB::truncateFishesTable();

            echo "START IMPORTING FISHES<br>";
            $this->handleUploadedFishFile($uploadedfile['tmp_name']);


            echo "TRUNCATE FISH RELATIONS TABLE<br>";
            Fishmap_DB::truncateRelationsTable();

            echo "START IMPORTING FISH RELATIONS<br>";
            $this->handleUploadedRelationsFile($uploadedfile['tmp_name']);
        }
    }

    private function handleUploadedRelationsFile($uploadedFile) {
        $row = 1;
        $rowFishNames = [];
        if (($handle = fopen($uploadedFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
//                print_r($data);die('<---');
                $num = count($data);
                $numToShow = $num - 1;
                if ($row > 1) {
                    $rowToShow = $row - 1;
                    echo "<p> $numToShow fields in line $rowToShow: <br /></p>\n";
                }
                $row++;
                for ($c=0; $c < $num; $c++) {
                    if ($row === 2 && $c > 0) {
                        $rowFishNames[$c] = $data[$c];
                    } else {
                        if ($c > 0) {
                            $cFishName = $data[0];
                            $cFishId = Fishmap_DB::getFishIdByFishName($cFishName);
                            $rFishId = Fishmap_DB::getFishIdByFishName($rowFishNames[$c]);
                            Fishmap_DB::insertRelation($cFishId, $rFishId, self::FISH_COMPATIBILITY_STATUSES[$data[$c]]);
                            echo 'Added relation: ' . $cFishName . ' <> ' . $rowFishNames[$c] . ' ---> ' . self::FISH_COMPATIBILITY_STATUSES[$data[$c]] . "<br />\n";
//                            diedump([$cFishId, $rFishId]);

                        }
                    }
                }
            }
            diedump($rowFishNames);
            fclose($handle);
        }
    }

    private function handleUploadedFishFile($uploadedFile) {
        $row = 1;
        if (($handle = fopen($uploadedFile, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $row++;
                for ($c=0; $c < $num; $c++) {
                    if ($row === 2 && $c > 0) {
                        Fishmap_DB::insertNewFish($data[$c], null, null, null, null);
                        echo "Added fish: " . $data[$c] . "<br>";
                    }
                }
            }
            fclose($handle);
        }
    }

    private function createUploadFishForm() {
    ?>
        <h2>Import fishes and fish relations</h2>
        <form method='post' action='' name='upload-csv-form' enctype='multipart/form-data'>
            <table>
                <tr>
                    <td>Upload file</td>
                    <td><input type='file' name='file'></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><input type='submit' name='submit-upload' value='Submit'></td>
                </tr>
            </table>
        </form>
    <?php
    }

}
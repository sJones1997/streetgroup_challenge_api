<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

class HomeownerService {

    const AND_SPLIT = "and"; 
    const AMPERSAND_SPLIT = "&";

    public function validateData($csvData): array {

        if($this->isCsvFile($csvData)){
            return array();
        }

        $extractedHomeownerData = $this->extractCsvData($csvData);
        return $this->formatExtractedData($extractedHomeownerData);

    }

    function isCsvFile($filePath): bool {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    return strtolower($extension) === 'csv';
    }

    private function extractCsvData($csvData): array {
        $openedFile = fopen($csvData, "r");
        $data = array();
        while(($line = fgetcsv($openedFile)) !== false) {
            $data[] = $line[0];
        }
        fclose($openedFile);
        return $data;
    }

    private function formatExtractedData($extractedHomeownerData): array {

        $formattedData = array();

        foreach($extractedHomeownerData as $homeownerRecord){
            if($this->hasMultipleRecords($homeownerRecord)){
                $formattedData = $this->separateRecords($homeownerRecord, $formattedData);
            } else {
                $formattedData[] = $homeownerRecord;
            }
        }

        return $formattedData;
    }


    private function hasMultipleRecords($homeownerRecord): bool {
        return count(explode(" ", $homeownerRecord)) > 3;
    }

    private function separateRecords($homeownerRecord, $formattedData): array {

        $explodedRecord = null;
        $splitOnAnd = explode(self::AND_SPLIT , $homeownerRecord);
        $splitOnAmpersand = explode(self::AMPERSAND_SPLIT, $homeownerRecord);

        if(count($splitOnAmpersand) > count($splitOnAnd)){
            $explodedRecord = $splitOnAmpersand;
        } else {
            $explodedRecord = $splitOnAnd;
        }

        return $this->includeRequiredFields($explodedRecord, $formattedData);

    }

    private function includeRequiredFields($splitRecord, $formattedData): array {

        $recordOne = explode(' ', trim($splitRecord[0]));
        $recordTwo = explode(' ', trim($splitRecord[1]));

        $recordOne = $this->addMissingLastName($recordOne, $recordTwo);
        $recordTwo = $this->addMissingLastName($recordTwo, $recordOne);

        $formattedData[] = implode(" ", $recordOne);
        $formattedData[] = implode(" ", $recordTwo);

        return $formattedData;
        
    }


    private function addMissingLastName($recordOne, $recordTwo){
        if(count($recordOne) == 1){
            $recordOne[] = $recordTwo[array_key_last($recordTwo)];
        }
        return $recordOne;
    }

    

}
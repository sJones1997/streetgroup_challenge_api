<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

class HomeownerService {

    const AND_SPLIT = "and"; 
    const AMPERSAND_SPLIT = "&";

    public function validateData($csvData): array {

        $extractedHomeownerData = $this->extractCsvData($csvData);
        return $this->formatExtractedData($extractedHomeownerData);

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

        $formattedHomeowners = array();

        //Remove the title
        array_shift($extractedHomeownerData);

        foreach($extractedHomeownerData as $homeownerRecord){

            //Check to see if there are multiple homeowners in a single record
            if($this->hasMultipleRecords($homeownerRecord)){
                $formattedHomeowners = $this->separateRecords($homeownerRecord, $formattedHomeowners);
            } else {
                $formattedHomeowners[] = $this->splitinToDesiredFormat($homeownerRecord);
            }   
        }

        return $formattedHomeowners;
    }

    private function hasMultipleRecords($homeownerRecord): bool {
        return count(explode(" ", $homeownerRecord)) > 3;
    }

    private function separateRecords($homeownerRecord, $formattedHomeowners): array {

        $explodedRecord = null;
        $splitOnAnd = explode(self::AND_SPLIT , $homeownerRecord);
        $splitOnAmpersand = explode(self::AMPERSAND_SPLIT, $homeownerRecord);

        //If there are multiple, split on conjunction and use larger array
        if(count($splitOnAmpersand) > count($splitOnAnd)){
            $explodedRecord = $splitOnAmpersand;
        } else {
            $explodedRecord = $splitOnAnd;
        }

        return $this->includeRequiredFields($explodedRecord, $formattedHomeowners);

    }

    private function includeRequiredFields($splitRecord, $formattedHomeowners): array {

        //After split, missing required field (last name) could be missing from one record so do a check on both

        $recordOne = $this->addMissingLastName($splitRecord[0], $splitRecord[1]);
        $recordTwo = $this->addMissingLastName($splitRecord[1], $splitRecord[0]);

        return array_merge($formattedHomeowners, [
            $this->splitinToDesiredFormat($recordOne), 
            $this->splitinToDesiredFormat($recordTwo)
            ]
        );
        
    }


    private function addMissingLastName($recordOne, $recordTwo){
        
        $recordOne = explode(' ', trim($recordOne));
        $recordTwo = explode(' ', trim($recordTwo));

        if(count($recordOne) == 1){
            $recordOne[] = $recordTwo[array_key_last($recordTwo)];
        }

        return implode(" ", $recordOne);
    }    

    private function splitinToDesiredFormat($homeownerRecord): array{

        //Once all records have been correctly formatted, split the record into the desired format given in the brief
        $homeownerRecordSplit = explode(" ", $homeownerRecord);

        if(count($homeownerRecordSplit) > 1){

            $person = array();
            $person["title"] = $homeownerRecordSplit[0];

            if(count($homeownerRecordSplit) > 2) {
                if(!$this->isInitial($homeownerRecordSplit[1])){
                    $person['firstName'] = $homeownerRecordSplit[1];
                } else {
                    $person['initial'] = $homeownerRecordSplit[1];
                }
            }

            $person["lastName"] = $homeownerRecordSplit[array_key_last($homeownerRecordSplit)];
            return $person;
        }

        return array();
    }


    private function isInitial($initial){
        $regex = '/[A-Za-z]\./';
        if(preg_match($regex, $initial) || strlen($initial) == 1){
            return true;
        }
        return false;
    }

}
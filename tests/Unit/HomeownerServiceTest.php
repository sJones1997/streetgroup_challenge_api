<?php

namespace Tests\Unit;

use App\Services\HomeownerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HomeownerServiceTest extends TestCase {

    protected $homeownerService;

    const VALID_FILE = "mock.csv";
    const INVALID_FILE = "mock.png";

    const CSV_DATA = [
        'homeowner',
        "Mr S Jones",
        "Mr and Mrs Doe",
        "Mr Sean Jones & Mrs Jones"
    ];

    
    public function testValidateData(){

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createMockCsv(self::CSV_DATA, self::VALID_FILE);

        Log::info(filetype($file));

        //When
        $formattedData = $this->homeownerService->validateData($file);

        //Then
        $this->assertEquals(count($formattedData), 6);
    }


    public function testInvalidFileFormat() {

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createWrongFileFormat(self::INVALID_FILE);

        Log::info(filetype($file));

        //When
        $formattedData = $this->homeownerService->validateData($file);

        //Then
        $this->assertEquals(count($formattedData), 0);
    }

    private function createMockCsv($data, $filePath){

        Storage::fake("csvfile");

        $data = implode("\n", $data);

        $file = UploadedFile::fake()
        ->create(
            $filePath,
            $data
        );


        return $file;
    }

    private function createWrongFileFormat($filePath){

        Storage::fake("invalidFile");

        $file = UploadedFile::fake()
        ->create(
            $filePath
        );


        return $file;
    }

}
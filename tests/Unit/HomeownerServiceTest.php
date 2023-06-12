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
        "Mr S. Jones",
        "Mr and Mrs Doe",
        "Mr Sean Jones & Mrs Jones"
    ];

    const CSV_DATA_INITIALS = [
        'homeowner',
        "Mr S Jones",
        "Mr S. Jones"
    ];

    const CSV_DATA_MULTIPLE = [
        'homeowner',
        "Mr and Mrs Doe"
    ];

    const CSV_DATA_SPLIT_FULL = [
        'homeowner',
        "Mr Sean Jones & Mrs F. Jones"
    ];


    
    public function testValidateData(){

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createMockCsv(self::CSV_DATA, self::VALID_FILE);

        Log::info(filetype($file));

        //When
        $formattedData = $this->homeownerService->validateData($file);

        //Then
        $this->assertEquals(6, count($formattedData));
    }

    public function testInitials(){

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createMockCsv(self::CSV_DATA_INITIALS, self::VALID_FILE);

        Log::info(filetype($file));

        //When
        $formattedData = $this->homeownerService->validateData($file);

        $recordOne = $formattedData[0];
        $recordTwo = $formattedData[1];

        Log::info(var_dump($formattedData, true));

        $this->assertEquals("Mr", $recordOne["title"]);
        $this->assertEquals("S", $recordOne["initial"]);
        $this->assertEquals("Jones", $recordOne["lastName"]);

        $this->assertEquals("Mr", $recordTwo["title"]);
        $this->assertEquals("S.", $recordTwo["initial"]);
        $this->assertEquals("Jones", $recordTwo["lastName"]);

    }


    
    public function testLastNameCarryOver(){

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createMockCsv(self::CSV_DATA_MULTIPLE, self::VALID_FILE);

        Log::info(filetype($file));

        //When
        $formattedData = $this->homeownerService->validateData($file);

        $recordOne = $formattedData[0];
        $recordTwo = $formattedData[1];

        $this->assertEquals("Mr", $recordOne["title"]);
        $this->assertEquals("Doe", $recordOne["lastName"]);

        $this->assertEquals("Mrs", $recordTwo["title"]);
        $this->assertEquals("Doe", $recordTwo["lastName"]);

    }


    public function testSplitTwoFullRecords(){

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createMockCsv(self::CSV_DATA_SPLIT_FULL, self::VALID_FILE);

        Log::info(filetype($file));

        //When
        $formattedData = $this->homeownerService->validateData($file);

        $recordOne = $formattedData[0];
        $recordTwo = $formattedData[1];

        $this->assertEquals("Mr", $recordOne["title"]);
        $this->assertEquals("Sean", $recordOne["firstName"]);
        $this->assertEquals("Jones", $recordOne["lastName"]);

        $this->assertEquals("Mrs", $recordTwo["title"]);
        $this->assertEquals("F.", $recordTwo["initial"]);
        $this->assertEquals("Jones", $recordTwo["lastName"]);

    }



    public function testInvalidFileFormat() {

        $this->homeownerService = new HomeownerService();

        //Given
        $file = $this->createWrongFileFormat(self::INVALID_FILE);

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
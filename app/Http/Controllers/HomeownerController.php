<?php

namespace App\Http\Controllers;

use App\Http\Requests\HomeownerDataValidationRequest;
use App\Services\HomeownerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HomeownerController extends Controller
{

    public function __construct(
        private HomeownerService $homeownerService
    ){

    }

    public function validateData(HomeownerDataValidationRequest $request): Response {
        return response($this->homeownerService->validateData($request->file("csvData")));
    }
}

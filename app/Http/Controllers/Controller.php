<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $successStatus = 200;
    protected $badRequestStatus = 400;
    protected $unauthorizedStatus = 401;
    protected $notFoundStatus = 404;
    protected $unprocessableStatus = 422;
}

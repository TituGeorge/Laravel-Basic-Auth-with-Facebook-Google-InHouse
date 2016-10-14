<?php
/**
 * Created By Titu George
 * titugeorge@gmail.com
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

define("API_SUCCESS", 'Success');
define("API_FAILURE", 'Failure');

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public static function JSONResponse($data, $status) {
        header('Content-Type: application/json');
        return json_encode(array('data' => $data, 'status' => $status));
    }
}

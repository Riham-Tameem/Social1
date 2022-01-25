<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendResponse( $message,$result )
    {
       // if($paginate == null){
            $response = [
                'status'     => true,
                'statusCode' => 200,
                'message'    => $message,
                'data'       => $result,
            ];
            return response()->json($response);
      //  }

    }
    public function sendError($statusCode,$error)
    {
        $response = [
            'status'     => false,
            'statusCode' => $statusCode,
            'message' => $error,
            'data' => [],
        ];

//        if(!empty($errorMessages)){
//            $response['data'] = $errorMessages;
//        }
        return response()->json($response);
    }
}

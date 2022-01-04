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
                'item'       => $result,
            ];
            return response()->json($response);
      //  }

    }
    public function sendError($error)
    {
        $response = [
            'error'     => false,
            'statusCode' => 404,
            'message' => $error,
        ];

//        if(!empty($errorMessages)){
//            $response['data'] = $errorMessages;
//        }
        return response()->json($response);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiResponseController extends Controller
{
    /**
     * Handle a success response.
     * @date 2021-03-31
     * @param $data
     * @return JsonResponse
     */
    public function successResponse($data)
    {
        return response()->json($data, 200);
    }

    /**
     * Handle an error response.
     * @date 2021-03-31
     * @param array Exception $e
     * @return JsonResponse
     */
    public function errorResponse(\Exception $e)
    {
        $log = [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ];
        logger('ERROR: ', $log);
        return response()->json($log, 500);
    }

    /**
     * Handle a success response.
     * @date 2021-03-31
     * @param $data
     * @return JsonResponse
     */
    public function customResponse($data, $code)
    {
        return response()->json($data, $code);
    }
}

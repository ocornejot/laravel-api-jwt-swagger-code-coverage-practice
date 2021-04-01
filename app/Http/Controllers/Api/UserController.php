<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;

/**
 * @OA\Info(title="API's JWT/Swagger", version="1.0")
 *
 */
class UserController extends ApiResponseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *      path="/api/users",
     *      summary="Obtiene el listado de usuarios registrados.",
     *      tags={"users"},
     *      @OA\Response(
     *          response=200,
     *          description="ok",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthorized.",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *      security={
     *          {"bearerAuth": {}}
     *      }
     * )
     */
    public function index()
    {
        try {
            $users = User::all();
            return response()->json(compact('users'));
            return $this->successResponse(compact('users'));
        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }
}

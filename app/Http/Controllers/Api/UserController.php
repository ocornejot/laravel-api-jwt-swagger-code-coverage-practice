<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * @OA\Info(title="API's JWT/Swagger", version="1.0")
 *
 * @OA\Server(url="http://api-jwt.test")
 */
class UserController extends Controller
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
        $users = User::all();
        return response()->json(compact('users'));
    }
}

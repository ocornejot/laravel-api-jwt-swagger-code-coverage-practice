<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      tags={"auth"},
     *      summary="endpoint para autentificar",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="ok"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Datos incorrectos."
     *      ),
     *      @OA\Response(
     *          response="default",
     *          description="unauthorized."
     *      )
     * )
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->createNewToken($token);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      tags={"auth"},
     *      summary="registra un nuevo usuario",
     *      @OA\Parameter(
     *          name="email",
     *          in="query",
     *          required=true,
     *      ),
     *      @OA\Parameter(
     *          name="password",
     *          in="query",
     *          required=true,
     *      ),
     *      @OA\Parameter(
     *          name="password_confirmation",
     *          in="query",
     *          required=true,
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="query",
     *          required=true,
     *      ),
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
     * )
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
                    $validator->validated(),
                    ['password' => bcrypt($request->password)]
                ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }


    /**
     * @OA\Post(
     *      path="/api/auth/logout",
     *      summary="Cierra la sesión del usuario logueado.",
     *      tags={"auth"},
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
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * @OA\Post(
     *      path="/api/auth/refresh",
     *      summary="Renueva el token del usuario logueado.",
     *      tags={"auth"},
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
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * @OA\Get(
     *      path="/api/auth/user-profile",
     *      summary="Obtiene los datos del usaurio logueado.",
     *      tags={"auth"},
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
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}

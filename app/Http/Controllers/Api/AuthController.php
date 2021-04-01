<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\ApiResponseController;

class AuthController extends ApiResponseController
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
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return $this->customResponse($validator->errors(), 422);
            }
            if (! $token = auth()->attempt($validator->validated())) {
                return $this->customResponse(['error' => 'Unauthorized'], 401);
            }
            return $this->createNewToken($token);
        } catch (\Exception $e) {
            $this->errorResponse($e);
        }
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
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|between:2,100',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|confirmed|min:6',
            ]);

            if($validator->fails()){
                return $this->customResponse($validator->errors()->toJson(), 400);
            }

            $user = User::create(array_merge(
                        $validator->validated(),
                        ['password' => bcrypt($request->password)]
                    ));

            return $this->successResponse([
                'message' => 'User successfully registered',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            $this->errorResponse($e);
        }
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
        try {
            auth()->logout();
            return $this->successResponse(['message' => 'User successfully signed out']);
        } catch (\Exception $e) {
            $this->errorResponse($e);
        }

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
        try {
            return $this->createNewToken(auth()->refresh());
        } catch (\Exception $e) {
            $this->errorResponse($e);
        }
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
        try {
            return $this->successResponse(auth()->user());
        } catch (\Exception $e) {
            $this->errorResponse($e);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}

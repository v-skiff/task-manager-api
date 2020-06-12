<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected function generateAccessToken($user) {
        $token = $user->createToken($user->email . '_' . now());

        return $token->accessToken;
    }

    /**
     * @OA\Post(
     *      path="/api/register",
     *      operationId="register_user",
     *      tags={"Auth"},
     *      summary="Register new user",
     *      description="Returns user data",
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function register(Request $request) {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' =>  $request->last_name,
            'email' =>  $request->email,
            'password' =>  bcrypt($request->password),
        ]);

        return response()->json($user);
    }

    /**
     * @OA\Post(
     *      path="/api/login",
     *      operationId="login_user",
     *      tags={"Auth"},
     *      summary="Login user",
     *      description="Returns bearer token",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ])) {

            $user = Auth::user();
            $token = $user->createToken($user->email . '_' . now());

            return response()->json([
                'token' => $token->accessToken,
            ]);
        }
    }
}

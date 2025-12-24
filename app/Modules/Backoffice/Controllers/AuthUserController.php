<?php

namespace App\Modules\Backoffice\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Backoffice\Models\User;
use App\Modules\Backoffice\Requests\SignupUserRequest;
use App\Modules\Backoffice\Requests\UserLoginRequest;
use App\Traits\ApiResponses;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthUserController extends Controller
{
    use ApiResponses;

    /**
     * Methode d'inscription d'un utilisateur
     */
    public function signup(SignupUserRequest $request)
    {
        try {
            $fields = $request->validated();

            $user = User::create([
                'name' => $fields['name'],
                'telephone' => $fields['telephone'],
                'adresse' => $fields['adresse'],
                'email' => $fields['email'],
                'password' => Hash::make($fields['password']),
            ]);

            $token = $user->createToken('user-token', ['user'])->plainTextToken;

            return $this->successResponseWithToken($user, $token, "Utilisateur créé avec succès");
        } catch (AuthorizationException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    /**
     * Methode d'authentification pour un utilisateur
     */
    public function login(UserLoginRequest $request)
    {
        $credentaials = $request->validated();

        $user = User::where('email', $credentaials['email'])->first();

        if (! $user || ! Hash::check($credentaials['password'], $user->password)) {
            return $this->errorResponse("Les coordonnées sont invalide. Réessayer", 401);
        }

        $token = $user->createToken('user-token', ['user'])->plainTextToken;

        return $this->successResponseWithToken($user, $token, "Utilisateur loggué avec succès");
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => 1,
            'message' => "Utilisateur déconnecté avec succès"
        ], 200);
    }
}

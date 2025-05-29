<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginUserRequest;
use App\Http\Requests\V1\RegisterUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use function Laravel\Prompts\password;
use function Pest\Laravel\json;

/**
 *  APIs for managing todos resources
 *
 * @group Auth Management
 */
class AuthController extends Controller
{
    /**
     * Register
     *
     * Register the new user and return its access token.
     *
     * @unauthenticated
     * @apiResource App\Http\Resources\V1\UserResource
     * @apiResourceModel App\Models\User
     * @param RegisterUserRequest $request
     * @return UserResource
     */
    public function register(RegisterUserRequest $request): UserResource|JsonResponse
    {
        $user = User::create($request->validated());

        // Generate JWT token for the newly created user
        $token = auth()->login($user);

        $user = auth()->user();
        $user->access_token = $token;
        $user->expires_in = auth()->factory()->getTTL() * 60;

        return new UserResource($user);
    }

    /**
     * Login
     *
     * Log the user in and return its access token.
     *
     * @unauthenticated
     * @apiResource App\Http\Resources\V1\UserResource
     * @apiResourceModel App\Models\User
     * @param LoginUserRequest $request
     * @return UserResource | JsonResponse
    */
    public function login(LoginUserRequest $request): UserResource|JsonResponse
    {
        $token = auth()->attempt($request->validated());

        if (! $token)
        {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $user = auth()->user();
        $user->access_token = $token;
        $user->expires_in = auth()->factory()->getTTL() * 60;

        return new UserResource($user);
    }

    /**
     * Get the authenticated User.
     *
     */
    public function whoami(): UserResource
    {
        $user = auth()->user();
        return new UserResource($user);
    }

    /**
     * Refresh a token.
     *
     */
    public function refresh(): UserResource|JsonResponse
    {
        try {

            $token = auth()->refresh();

            return response()->json(data: [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token cannot be refreshed'], 401);
        }
    }


    /**
     * Logout
     *
     * Returns a list of the current user's todos.
     *
     * @authenticated
     * @response 200{
     *     "message" : "Successfully logged out"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}

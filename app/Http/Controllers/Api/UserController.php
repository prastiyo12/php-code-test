<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\ListUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Mail\WelcomeUser;
use App\Mail\NotifyAdmin;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * @group Users
     * 
     * Create a new user
     *
     * This endpoint allows you to create a new user.  
     * It also sends:
     * - A welcome email to the new user.
     * - A notification email to the admin.
     *
     * @bodyParam email string required The email address of the user. Example: alice@example.com
     * @bodyParam password string required The password (minimum 8 characters). Example: password123
     * @bodyParam name string required The full name of the user. Example: Alice Doe
     * @bodyParam role string optional The role of the user. One of: administrator, manager, user. Example: user
     *
     * @response 201 {
     *   "id": 1,
     *   "email": "alice@example.com",
     *   "name": "Alice Doe",
     *   "role": "user",
     *   "created_at": "2025-11-13T08:00:00Z"
     * }
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        Mail::to($user->email)->send(new WelcomeUser($user));

        $adminEmail = config('app.admin_email') ?? env('ADMIN_EMAIL');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NotifyAdmin($user));
        }

        return response()->json(new UserResource($user), 201);
    }

    /**
     * @group Users
     * 
     * List all active users
     *
     * Retrieve all active users with pagination, filtering, and sorting.  
     * Each record includes:
     * - `orders_count` (number of orders)
     * - `can_edit` (edit permission flag based on role)
     *
     * @queryParam search string Optional Search keyword (matches name or email). Example: alice
     * @queryParam page integer Optional Page number. Default: 1. Example: 2
     * @queryParam sortBy string Optional Sort field. One of: name, email, created_at. Example: name
     *
     * @response 200 {
     *   "page": 1,
     *   "per_page": 15,
     *   "total": 2,
     *   "users": [
     *     {
     *       "id": 1,
     *       "email": "alice@example.com",
     *       "name": "Alice Doe",
     *       "role": "user",
     *       "created_at": "2025-11-13T08:00:00Z",
     *       "orders_count": 2,
     *       "can_edit": true
     *     }
     *   ]
     * }
     */
    public function index(ListUserRequest $request): JsonResponse
    {
        $search = $request->get('search');
        $sortBy = $request->get('sortBy', 'created_at');

        $query = User::query()->where('active', true)->withCount('orders');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allowedSort = ['name', 'email', 'created_at'];
        $query->orderBy(in_array($sortBy, $allowedSort) ? $sortBy : 'created_at', 'desc');

        $users = $query->paginate(15);

        $authUser = Auth::user();
        $users->getCollection()->transform(function ($user) use ($authUser) {
            $user->can_edit = $this->computeCanEdit($authUser, $user);
            return $user;
        });

        return response()->json([
            'page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'users' => UserResource::collection($users)->resolve(),
        ]);
    }

    private function computeCanEdit(?User $actor, User $target): bool
    {
        if (!$actor) {
            return false;
        }

        return match ($actor->role) {
            'administrator' => true,
            'manager' => $target->role === 'user',
            'user' => $actor->id === $target->id,
            default => false,
        };
    }
}

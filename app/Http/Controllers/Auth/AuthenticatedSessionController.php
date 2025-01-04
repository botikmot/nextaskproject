<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Events\UserStatusChanged;
use App\Services\LevelService;

class AuthenticatedSessionController extends Controller
{
    protected $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Retrieve the authenticated user
        $user = Auth::user();
        // Check if this is the user's first login
        //$isNewUser = !$user->last_login;
        $isNewUser = $user->is_new;

        // Update the last_login timestamp
        $user->last_login = now();
        if ($isNewUser) {
            $user->is_new = false; // Clear the is_new flag after first login
        }
        $user->save();

        $this->levelService->checkAndUpdateLevel($user);
        $this->levelService->checkAllBadges($user);
        
        // Pass the `isNewUser` variable to the dashboard so it can display the appropriate welcome message
        return redirect()->intended(route('dashboard', absolute: false))->with([
            'isNewUser' => $isNewUser,
            'userName' => $user->name,
            'profileImage' => $user->profile_image,
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        broadcast(new UserStatusChanged($user, 'offline'));
        $user->update(['last_login' => null]);
        Auth::guard('web')->logout();
       
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

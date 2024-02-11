<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'employee_id' => 'required|string|unique:users,employee_id',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:Manager,Teammate', // Assuming role is passed from the form
        ]);

        // Retrieve role ID based on role name
        $role = Role::where('name', $request->role)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
        ]);

        event(new Registered($user));

        // If manager create the teammate
        if($request->role == 'Teammate'){
            return response()->json(['message' => 'Teammate added successfully'], 201);
        }

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}

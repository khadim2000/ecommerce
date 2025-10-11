<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;



use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role' => 'client',
            'password' => bcrypt($request->password),
        ]);
        
         

    return redirect()->intended('/dashboard');
}

    

public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        throw ValidationException::withMessages([
            'email' => ['Les informations sont invalides.'],
        ]);
    }

    $request->session()->regenerate();

    return redirect()->intended('/dashboard');
}

    public function user(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
    Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

   return redirect()->intended('/login');

    }
}

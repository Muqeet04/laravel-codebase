<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * GET /login
     */
    public function loginPage(Request $request)
    {
        if ($request->session()->has('user')) {
            return redirect('/');
        }
        return view('login');
    }

    /**
     * POST /api/login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $adminUser = env('ADMIN_USER', 'admin');
        $adminPassword = env('ADMIN_PASSWORD', 'attendance2026');

        if ($validated['username'] === $adminUser && $validated['password'] === $adminPassword) {
            $request->session()->put('user', $validated['username']);
            return response()->json(['success' => true]);
        }

        return response()->json(['detail' => 'Invalid username or password'], 401);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget('user');
        return response()->json(['success' => true]);
    }
}

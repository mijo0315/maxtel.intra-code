<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\role;
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeOld(LoginRequest $request)
    {
      
    $credentials = $request->only('email', 'password');

    // Attempt to log in with the provided credentials
    if (Auth::attempt($credentials)) {
        // Authentication passed
        $request->session()->regenerate();
        
        return redirect()->intended(RouteServiceProvider::HOME);
    } else {
        // Authentication failed
        
        return redirect()->back()
                         ->withErrors([
                             'email' => 'The provided credentials are incorrect.',
                         ])
                         ->onlyInput('email');
    }

        //return redirect()->intended(RouteServiceProvider::HOME);
    }

    public function store(LoginRequest $request)
    {
        $loginInput = $request->input('email');
        $password = $request->input('password');

        // Determine if the input is an email or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Try to log in using the identified field
        if (Auth::attempt([$fieldType => $loginInput, 'password' => $password])) {
            $request->session()->regenerate();
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return back()->withErrors([
            'email' => 'The provided credentials are incorrect.',
        ])->onlyInput('email');
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/outside');
    }
}

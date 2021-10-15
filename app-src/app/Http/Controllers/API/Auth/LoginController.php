<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Auth\LoginController as Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * since SessionGuard is more completely than TokenGuard
     * so we use SessionGuard for here.
     * but verify if user is logged-in or not,
     * still using TokenGuard.
     * \Illuminate\Support\Facades\Auth::guard() <-- \Illuminate\Auth\SessionGuard;
     * \Illuminate\Support\Facades\Auth::guard('api') <-- \Illuminate\Auth\TokenGuard;
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:api')->except('logout');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @override \Illuminate\Foundation\Auth\AuthenticatesUsers@sendLoginResponse
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        if ($request->hasSession()) $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $user = $this->guard()->user();

        if ($response = $this->authenticated($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse($user, 200)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('api')->user()->updateToken();

        $this->guard()->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse(['message' => 'logout success.'], 200)
            : redirect('/');
    }
}

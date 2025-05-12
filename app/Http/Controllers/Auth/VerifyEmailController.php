<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
{
    // Buscar el usuario usando el ID proporcionado en la URL
    $user = \App\Models\User::findOrFail($request->route('id'));

    // Verificar si ya está verificado
    if ($user->hasVerifiedEmail()) {
        return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
    }

    // Marcar el correo como verificado
    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    // Redirigir al usuario tras la verificación
    return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
}

}

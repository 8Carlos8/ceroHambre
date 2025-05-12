
<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request)
    {
        // Verificar si el usuario está autenticado antes de proceder
        if (Auth::check()) {
            $request->fulfill();  // Verifica el correo
            return redirect('/home');  // Redirige al usuario a su página de inicio o donde desees
        }

        // Si el usuario no está autenticado, lo redirige al login
        return redirect('/login');
    }
}
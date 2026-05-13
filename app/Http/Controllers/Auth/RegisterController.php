<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Domain\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            Mail::raw(
                "Nuevo registro en VideoConf Reservas\n\nNombre: {$user->name}\nEmail: {$user->email}\nFecha: {$user->created_at}",
                function ($msg) {
                    $msg->to(config('mail.to_address'))->subject('Nuevo usuario registrado');
                }
            );
        } catch (\Throwable $e) {
            // Log email error without breaking registration
            logger()->error('Error enviando correo de registro: ' . $e->getMessage());
        }

        Auth::login($user);

        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordCodeNotification;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Passo 1: formulário do e-mail.
     */
    public function showEmailForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Passo 1: envia o código por e-mail.
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim($request->email));
        $user = User::where('email', $email)->first();

        if ($user) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_resets')->where('email', $email)->delete();
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => Hash::make($code),
                'created_at' => Carbon::now(),
            ]);

            $user->notify(new ResetPasswordCodeNotification($code));
        }

        $request->session()->put('password_reset_email', $email);
        $request->session()->forget('password_reset_verified');

        return redirect()
            ->route('password.code.form')
            ->with('status', 'Se o e-mail existir em nossa base, enviamos um código de verificação. Confira sua caixa de entrada.');
    }

    /**
     * Passo 2: formulário do código.
     */
    public function showCodeForm(Request $request)
    {
        $email = $request->session()->get('password_reset_email', old('email'));

        return view('auth.passwords.code', compact('email'));
    }

    /**
     * Passo 2: valida o código e libera a tela da nova senha.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|min:4|max:12',
        ]);

        $email = strtolower(trim($request->email));
        $code = trim($request->code);

        $row = DB::table('password_resets')->where('email', $email)->first();
        $expire = (int) config('auth.passwords.users.expire', 60);

        if (! $row
            || Carbon::parse($row->created_at)->addMinutes($expire)->isPast()
            || ! Hash::check($code, $row->token)
        ) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->withErrors(['code' => 'Código inválido ou expirado. Solicite um novo.']);
        }

        $request->session()->put('password_reset_email', $email);
        $request->session()->put('password_reset_verified', $email);

        return redirect()->route('password.new.form');
    }

    /**
     * Passo 3: formulário da nova senha (só após código válido).
     */
    public function showNewPasswordForm(Request $request)
    {
        if (! $request->session()->get('password_reset_verified')) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Informe o e-mail e o código antes de definir a nova senha.']);
        }

        $email = $request->session()->get('password_reset_verified');

        return view('auth.passwords.new', compact('email'));
    }

    /**
     * Passo 3: grava a nova senha.
     */
    public function updatePassword(Request $request)
    {
        $email = $request->session()->get('password_reset_verified');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            $request->session()->forget(['password_reset_email', 'password_reset_verified']);

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Usuário não encontrado.']);
        }

        // Garante que o código ainda é válido
        $row = DB::table('password_resets')->where('email', $email)->first();
        $expire = (int) config('auth.passwords.users.expire', 60);

        if (! $row || Carbon::parse($row->created_at)->addMinutes($expire)->isPast()) {
            $request->session()->forget(['password_reset_email', 'password_reset_verified']);

            return redirect()->route('password.request')
                ->withErrors(['email' => 'Código expirado. Solicite um novo.']);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $email)->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()
            ->route('login')
            ->with('status', 'Senha redefinida com sucesso! Faça login com a nova senha.');
    }
}

<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    protected function intentosKey(): string
    {
        return 'login_attempts:' . sha1($this->ip() . '|' . strtolower(trim((string) $this->input('email'))));
    }

    protected function umbral(): int
    {
        return (int) config('services.turnstile.captcha_after', 3);
    }

    public function authenticate(): void
    {
        $intentos = Cache::get($this->intentosKey(), 0);

        if ($intentos >= $this->umbral()) {
            $this->verificarCaptcha();
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            Cache::put($this->intentosKey(), $intentos + 1, now()->addMinutes(15));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Cache::forget($this->intentosKey());
    }

    protected function verificarCaptcha(): void
    {
        $token = $this->input('cf-turnstile-response');

        if (! $token) {
            throw ValidationException::withMessages([
                'captcha' => 'Completá la verificación de seguridad.',
            ]);
        }

        $respuesta = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $token,
            'remoteip' => $this->ip(),
        ]);

        if (! $respuesta->json('success')) {
            throw ValidationException::withMessages([
                'captcha' => 'No se pudo verificar la seguridad. Intentá nuevamente.',
            ]);
        }
    }
}
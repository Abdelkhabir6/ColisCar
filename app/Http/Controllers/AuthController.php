<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\WelcomeEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:user,driver,sender',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
        ]);

        Auth::login($user);

        // Envoyer l'email de bienvenue
        $user->notify(new WelcomeEmail());

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Rediriger vers Google pour l'authentification
     */
    public function redirectToGoogle()
    {
        // Vérifier que les identifiants Google sont configurés
        if (!config('services.google.client_id') || !config('services.google.client_secret')) {
            return redirect('/login')->with('error', 'L\'authentification Google n\'est pas configurée.');
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la redirection Google: ' . $e->getMessage());
            return redirect('/login')->with('error', 'Une erreur est survenue lors de la connexion avec Google. Veuillez réessayer.');
        }
    }

    /**
     * Gérer le callback de Google
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Chercher un utilisateur existant par email ou google_id
            $user = User::where('email', $googleUser->getEmail())
                ->orWhere('google_id', $googleUser->getId())
                ->first();

            if ($user) {
                // Mettre à jour les informations Google si nécessaire
                if (!$user->google_id) {
                    $user->google_id = $googleUser->getId();
                }
                if (!$user->avatar && $googleUser->getAvatar()) {
                    $user->avatar = $googleUser->getAvatar();
                }
                if (!$user->is_verified) {
                    $user->is_verified = true; // Google vérifie l'email
                }
                $user->save();
            } else {
                // Créer un nouvel utilisateur
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(uniqid() . time()), // Mot de passe aléatoire (non utilisé pour Google)
                    'role' => 'user', // Rôle par défaut
                    'is_verified' => true, // Google vérifie l'email
                    'email_verified_at' => now(), // Marquer l'email comme vérifié
                ]);

                // Envoyer l'email de bienvenue
                try {
                    $user->notify(new WelcomeEmail());
                } catch (\Exception $e) {
                    // Ne pas bloquer la connexion si l'email échoue
                    \Log::warning('Impossible d\'envoyer l\'email de bienvenue: ' . $e->getMessage());
                }
            }

            Auth::login($user, true); // Se souvenir de l'utilisateur

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'authentification Google: ' . $e->getMessage());
            return redirect('/login')->withErrors([
                'email' => 'Une erreur est survenue lors de l\'authentification avec Google. Veuillez réessayer.',
            ]);
        }
    }
}


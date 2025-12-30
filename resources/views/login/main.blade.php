@extends('../layout/' . $layout)

@section('head')
    <title>Connexion - Mon Application</title>
    <!-- Font Awesome CDN pour l'icône œil -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
@endsection

@section('content')
<div class="container sm:px-10">
    <div class="block xl:grid grid-cols-2 gap-4">
        <!-- Partie info / illustration -->
        <div class="hidden xl:flex flex-col min-h-screen">
            <a href="" class="-intro-x flex items-center pt-5">
                <img alt="Logo" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                <span class="text-white text-lg ml-3">
                    Mon<span class="font-medium">Application</span>
                </span>
            </a>
            <div class="my-auto">
                <img alt="Illustration" class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">Quelques clics<br>pour vous connecter.</div>
                <div class="-intro-x mt-5 text-lg text-white dark:text-gray-500">Gérez toutes vos tâches facilement.</div>
            </div>
        </div>

        <!-- Formulaire connexion -->
        <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
            <div class="my-auto mx-auto xl:ml-20 bg-white xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">Connexion</h2>

                {{-- Zone d'erreur globale pour le mot de passe expiré --}}
                <div id="expired-password-alert" class="intro-x mt-6 hidden">
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 mr-3 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="font-bold text-sm">🔒 Mot de passe expiré</p>
                                <p id="expired-password-message" class="text-sm mt-1"></p>
                                <p class="text-xs mt-2 bg-red-50 p-2 rounded">
                                    ⚠️ Veuillez contacter l'administrateur pour réinitialiser votre mot de passe.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="intro-x mt-8">
                    <form id="login-form">
                        @csrf
                        <input type="text" id="input-login" name="login" class="intro-x login__input input input--lg border border-gray-300 block" placeholder="login" required>
                        <div id="error-login" class="login__input-error w-5/6 text-theme-6 mt-2"></div>

                        <!-- Champ mot de passe avec œil -->
                        <!-- Champ mot de passe avec œil -->
<div class="intro-x mt-4">
    <div class="relative w-full">
        <input type="password"
            id="password"
            name="password"
            class="intro-x login__input input input--lg border border-gray-300 block w-full pr-10"
            placeholder="Mot de passe"
            required>

        <!-- Icône Oeil SVG (visible par défaut) -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer z-50" onclick="togglePassword()">
            <!-- Icône Eye (Oeil ouvert) -->
            <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 hover:text-gray-600">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>

            <!-- Icône Eye Off (Oeil barré - caché par défaut) -->
            <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 hover:text-gray-600 hidden">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        </div>
    </div>
    <div id="error-password" class="login__input-error w-5/6 text-theme-6 mt-2"></div>
</div>
                        <div class="intro-x flex text-gray-700 dark:text-gray-600 text-xs sm:text-sm mt-4">
                            <div class="flex items-center mr-auto">
                                <input type="checkbox" class="input border mr-2" id="input-remember-me" name="remember_me">
                                <label class="cursor-pointer select-none" for="input-remember-me">Se souvenir de moi</label>
                            </div>
                            <a href="#">Mot de passe oublié ?</a>
                        </div>

                        <div class="intro-x mt-5 xl: mt-8 text-center xl:text-left">
                            <button type="button" id="btn-login" class="button button--lg w-full xl:w-32 text-white bg-theme-1 xl:mr-3 align-top">Se connecter</button>
                        </div>
                    </form>
                </div>
                <div class="intro-x mt-10 xl:mt-24 text-gray-700 dark:text-gray-600 text-center xl:text-left text-sm">
                    En vous connectant, vous acceptez nos <br> <a class="text-theme-1 dark:text-theme-10" href="#">Conditions d'utilisation</a> & <a class="text-theme-1 dark:text-theme-10" href="#">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>


   // ✅ CORRECTION : Utiliser les bons IDs de champs
async function login() {
    // Réinitialiser TOUTES les erreurs
    cash('#login-form').find('.input').removeClass('border-theme-6');
    cash('#login-form').find('.login__input-error').html('');
    cash('#expired-password-alert').addClass('hidden');

    let loginValue = cash('#input-login').val();
    let password = cash('#password').val(); // ✅ CORRIGÉ : #password au lieu de #input-password
    let rememberMe = cash('#input-remember-me').is(':checked');

    // Affiche un loader
    cash('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>').svgLoader();

    try {
        const response = await axios.post('{{ route("login") }}', {
            login: loginValue,
            password: password,
            remember_me: rememberMe,
            _token: '{{ csrf_token() }}'
        });

        // ✅ CONNEXION RÉUSSIE
        if (response.data.success) {

            // ⚠️ Avertissement d'expiration prochaine (25-29 jours)
            if (response.data.show_warning && response.data.warning) {
                showWarningToast(response.data.warning, response.data.days_left);

                // Rediriger après 3 secondes
                setTimeout(() => {
                    window.location.href = '{{ route("page", ["layout" => "side-menu", "theme" => "light", "pageName" => "dashboard"]) }}';
                }, 3000);
            } else {
                // ✅ Connexion normale
                window.location.href = '{{ route("page", ["layout" => "side-menu", "theme" => "light", "pageName" => "dashboard"]) }}';
            }
        }

    } catch (err) {
        cash('#btn-login').html('Se connecter');

        // ❌ MOT DE PASSE EXPIRÉ (403 Forbidden)
        if (err.response && err.response.status === 403) {
            // Afficher l'alerte d'expiration
            cash('#expired-password-alert').removeClass('hidden');
            cash('#expired-password-message').html(err.response.data.message || 'Votre mot de passe a expiré.');

            // Marquer les champs en rouge
            cash('#input-login').addClass('border-red-500');
            cash('#password').addClass('border-red-500'); // ✅ CORRIGÉ

            // Scroll vers le haut pour voir l'alerte
            window.scrollTo({ top: 0, behavior: 'smooth' });

            return;
        }

        // ❌ ERREURS DE VALIDATION (422 Unprocessable Entity)
        if (err.response && err.response.status === 422) {
            let errors = err.response.data.errors || {};

            // Afficher les erreurs de validation
            for (const [key, val] of Object.entries(errors)) {
                // ✅ Gérer spécifiquement le champ password
                if (key === 'password') {
                    cash(`#password`).addClass('border-theme-6');
                    cash(`#error-password`).html(val[0]);
                } else {
                    cash(`#input-${key}`).addClass('border-theme-6');
                    cash(`#error-${key}`).html(val[0]);
                }
            }

            // Message d'erreur générique (identifiants incorrects)
            if (err.response.data.message && !errors.login && !errors.password) {
                cash(`#password`).addClass('border-theme-6'); // ✅ CORRIGÉ
                cash(`#error-password`).html(err.response.data.message);
            }
        }
        // ❌ ERREUR SERVEUR (500+)
        else {
            cash(`#password`).addClass('border-theme-6'); // ✅ CORRIGÉ
            cash(`#error-password`).html('Erreur serveur, veuillez réessayer plus tard.');
        }
    }
}

// ⚠️ FONCTION : Afficher un toast d'avertissement (mot de passe va expirer)
function showWarningToast(message, daysLeft) {
    const toast = `
        <div id="warning-toast" class="fixed top-4 right-4 z-50 max-w-md bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-lg shadow-2xl animate-slide-in">
            <div class="flex items-start">
                <svg class="w-8 h-8 mr-3 text-yellow-600 flex-shrink-0 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-bold text-lg">⚠️ Attention</p>
                    <p class="text-sm mt-1">${message}</p>
                    <p class="text-xs mt-2 bg-yellow-200 p-2 rounded">
                        Il vous reste <strong>${daysLeft} jour(s)</strong> pour changer votre mot de passe.
                    </p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-yellow-500 hover:text-yellow-700">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
    cash('body').append(toast);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
        cash('#warning-toast').fadeOut(300, function() {
            cash(this).remove();
        });
    }, 5000);
}

function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Initialisation avec cash
cash(function () {
    // Gestion des événements
    cash('#login-form').on('keyup', function(e) {
        if (e.key === 'Enter') {
            login();
        }
    });

    cash('#btn-login').on('click', function() {
        login();
    });
});
</script>

<style>
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
</style>
@endsection

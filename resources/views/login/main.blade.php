@extends('../layout/' . $layout)

@section('head')
    <title>Connexion - Mon Application</title>
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
                <div class="intro-x mt-8">
                    <form id="login-form">
                        @csrf
                       <input type="text" id="input-name" name="name" class="intro-x login__input input input--lg border border-gray-300 block" placeholder="Nom d'utilisateur" required>
                        <div id="error-name" class="login__input-error w-5/6 text-theme-6 mt-2"></div>


                        <input type="password" id="input-password" name="password" class="intro-x login__input input input--lg border border-gray-300 block mt-4" placeholder="Mot de passe" required>
                        <div id="error-password" class="login__input-error w-5/6 text-theme-6 mt-2"></div>

                        <div class="intro-x flex text-gray-700 dark:text-gray-600 text-xs sm:text-sm mt-4">
                            <div class="flex items-center mr-auto">
                                <input type="checkbox" class="input border mr-2" id="input-remember-me" name="remember_me">
                                <label class="cursor-pointer select-none" for="input-remember-me">Se souvenir de moi</label>
                            </div>
                            <a href="#">Mot de passe oublié ?</a>
                        </div>

                        <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                            <button type="button" id="btn-login" class="button button--lg w-full xl:w-32 text-white bg-theme-1 xl:mr-3 align-top">Se connecter</button>
                            <!-- <a href="{{ route('register-view') }}" class="button button--lg w-full xl:w-32 text-gray-700 border border-gray-300 dark:border-dark-5 dark:text-gray-300 mt-3 xl:mt-0 align-top text-center block no-underline">S'inscrire</a> -->
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
    cash(function () {
        async function login() {
            // Réinitialiser les erreurs
            cash('#login-form').find('.input').removeClass('border-theme-6');
            cash('#login-form').find('.login__input-error').html('');

          let name = cash('#input-name').val();
            let password = cash('#input-password').val();
            let rememberMe = cash('#input-remember-me').is(':checked');

            // Affiche un loader
            cash('#btn-login').html('<i data-loading-icon="oval" data-color="white" class="w-5 h-5 mx-auto"></i>').svgLoader();

            try {
                await axios.post('{{ route("login") }}', {
                    name: name,
                    password: password,
                    remember_me: rememberMe,
                    _token: '{{ csrf_token() }}'
                });
                // Redirection en cas de succès
                window.location.href = '{{ route("page", ["layout" => "side-menu", "theme" => "light", "pageName" => "dashboard"]) }}';

            } catch (err) {
                cash('#btn-login').html('Se connecter');

                if (err.response.status === 422) {
                    let errors = err.response.data.errors || {};
                    for (const [key, val] of Object.entries(errors)) {
                        cash(`#input-${key}`).addClass('border-theme-6');
                        cash(`#error-${key}`).html(val[0]);
                    }

                    if (err.response.data.message && !errors.email && !errors.password) {
                        cash(`#input-password`).addClass('border-theme-6');
                        cash(`#error-password`).html(err.response.data.message);
                    }
                } else {
                    cash(`#input-password`).addClass('border-theme-6');
                    cash(`#error-password`).html('Erreur serveur, veuillez réessayer plus tard.');
                }
            }
        }

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
@endsection

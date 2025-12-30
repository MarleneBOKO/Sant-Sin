@extends('layout.' . $layout)

@section('head')
    <title>Changement de mot de passe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .password-requirements {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #6c757d;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .requirement {
            display: flex;
            align-items: center;
            margin: 8px 0;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .requirement.valid { color: #28a745; font-weight: 500; }
        .requirement.invalid { color: #dc3545; }

        .requirement i {
            margin-right: 8px;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .requirement.valid i { transform: scale(1.2); }

        /* Wrapper pour l'input de mot de passe */
        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Bouton pour afficher/masquer le mot de passe */
        .toggle-password {
            position: absolute;
            right: 15px;
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s ease;
            z-index: 10; /* S'assure que l'icône est au-dessus de l'input */
        }

        .toggle-password:hover {
            color: #1a202c;
        }
    </style>
@endsection

@section('body')
    <div class="sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white dark:bg-dark-1 xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">

                    @if(isset($isFirstTime) && $isFirstTime)
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded">
                            <p class="font-bold">🔐 Première connexion</p>
                            <p class="text-sm mt-2">Vous devez changer votre mot de passe avant d'accéder à l'application.</p>
                        </div>
                    @endif

                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
                        Changement de mot de passe
                    </h2>

                    @if ($errors->any())
                        <div class="alert alert-danger mt-5 bg-red-50 border border-red-200 rounded p-4 text-red-600 text-sm">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('password.change') }}" method="POST" id="changePasswordForm">
                        @csrf

                        {{-- Mot de passe actuel --}}
                        <div class="intro-x mt-8">
                            <label class="block text-sm font-medium mb-2">Mot de passe actuel <span class="text-red-500">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="current_password" id="current_password" class="login__input input input--lg border border-gray-300 block w-full pr-10 rounded-lg" placeholder="Mot de passe actuel" required>
                                <i class="toggle-password fas fa-eye" data-target="current_password"></i>
                            </div>
                        </div>

                        {{-- Nouveau mot de passe --}}
                        <div class="intro-x mt-4">
                            <label class="block text-sm font-medium mb-2">Nouveau mot de passe <span class="text-red-500">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="password" id="password" class="login__input input input--lg border border-gray-300 block w-full pr-10 rounded-lg" placeholder="Nouveau mot de passe" required>
                                <i class="toggle-password fas fa-eye" data-target="password"></i>
                            </div>
                        </div>

                        {{-- Confirmation --}}
                        <div class="intro-x mt-4">
                            <label class="block text-sm font-medium mb-2">Confirmer le nouveau mot de passe <span class="text-red-500">*</span></label>
                            <div class="password-input-wrapper">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="login__input input input--lg border border-gray-300 block w-full pr-10 rounded-lg" placeholder="Confirmez" required>
                                <i class="toggle-password fas fa-eye" data-target="password_confirmation"></i>
                            </div>
                        </div>

                        {{-- Liste des exigences (HTML inchangé) --}}
                        <div class="password-requirements intro-x mt-5">
                            <h4 class="font-semibold mb-3">✅ Exigences du mot de passe :</h4>
                            <div class="requirement invalid" id="req-length"><i class="fas fa-times-circle"></i><span>Au moins 12 caractères</span></div>
                            <div class="requirement invalid" id="req-uppercase"><i class="fas fa-times-circle"></i><span>Au moins une majuscule</span></div>
                            <div class="requirement invalid" id="req-lowercase"><i class="fas fa-times-circle"></i><span>Au moins une minuscule</span></div>
                            <div class="requirement invalid" id="req-number"><i class="fas fa-times-circle"></i><span>Au moins un chiffre</span></div>
                            <div class="requirement invalid" id="req-special"><i class="fas fa-times-circle"></i><span>Au moins un caractère spécial (!@#$%^&*)</span></div>
                            <div class="requirement invalid" id="req-match"><i class="fas fa-times-circle"></i><span>Les mots de passe correspondent</span></div>
                        </div>

                        <div class="intro-x mt-5 text-center xl:text-left">
                            <button type="submit" id="submitBtn" class="button button--lg w-full xl:w-32 text-white bg-theme-1 rounded-lg opacity-50 cursor-not-allowed" disabled>
                                ✓ Valider
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Logique Afficher/Masquer
            $('.toggle-password').on('click', function() {
                const targetId = $(this).data('target');
                const input = $('#' + targetId);

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // Logique de validation (Inchangée mais vérifiée)
            function validatePassword() {
                const password = $('#password').val();
                const confirm = $('#password_confirmation').val();

                const requirements = {
                    length: password.length >= 12,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /[0-9]/.test(password),
                    special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
                    match: password === confirm && password.length > 0
                };

                Object.keys(requirements).forEach(req => {
                    const element = $(`#req-${req}`);
                    if (requirements[req]) {
                        element.addClass('valid').removeClass('invalid');
                        element.find('i').removeClass('fa-times-circle').addClass('fa-check-circle');
                    } else {
                        element.addClass('invalid').removeClass('valid');
                        element.find('i').removeClass('fa-check-circle').addClass('fa-times-circle');
                    }
                });

                const allValid = Object.values(requirements).every(v => v);
                $('#submitBtn').prop('disabled', !allValid);
                if (allValid) {
                    $('#submitBtn').removeClass('opacity-50 cursor-not-allowed').addClass('bg-theme-1');
                } else {
                    $('#submitBtn').addClass('opacity-50 cursor-not-allowed');
                }
            }

            $('#password, #password_confirmation').on('input', validatePassword);
        });
    </script>
@endsection

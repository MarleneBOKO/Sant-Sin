<!-- BEGIN: Top Bar -->
<div class="top-bar">
    <!-- BEGIN: Breadcrumb -->
<!-- BEGIN: Breadcrumb -->
<div class="-intro-x breadcrumb mr-auto hidden sm:flex">
    <a href="" class="">Application</a>
    <i data-feather="chevron-right" class="breadcrumb__icon"></i>
    <a href="" class="breadcrumb--active">{{ ucfirst(str_replace('-', ' ', $page_name ?? 'Dashboard')) }}</a>
</div>
<!-- END: Breadcrumb -->

    <!-- END: Breadcrumb -->
    <!-- BEGIN: Search -->
    <div class="intro-x relative mr-3 sm:mr-6">
        <div class="search hidden sm:block">
            <input type="text" class="search__input input placeholder-theme-13" placeholder="Search...">
            <i data-feather="search" class="search__icon dark:text-gray-300"></i>
        </div>
        <a class="notification sm:hidden" href="">
            <i data-feather="search" class="notification__icon dark:text-gray-300"></i>
        </a>
        <div class="search-result">
            <div class="search-result__content">
                <div class="search-result__content__title">Pages</div>
                <div class="mb-5">
                    <a href="" class="flex items-center">
                        <div class="w-8 h-8 bg-theme-18 text-theme-9 flex items-center justify-center rounded-full">
                            <i class="w-4 h-4" data-feather="inbox"></i>
                        </div>
                        <div class="ml-3">Mail Settings</div>
                    </a>
                    <a href="" class="flex items-center mt-2">
                        <div class="w-8 h-8 bg-theme-17 text-theme-11 flex items-center justify-center rounded-full">
                            <i class="w-4 h-4" data-feather="users"></i>
                        </div>
                        <div class="ml-3">Users & Permissions</div>
                    </a>
                    <a href="" class="flex items-center mt-2">
                        <div class="w-8 h-8 bg-theme-14 text-theme-10 flex items-center justify-center rounded-full">
                            <i class="w-4 h-4" data-feather="credit-card"></i>
                        </div>
                        <div class="ml-3">Transactions Report</div>
                    </a>
                </div>
                <div class="search-result__content__title">Users</div>
                <div class="mb-5">
                    @foreach (array_slice($fakers, 0, 4) as $faker)
                        <a href="" class="flex items-center mt-2">
                            <div class="w-8 h-8 image-fit">
                                <img alt="Midone Tailwind HTML Admin Template" class="rounded-full" src="{{ asset('dist/images/' . $faker['photos'][0]) }}">
                            </div>
                            <div class="ml-3">{{ $faker['users'][0]['name'] }}</div>
                            <div class="ml-auto w-48 truncate text-gray-600 text-xs text-right">{{ $faker['users'][0]['email'] }}</div>
                        </a>
                    @endforeach
                </div>
                <div class="search-result__content__title">Products</div>
                @foreach (array_slice($fakers, 0, 4) as $faker)
                    <a href="" class="flex items-center mt-2">
                        <div class="w-8 h-8 image-fit">
                            <img alt="Midone Tailwind HTML Admin Template" class="rounded-full" src="{{ asset('dist/images/' . $faker['images'][0]) }}">
                        </div>
                        <div class="ml-3">{{ $faker['products'][0]['name'] }}</div>
                        <div class="ml-auto w-48 truncate text-gray-600 text-xs text-right">{{ $faker['products'][0]['category'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <!-- END: Search -->
    <!-- BEGIN: Notifications -->
    <div class="intro-x dropdown mr-auto sm:mr-6">
    <div class="dropdown-toggle notification notification--bullet cursor-pointer" id="notification-toggle">
        <i data-feather="bell" class="notification__icon dark:text-gray-300"></i>
        <span class="notification__badge" id="notification-badge" style="display: none;">0</span>
    </div>
    <div class="notification-content pt-2 dropdown-box" id="notification-dropdown">
        <div class="notification-content__box dropdown-box__content box dark:bg-dark-6">
            <div class="flex items-center justify-between px-5 py-3 border-b">
                <div class="notification-content__title font-medium">Notifications</div>
                <button type="button" class="text-xs text-theme-1 hover:text-theme-2" id="mark-all-read">
                    Tout marquer comme lu
                </button>
            </div>

            <div id="notifications-list" class="max-h-96 overflow-y-auto">
                <!-- Les notifications seront chargées ici via AJAX -->
                <div class="text-center py-8 text-gray-500">
                    <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                    <p>Chargement...</p>
                </div>
            </div>


        </div>
    </div>
</div>

    <!-- END: Notifications -->
    <!-- BEGIN: Account Menu -->
    <!-- BEGIN: Account Menu -->
<div class="intro-x dropdown w-8 h-8">
    <div class="dropdown-toggle w-8 h-8 rounded-full overflow-hidden shadow-lg image-fit zoom-in">
        <img alt="Photo de profil" src="{{ asset('dist/images/' . ($authUser->photo ?? 'profil-default.jpg')) }}">
    </div>
    <div class="dropdown-box w-56">
        <div class="dropdown-box__content box bg-theme-38 dark:bg-dark-6 text-white">
            <div class="p-4 border-b border-theme-40 dark:border-dark-3">
                <div class="font-medium">{{ $authUser->name }}</div>
                <div class="text-xs text-theme-41 dark:text-gray-600">{{ $authUser->profil->libelle ?? 'Profil non défini' }}</div>
            </div>
            <div class="p-2">
                <a href="#" class="flex items-center block p-2 hover:bg-theme-1 dark:hover:bg-dark-3 rounded-md">
                    <i data-feather="user" class="w-4 h-4 mr-2"></i> Profil
                </a>

                <a href="{{ route('password.change.form') }}" class="flex items-center block p-2 hover:bg-theme-1 dark:hover:bg-dark-3 rounded-md">
                    <i data-feather="lock" class="w-4 h-4 mr-2"></i> Réinitialiser le mot de passe
                </a>

                <a href="#" class="flex items-center block p-2 hover:bg-theme-1 dark:hover:bg-dark-3 rounded-md">
                    <i data-feather="help-circle" class="w-4 h-4 mr-2"></i> Aide
                </a>
            </div>
            <div class="p-2 border-t border-theme-40 dark:border-dark-3">
                <a href="{{ route('logout') }}" class="flex items-center block p-2 hover:bg-theme-1 dark:hover:bg-dark-3 rounded-md">
                    <i data-feather="toggle-right" class="w-4 h-4 mr-2"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</div>
<!-- END: Account Menu -->

    <!-- END: Account Menu -->
</div>
<!-- END: Top Bar -->
<style>
.notification__badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
}

.notification-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f1f5f9;
    cursor: pointer;
    transition: background 0.2s;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-item.unread {
    background: #eff6ff;
}

.notification-item.priorite-haute {
    border-left: 3px solid #ff4444;
}

/* Force l'affichage pour test */
/* Positionnement correct pour le dropdown dans Midone */
#notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 350px; /* Largeur fixe pour éviter les bugs */
    z-index: 50;
    display: none; /* Laissé à jQuery pour le toggle */
}

/* On s'assure que le contenu est visible */
.notification-content__box {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    background-color: white;
    border-radius: 0.375rem;
}
</style>

<script>
$(document).ready(function() {
    // 1. Charger les notifications au démarrage
    loadNotifications();

    // 2. Recharger toutes les 30 secondes
    setInterval(loadNotifications, 30000);

    // 3. Gestion du clic sur la cloche (Toggle)
    $('#notification-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const dropdown = $('#notification-dropdown');

        // Fermer les autres dropdowns ouverts (compatibilité Midone)
        $('.dropdown-box').not(dropdown).removeClass('show').hide();

        // Basculer l'affichage
        dropdown.toggle().toggleClass('show');

        if (dropdown.is(':visible')) {
            loadNotifications();
        }
    });

    // 4. Fermer si on clique ailleurs sur la page
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#notification-toggle, #notification-dropdown').length) {
            $('#notification-dropdown').hide().removeClass('show');
        }
    });

    // 5. Marquer tout comme lu
    $('#mark-all-read').on('click', function(e) {
        e.stopPropagation();
        $.ajax({
            url: '{{ route("notifications.markAllRead") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadNotifications();
            }
        });
    });

    // 6. Cliquer sur une notification individuelle (Marquer comme lu + Redirection)
    $(document).on('click', '.notification-item', function() {
        const id = $(this).data('id');
        const factureId = $(this).data('facture-id');

        markAsRead(id);

        if (factureId) {
            window.location.href = `/factures/${factureId}`; // Ajustez l'URL selon vos besoins
        }
    });

    // --- FONCTIONS INTERNES ---

    function loadNotifications() {
        $.ajax({
            url: '{{ route("notifications.index") }}',
            method: 'GET',
            success: function(notifications) {
                renderNotifications(notifications);
                updateBadge(notifications);
            },
            error: function(err) {
                console.error("Erreur de chargement des notifications", err);
            }
        });
    }

    function renderNotifications(notifications) {
        const container = $('#notifications-list');
        container.empty();

        if (!notifications || notifications.length === 0) {
            container.html('<div class="p-5 text-center text-gray-500">Aucune notification</div>');
            return;
        }

        let html = '';
        notifications.forEach(notif => {
            const unreadClass = !notif.lue ? 'unread' : '';
            // Sécurité si moment n'est pas encore prêt
            const timeAgo = (typeof moment !== 'undefined') ? moment(notif.created_at).fromNow() : '';

            html += `
                <div class="notification-item ${unreadClass}" data-id="${notif.id}" data-facture-id="${notif.facture_id || ''}">
                    <div class="flex items-start">
                        <div class="flex-1">
                            <div class="font-medium text-gray-800">${notif.titre}</div>
                            <div class="text-sm text-gray-600 mt-1">${notif.message}</div>
                            <div class="text-xs text-gray-500 mt-1">${timeAgo}</div>
                        </div>
                        ${!notif.lue ? '<div class="w-2 h-2 bg-blue-500 rounded-full ml-2 flex-shrink-0"></div>' : ''}
                    </div>
                </div>`;
        });

        container.append(html);

        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }

    function updateBadge(notifications) {
        const unreadCount = notifications.filter(n => !n.lue).length;
        const badge = $('#notification-badge');

        if (unreadCount > 0) {
            badge.text(unreadCount > 99 ? '99+' : unreadCount).show();
        } else {
            badge.hide();
        }
    }

    function markAsRead(id) {
        $.ajax({
            url: `/notifications/${id}/read`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                loadNotifications();
            }
        });
    }
});
</script>

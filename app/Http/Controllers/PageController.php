<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Faker\Factory as Faker;
use Faker\Factory as FakerFactory;
use App\Services\MenuService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Service;
use App\Models\Profil;
use App\Models\LigneSuivi;
use App\Models\Partenaire;
use App\Models\parametres;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



class PageController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

protected $menuService;

public function __construct(MenuService $menuService)
{
    $this->menuService = $menuService;
}



public function loadPage($layout = 'side-menu', $theme = 'light', $pageName = 'dashboard')
{

     $side_menu = $this->menuService->getMenusForAuthenticatedUser();
    $activeMenu = $this->activeMenu($layout, $pageName);
    $fakers = [];
    $faker = FakerFactory::create();
 $time = $faker->time();

 // Extraction des pages actives
    $first_page_name = $side_menu[0]['page_name'] ?? null;
    $second_page_name = null;
    $third_page_name = null;



    for ($i = 0; $i < 10; $i++) {
        $fakers[] = [
            'photos' => ['profile-1.jpg', 'profile-2.jpg', 'profile-3.jpg'],
            'images' => ['product-1.jpg', 'product-2.jpg', 'product-3.jpg'],
            'true_false' => [rand(0, 1)],
             'formatted_times' => [date('H:i', strtotime($time))],
            'totals' => [
                'orders' => rand(100, 1000),
                'purchases' => rand(50, 500),
                'reviews' => rand(10, 100),
            ],

            'stocks' => [rand(1, 100)],

            'files' => [
                [
                    'type' => $faker->randomElement(['Empty Folder', 'Folder', 'Image', 'File']),
                    'file_name' => $faker->randomElement(['report.pdf', 'photo.jpg', 'archive.zip']),
                    'size' => $faker->randomElement(['200 KB', '1.5 MB', '3.2 MB', '875 KB']),
                ]
            ],
            'foods' => [
                [
                    'image' => $faker->randomElement([
                        'preview-1.jpg', 'preview-2.jpg', 'preview-3.jpg', 'preview-4.jpg',
                    ]),
                    'name' => $faker->words(2, true),
                ],
            ],
           'users' => [
                ['name' => $faker->name, 'email' => $faker->email],
                ['name' => $faker->name, 'email' => $faker->email],
                ['name' => $faker->name, 'email' => $faker->email],
            ],

            'dates' => [$faker->date(), $faker->date(), $faker->date()],
            'products' => [
                ['name' => $faker->word, 'category' => $faker->word],
            ],

            'times' => [$faker->time()],
            'news' => [
                [
                    'super_short_content' => $faker->words(3, true),
                     'title' => $faker->sentence(3),
                    'short_content' => $faker->sentence,
                ],
            ],
            'jobs' => [$faker->jobTitle],
            'notification_count' => rand(1, 5), // 🔧 Ajouté pour éviter l’erreur dans chat.blade.php
        ];
    }
if ($pageName === 'gestion-utilisateurs') {
    $users = User::with(['service', 'profil'])->get();
    $services = Service::all(); // <-- Ajouté
    $profils = Profil::all();   // <-- Ajouté

    return view('pages.' . $pageName, [
        'top_menu' => $this->topMenu(),
        'side_menu' => $side_menu,
        'simple_menu' => $this->simpleMenu(),
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'users' => $users,
        'services' => $services, // <-- Ajouté
        'profils' => $profils,   // <-- Ajouté
        'fakers' => $fakers,
    ]);
}

if ($pageName === 'gestion-profils') {
    $profils = Profil::all();  // Ajout de la variable
    return view('pages.' . $pageName, [
        'top_menu' => $this->topMenu(),
        'side_menu' => $side_menu,
        'simple_menu' => $this->simpleMenu(),
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'profils' => $profils,   // <-- Transmission de la variable
        'fakers' => $fakers,
    ]);
}
if ($pageName === 'gestion-factures') {
    $user = auth()->user();
    $profil_id = $user->profil->id ?? null;

    // Début de la requête commune
    $query = LigneSuivi::whereNotNull('Date_Enregistrement')
        ->with(['souscripteur', 'prestataire']);

    // 🔀 Filtrage selon le profil
    if ($profil_id == 4) {
        // Régleur Sinistre Individuel
        $query->whereNotNull('Nom_Assure')
            ->whereNull('Code_Prestataire');
    } elseif ($profil_id == 8) {
        // Régleur Sinistre Tiers Payant
        $query->whereNotNull('Code_Prestataire');
    } elseif (in_array($profil_id, [3, 5])) {
        // Régleur Sinistre ou Admin → voir les deux
        $query->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('Nom_Assure')
                    ->whereNull('Code_Prestataire');
            })->orWhere(function ($sub) {
                $sub->whereNotNull('Code_Prestataire');
            });
        });
    }

    // Récupération des factures (sans filtrage sur 'rejete')
    $factures = $query->get();

    $souscripteurs = Partenaire::souscripteurs()->orderBy('nom')->get();
    $prestataires = Partenaire::prestataires()->orderBy('nom')->get();

    $moisList = DB::table('parametres')
        ->where('typaram', 'MoisFacture')
        ->orderByDesc('codtyparam')
        ->select('codtyparam as Id_mois', 'libelleparam as libelle_mois')
        ->get();

    $annees = DB::table('parametres')
        ->where('typaram', 'AnneFacture')
        ->orderByDesc('codtyparam')
        ->select('codtyparam as Id_annee', 'libelleparam as libelle_annee')
        ->get();

    return view('pages.' . $pageName, [
        'top_menu' => $this->topMenu(),
        'side_menu' => $side_menu,
        'simple_menu' => $this->simpleMenu(),
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'factures' => $factures,
        'souscripteurs' => $souscripteurs,
        'prestataires' => $prestataires,
        'moisList' => $moisList,
        'annees' => $annees,
        'fakers' => $fakers,
        'profil_id' => $profil_id, // utile dans la vue si besoin
    ]);
}



    return view('pages/' . $pageName, [
        'top_menu' =>$this->topMenu(),
        'side_menu' => $side_menu,
        'simple_menu' => $this->simpleMenu(),
        'first_page_name' => $activeMenu['first_page_name'],
        'second_page_name' => $activeMenu['second_page_name'],
        'third_page_name' => $activeMenu['third_page_name'],
        'page_name' => $pageName,
        'theme' => $theme,
        'layout' => $layout,
        'fakers' => $fakers,

    ]);
}


    /**
     * Determine active menu & submenu.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function activeMenu($layout, $pageName)
{
    return [
        'first_page_name' => $pageName,
        'second_page_name' => '',
        'third_page_name' => ''
    ];
}

    /**
     * List of side menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sideMenu()
    {
        return [
            'dashboard' => [
                'icon' => 'home',
                'layout' => 'side-menu',
                'page_name' => 'dashboard',
                'title' => 'Dashboard'
            ],
            'menu-layout' => [
                'icon' => 'box',
                'page_name' => 'menu-layout',
                'title' => 'Menu Layout',
                'sub_menu' => [
                    'side-menu' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Side Menu'
                    ],
                    'simple-menu' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Simple Menu'
                    ],
                    'top-menu' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Top Menu'
                    ]
                ]
            ],
            'inbox' => [
                'icon' => 'inbox',
                'layout' => 'side-menu',
                'page_name' => 'inbox',
                'title' => 'Inbox'
            ],
            'file-manager' => [
                'icon' => 'hard-drive',
                'layout' => 'side-menu',
                'page_name' => 'file-manager',
                'title' => 'File Manager'
            ],
            'point-of-sale' => [
                'icon' => 'credit-card',
                'layout' => 'side-menu',
                'page_name' => 'point-of-sale',
                'title' => 'Point of Sale'
            ],
            'chat' => [
                'icon' => 'message-square',
                'layout' => 'side-menu',
                'page_name' => 'chat',
                'title' => 'Chat'
            ],
            'post' => [
                'icon' => 'file-text',
                'layout' => 'side-menu',
                'page_name' => 'post',
                'title' => 'Post'
            ],
            'devider',
            'crud' => [
                'icon' => 'edit',
                'page_name' => 'crud',
                'title' => 'Crud',
                'sub_menu' => [
                    'crud-data-list' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'crud-data-list',
                        'title' => 'Data List'
                    ],
                    'crud-form' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'crud-form',
                        'title' => 'Form'
                    ]
                ]
            ],
            'users' => [
                'icon' => 'users',
                'page_name' => 'users',
                'title' => 'Users',
                'sub_menu' => [
                    'users-layout-1' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'users-layout-1',
                        'title' => 'Layout 1'
                    ],
                    'users-layout-2' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'users-layout-2',
                        'title' => 'Layout 2'
                    ],
                    'users-layout-3' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'users-layout-3',
                        'title' => 'Layout 3'
                    ]
                ]
            ],
            'profile' => [
                'icon' => 'trello',
                'page_name' => 'profile',
                'title' => 'Profile',
                'sub_menu' => [
                    'profile-overview-1' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'profile-overview-1',
                        'title' => 'Overview 1'
                    ],
                    'profile-overview-2' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'profile-overview-2',
                        'title' => 'Overview 2'
                    ],
                    'profile-overview-3' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'profile-overview-3',
                        'title' => 'Overview 3'
                    ]
                ]
            ],
            'pages' => [
                'icon' => 'layout',
                'page_name' => 'layout',
                'title' => 'Pages',
                'sub_menu' => [
                    'wizards' => [
                        'icon' => '',
                        'page_name' => 'wizards',
                        'title' => 'Wizards',
                        'sub_menu' => [
                            'wizard-layout-1' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'wizard-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'wizard-layout-2' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'wizard-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'wizard-layout-3' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'wizard-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'blog' => [
                        'icon' => '',
                        'page_name' => 'blog',
                        'title' => 'Blog',
                        'sub_menu' => [
                            'blog-layout-1' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'blog-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'blog-layout-2' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'blog-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'blog-layout-3' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'blog-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'pricing' => [
                        'icon' => '',
                        'page_name' => 'pricing',
                        'title' => 'Pricing',
                        'sub_menu' => [
                            'pricing-layout-1' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'pricing-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'pricing-layout-2' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'pricing-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'invoice' => [
                        'icon' => '',
                        'page_name' => 'invoice',
                        'title' => 'Invoice',
                        'sub_menu' => [
                            'invoice-layout-1' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'invoice-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'invoice-layout-2' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'invoice-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'faq' => [
                        'icon' => '',
                        'page_name' => 'faq',
                        'title' => 'FAQ',
                        'sub_menu' => [
                            'faq-layout-1' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'faq-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'faq-layout-2' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'faq-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'faq-layout-3' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'faq-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'login' => [
                        'icon' => '',
                        'layout' => 'login',
                        'page_name' => 'login',
                        'title' => 'Login'
                    ],
                    'register' => [
                        'icon' => '',
                        'layout' => 'login',
                        'page_name' => 'register',
                        'title' => 'Register'
                    ],
                    'error-page' => [
                        'icon' => '',
                        'layout' => 'main',
                        'page_name' => 'error-page',
                        'title' => 'Error Page'
                    ],
                    'update-profile' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'update-profile',
                        'title' => 'Update profile'
                    ],
                    'change-password' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'change-password',
                        'title' => 'Change Password'
                    ]
                ]
            ],
            'devider',
            'components' => [
                'icon' => 'inbox',
                'page_name' => 'components',
                'title' => 'Components',
                'sub_menu' => [
                    'grid' => [
                        'icon' => '',
                        'page_name' => 'grid',
                        'title' => 'Grid',
                        'sub_menu' => [
                            'regular-table' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'regular-table',
                                'title' => 'Regular Table'
                            ],
                            'tabulator' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'tabulator',
                                'title' => 'Tabulator'
                            ]
                        ]
                    ],
                    'accordion' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'accordion',
                        'title' => 'Accordion'
                    ],
                    'button' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'button',
                        'title' => 'Button'
                    ],
                    'modal' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'modal',
                        'title' => 'Modal'
                    ],
                    'alert' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'alert',
                        'title' => 'Alert'
                    ],
                    'progress-bar' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'progress-bar',
                        'title' => 'Progress Bar'
                    ],
                    'tooltip' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'tooltip',
                        'title' => 'Tooltip'
                    ],
                    'dropdown' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'dropdown',
                        'title' => 'Dropdown'
                    ],
                    'toast' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'toast',
                        'title' => 'Toast'
                    ],
                    'typography' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'typography',
                        'title' => 'Typography'
                    ],
                    'icon' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'icon',
                        'title' => 'Icon'
                    ],
                    'loading-icon' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'loading-icon',
                        'title' => 'Loading Icon'
                    ]
                ]
            ],
            'forms' => [
                'icon' => 'sidebar',
                'page_name' => 'forms',
                'title' => 'Forms',
                'sub_menu' => [
                    'regular-form' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'regular-form',
                        'title' => 'Regular Form'
                    ],
                    'datepicker' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'datepicker',
                        'title' => 'Datepicker'
                    ],
                    'tail-select' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'tail-select',
                        'title' => 'Tail Select'
                    ],
                    'file-upload' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'file-upload',
                        'title' => 'File Upload'
                    ],
                    'wysiwyg-editor' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'wysiwyg-editor',
                        'title' => 'Wysiwyg Editor'
                    ],
                    'validation' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'validation',
                        'title' => 'Validation'
                    ]
                ]
            ],
            'widgets' => [
                'icon' => 'hard-drive',
                'page_name' => 'widgets',
                'title' => 'Widgets',
                'sub_menu' => [
                    'chart' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'chart',
                        'title' => 'Chart'
                    ],
                    'slider' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'slider',
                        'title' => 'Slider'
                    ],
                    'image-zoom' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'image-zoom',
                        'title' => 'Image Zoom'
                    ]
                ]
            ]
        ];
    }

    /**
     * List of simple menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function simpleMenu()
    {
        return [
            'dashboard' => [
                'icon' => 'home',
                'layout' => 'simple-menu',
                'page_name' => 'dashboard',
                'title' => 'Dashboard'
            ],
            'menu-layout' => [
                'icon' => 'box',
                'page_name' => 'menu-layout',
                'title' => 'Menu Layout',
                'sub_menu' => [
                    'side-menu' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Side Menu'
                    ],
                    'simple-menu' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Simple Menu'
                    ],
                    'top-menu' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Top Menu'
                    ]
                ]
            ],
            'inbox' => [
                'icon' => 'inbox',
                'layout' => 'simple-menu',
                'page_name' => 'inbox',
                'title' => 'Inbox'
            ],
            'file-manager' => [
                'icon' => 'hard-drive',
                'layout' => 'simple-menu',
                'page_name' => 'file-manager',
                'title' => 'File Manager'
            ],
            'point-of-sale' => [
                'icon' => 'credit-card',
                'layout' => 'simple-menu',
                'page_name' => 'point-of-sale',
                'title' => 'Point of Sale'
            ],
            'chat' => [
                'icon' => 'message-square',
                'layout' => 'simple-menu',
                'page_name' => 'chat',
                'title' => 'Chat'
            ],
            'post' => [
                'icon' => 'file-text',
                'layout' => 'simple-menu',
                'page_name' => 'post',
                'title' => 'Post'
            ],
            'devider',
            'crud' => [
                'icon' => 'edit',
                'page_name' => 'crud',
                'title' => 'Crud',
                'sub_menu' => [
                    'crud-data-list' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'crud-data-list',
                        'title' => 'Data List'
                    ],
                    'crud-form' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'crud-form',
                        'title' => 'Form'
                    ]
                ]
            ],
            'users' => [
                'icon' => 'users',
                'page_name' => 'users',
                'title' => 'Users',
                'sub_menu' => [
                    'users-layout-1' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'users-layout-1',
                        'title' => 'Layout 1'
                    ],
                    'users-layout-2' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'users-layout-2',
                        'title' => 'Layout 2'
                    ],
                    'users-layout-3' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'users-layout-3',
                        'title' => 'Layout 3'
                    ]
                ]
            ],
            'profile' => [
                'icon' => 'trello',
                'page_name' => 'profile',
                'title' => 'Profile',
                'sub_menu' => [
                    'profile-overview-1' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'profile-overview-1',
                        'title' => 'Overview 1'
                    ],
                    'profile-overview-2' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'profile-overview-2',
                        'title' => 'Overview 2'
                    ],
                    'profile-overview-3' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'profile-overview-3',
                        'title' => 'Overview 3'
                    ]
                ]
            ],
            'pages' => [
                'icon' => 'layout',
                'page_name' => 'layout',
                'title' => 'Pages',
                'sub_menu' => [
                    'wizards' => [
                        'icon' => '',
                        'page_name' => 'wizards',
                        'title' => 'Wizards',
                        'sub_menu' => [
                            'wizard-layout-1' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'wizard-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'wizard-layout-2' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'wizard-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'wizard-layout-3' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'wizard-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'blog' => [
                        'icon' => '',
                        'page_name' => 'blog',
                        'title' => 'Blog',
                        'sub_menu' => [
                            'blog-layout-1' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'blog-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'blog-layout-2' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'blog-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'blog-layout-3' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'blog-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'pricing' => [
                        'icon' => '',
                        'page_name' => 'pricing',
                        'title' => 'Pricing',
                        'sub_menu' => [
                            'pricing-layout-1' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'pricing-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'pricing-layout-2' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'pricing-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'invoice' => [
                        'icon' => '',
                        'page_name' => 'invoice',
                        'title' => 'Invoice',
                        'sub_menu' => [
                            'invoice-layout-1' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'invoice-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'invoice-layout-2' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'invoice-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'faq' => [
                        'icon' => '',
                        'page_name' => 'faq',
                        'title' => 'FAQ',
                        'sub_menu' => [
                            'faq-layout-1' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'faq-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'faq-layout-2' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'faq-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'faq-layout-3' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'faq-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'login' => [
                        'icon' => '',
                        'layout' => 'login',
                        'page_name' => 'login',
                        'title' => 'Login'
                    ],
                    'register' => [
                        'icon' => '',
                        'layout' => 'login',
                        'page_name' => 'register',
                        'title' => 'Register'
                    ],
                    'error-page' => [
                        'icon' => '',
                        'layout' => 'main',
                        'page_name' => 'error-page',
                        'title' => 'Error Page'
                    ],
                    'update-profile' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'update-profile',
                        'title' => 'Update profile'
                    ],
                    'change-password' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'change-password',
                        'title' => 'Change Password'
                    ]
                ]
            ],
            'devider',
            'components' => [
                'icon' => 'inbox',
                'page_name' => 'components',
                'title' => 'Components',
                'sub_menu' => [
                    'grid' => [
                        'icon' => '',
                        'page_name' => 'grid',
                        'title' => 'Grid',
                        'sub_menu' => [
                            'regular-table' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'regular-table',
                                'title' => 'Regular Table'
                            ],
                            'tabulator' => [
                                'icon' => '',
                                'layout' => 'simple-menu',
                                'page_name' => 'tabulator',
                                'title' => 'Tabulator'
                            ]
                        ]
                    ],
                    'accordion' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'accordion',
                        'title' => 'Accordion'
                    ],
                    'button' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'button',
                        'title' => 'Button'
                    ],
                    'modal' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'modal',
                        'title' => 'Modal'
                    ],
                    'alert' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'alert',
                        'title' => 'Alert'
                    ],
                    'progress-bar' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'progress-bar',
                        'title' => 'Progress Bar'
                    ],
                    'tooltip' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'tooltip',
                        'title' => 'Tooltip'
                    ],
                    'dropdown' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'dropdown',
                        'title' => 'Dropdown'
                    ],
                    'toast' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'toast',
                        'title' => 'Toast'
                    ],
                    'typography' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'typography',
                        'title' => 'Typography'
                    ],
                    'icon' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'icon',
                        'title' => 'Icon'
                    ],
                    'loading-icon' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'loading-icon',
                        'title' => 'Loading Icon'
                    ]
                ]
            ],
            'forms' => [
                'icon' => 'sidebar',
                'page_name' => 'forms',
                'title' => 'Forms',
                'sub_menu' => [
                    'regular-form' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'regular-form',
                        'title' => 'Regular Form'
                    ],
                    'datepicker' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'datepicker',
                        'title' => 'Datepicker'
                    ],
                    'tail-select' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'tail-select',
                        'title' => 'Tail Select'
                    ],
                    'file-upload' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'file-upload',
                        'title' => 'File Upload'
                    ],
                    'wysiwyg-editor' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'wysiwyg-editor',
                        'title' => 'Wysiwyg Editor'
                    ],
                    'validation' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'validation',
                        'title' => 'Validation'
                    ]
                ]
            ],
            'widgets' => [
                'icon' => 'hard-drive',
                'page_name' => 'widgets',
                'title' => 'Widgets',
                'sub_menu' => [
                    'chart' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'chart',
                        'title' => 'Chart'
                    ],
                    'slider' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'slider',
                        'title' => 'Slider'
                    ],
                    'image-zoom' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'image-zoom',
                        'title' => 'Image Zoom'
                    ]
                ]
            ]
        ];
    }

    /**
     * List of top menu items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function topMenu()
    {
        return [
            'dashboard' => [
                'icon' => 'home',
                'layout' => 'top-menu',
                'page_name' => 'dashboard',
                'title' => 'Dashboard'
            ],
            'menu-layout' => [
                'icon' => 'box',
                'page_name' => 'menu-layout',
                'title' => 'Menu Layout',
                'sub_menu' => [
                    'side-menu' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Side Menu'
                    ],
                    'simple-menu' => [
                        'icon' => '',
                        'layout' => 'simple-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Simple Menu'
                    ],
                    'top-menu' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'dashboard',
                        'title' => 'Top Menu'
                    ]
                ]
            ],
            'apps' => [
                'icon' => 'activity',
                'page_name' => 'apps',
                'title' => 'Apps',
                'sub_menu' => [
                    'users' => [
                        'icon' => 'users',
                        'page_name' => 'users',
                        'title' => 'Users',
                        'sub_menu' => [
                            'users-layout-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'users-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'users-layout-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'users-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'users-layout-3' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'users-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'profile' => [
                        'icon' => 'trello',
                        'page_name' => 'profile',
                        'title' => 'Profile',
                        'sub_menu' => [
                            'profile-overview-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'profile-overview-1',
                                'title' => 'Overview 1'
                            ],
                            'profile-overview-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'profile-overview-2',
                                'title' => 'Overview 2'
                            ],
                            'profile-overview-3' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'profile-overview-3',
                                'title' => 'Overview 3'
                            ]
                        ]
                    ],
                    'inbox' => [
                        'icon' => 'inbox',
                        'layout' => 'top-menu',
                        'page_name' => 'inbox',
                        'title' => 'Inbox'
                    ],
                    'file-manager' => [
                        'icon' => 'folder',
                        'layout' => 'top-menu',
                        'page_name' => 'file-manager',
                        'title' => 'File Manager'
                    ],
                    'point-of-sale' => [
                        'icon' => 'credit-card',
                        'layout' => 'top-menu',
                        'page_name' => 'point-of-sale',
                        'title' => 'Point of Sale'
                    ],
                    'chat' => [
                        'icon' => 'message-square',
                        'layout' => 'top-menu',
                        'page_name' => 'chat',
                        'title' => 'Chat'
                    ],
                    'post' => [
                        'icon' => 'file-text',
                        'layout' => 'top-menu',
                        'page_name' => 'post',
                        'title' => 'Post'
                    ],
                    'crud' => [
                        'icon' => 'edit',
                        'page_name' => 'crud',
                        'title' => 'Crud',
                        'sub_menu' => [
                            'crud-data-list' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'crud-data-list',
                                'title' => 'Data List'
                            ],
                            'crud-form' => [
                                'icon' => '',
                                'layout' => 'side-menu',
                                'page_name' => 'crud-form',
                                'title' => 'Form'
                            ]
                        ]
                    ]
                ]
            ],
            'pages' => [
                'icon' => 'layout',
                'page_name' => 'layout',
                'title' => 'Pages',
                'sub_menu' => [
                    'wizards' => [
                        'icon' => '',
                        'page_name' => 'wizards',
                        'title' => 'Wizards',
                        'sub_menu' => [
                            'wizard-layout-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'wizard-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'wizard-layout-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'wizard-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'wizard-layout-3' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'wizard-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'blog' => [
                        'icon' => '',
                        'page_name' => 'blog',
                        'title' => 'Blog',
                        'sub_menu' => [
                            'blog-layout-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'blog-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'blog-layout-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'blog-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'blog-layout-3' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'blog-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'pricing' => [
                        'icon' => '',
                        'page_name' => 'pricing',
                        'title' => 'Pricing',
                        'sub_menu' => [
                            'pricing-layout-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'pricing-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'pricing-layout-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'pricing-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'invoice' => [
                        'icon' => '',
                        'page_name' => 'invoice',
                        'title' => 'Invoice',
                        'sub_menu' => [
                            'invoice-layout-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'invoice-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'invoice-layout-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'invoice-layout-2',
                                'title' => 'Layout 2'
                            ]
                        ]
                    ],
                    'faq' => [
                        'icon' => '',
                        'page_name' => 'faq',
                        'title' => 'FAQ',
                        'sub_menu' => [
                            'faq-layout-1' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'faq-layout-1',
                                'title' => 'Layout 1'
                            ],
                            'faq-layout-2' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'faq-layout-2',
                                'title' => 'Layout 2'
                            ],
                            'faq-layout-3' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'faq-layout-3',
                                'title' => 'Layout 3'
                            ]
                        ]
                    ],
                    'login' => [
                        'icon' => '',
                        'layout' => 'login',
                        'page_name' => 'login',
                        'title' => 'Login'
                    ],
                    'register' => [
                        'icon' => '',
                        'layout' => 'login',
                        'page_name' => 'register',
                        'title' => 'Register'
                    ],
                    'error-page' => [
                        'icon' => '',
                        'layout' => 'main',
                        'page_name' => 'error-page',
                        'title' => 'Error Page'
                    ],
                    'update-profile' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'update-profile',
                        'title' => 'Update profile'
                    ],
                    'change-password' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'change-password',
                        'title' => 'Change Password'
                    ]
                ]
            ],
            'components' => [
                'icon' => 'inbox',
                'page_name' => 'components',
                'title' => 'Components',
                'sub_menu' => [
                    'grid' => [
                        'icon' => '',
                        'page_name' => 'grid',
                        'title' => 'Grid',
                        'sub_menu' => [
                            'regular-table' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'regular-table',
                                'title' => 'Regular Table'
                            ],
                            'tabulator' => [
                                'icon' => '',
                                'layout' => 'top-menu',
                                'page_name' => 'tabulator',
                                'title' => 'Tabulator'
                            ]
                        ]
                    ],
                    'accordion' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'accordion',
                        'title' => 'Accordion'
                    ],
                    'button' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'button',
                        'title' => 'Button'
                    ],
                    'modal' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'modal',
                        'title' => 'Modal'
                    ],
                    'alert' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'alert',
                        'title' => 'Alert'
                    ],
                    'progress-bar' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'progress-bar',
                        'title' => 'Progress Bar'
                    ],
                    'tooltip' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'tooltip',
                        'title' => 'Tooltip'
                    ],
                    'dropdown' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'dropdown',
                        'title' => 'Dropdown'
                    ],
                    'toast' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'toast',
                        'title' => 'Toast'
                    ],
                    'typography' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'typography',
                        'title' => 'Typography'
                    ],
                    'icon' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'icon',
                        'title' => 'Icon'
                    ],
                    'loading-icon' => [
                        'icon' => '',
                        'layout' => 'side-menu',
                        'page_name' => 'loading-icon',
                        'title' => 'Loading Icon'
                    ]
                ]
            ],
            'forms' => [
                'icon' => 'sidebar',
                'page_name' => 'forms',
                'title' => 'Forms',
                'sub_menu' => [
                    'regular-form' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'regular-form',
                        'title' => 'Regular Form'
                    ],
                    'datepicker' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'datepicker',
                        'title' => 'Datepicker'
                    ],
                    'tail-select' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'tail-select',
                        'title' => 'Tail Select'
                    ],
                    'file-upload' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'file-upload',
                        'title' => 'File Upload'
                    ],
                    'wysiwyg-editor' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'wysiwyg-editor',
                        'title' => 'Wysiwyg Editor'
                    ],
                    'validation' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'validation',
                        'title' => 'Validation'
                    ]
                ]
            ],
            'widgets' => [
                'icon' => 'hard-drive',
                'page_name' => 'widgets',
                'title' => 'Widgets',
                'sub_menu' => [
                    'chart' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'chart',
                        'title' => 'Chart'
                    ],
                    'slider' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'slider',
                        'title' => 'Slider'
                    ],
                    'image-zoom' => [
                        'icon' => '',
                        'layout' => 'top-menu',
                        'page_name' => 'image-zoom',
                        'title' => 'Image Zoom'
                    ]
                ]
            ]
        ];
    }
}

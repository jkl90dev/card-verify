<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="{{ __('CardVerify est une plateforme indépendante et hautement sécurisée de support, d\'activation et de remboursement pour vos coupons et cartes prépayées.') }}">
    <title>{{ $title ?? __('Validation & Remboursement de Vouchers Numériques') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="h-full antialiased text-slate-800 bg-[#f8fafc]"
    x-data="{ showTerms: false, showPrivacy: false, showAbout: false, mobileMenuOpen: false }">
    <div class="min-h-screen flex flex-col">

        <!-- Header / Navigation -->
        <header class="sticky top-0 z-40 w-full border-b border-slate-200 bg-white/95 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Logo Premium -->
                <div class="flex items-center gap-2">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group select-none">
                        <!-- Logo Icon -->
                        <div
                            class="h-10 w-10 flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                            <svg class="h-8 w-8 text-blue-600" viewBox="0 0 32 32" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#3b82f6" />
                                        <stop offset="100%" stop-color="#1d4ed8" />
                                    </linearGradient>
                                    <linearGradient id="shieldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#10b981" />
                                        <stop offset="100%" stop-color="#059669" />
                                    </linearGradient>
                                </defs>
                                <rect x="2" y="6" width="22" height="15" rx="3" fill="url(#logoGrad)"
                                    fill-opacity="0.85" />
                                <line x1="2" y1="11" x2="24" y2="11" stroke="#ffffff" stroke-opacity="0.3"
                                    stroke-width="2" />
                                <rect x="5" y="14" width="4" height="3" rx="0.5" fill="#ffffff" fill-opacity="0.6" />
                                <path d="M23 18.5c0 3.2-2.5 5.5-5.5 6.5-3-1-5.5-3.3-5.5-6.5v-4.5l5.5-2.2 5.5 2.2v4.5z"
                                    fill="#0f172a" stroke="#3b82f6" stroke-width="1.5" stroke-linejoin="round" />
                                <path d="M15 18.5l1.5 1.5 2.5-2.5" stroke="url(#shieldGrad)" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <!-- Logo Text -->
                        <span
                            class="font-black text-xl tracking-tight text-slate-900 group-hover:text-blue-600 transition-colors">Card<span
                                class="text-blue-600 group-hover:text-slate-900 transition-colors">Verify</span></span>
                    </a>
                </div>

                <!-- Navigation Links -->
                @if(!request()->routeIs('agent.chat') && !request()->is('agent/*'))
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#presentation"
                        class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">
                        {{ __('Présentation') }}
                    </a>
                    <a href="#avantages"
                        class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">
                        {{ __('Avantages') }}
                    </a>
                    <a href="#fonctionnement"
                        class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">
                        {{ __('Comment ça marche') }}
                    </a>
                    <a href="#faq" class="text-sm font-medium text-slate-600 hover:text-blue-600 transition-colors">
                        {{ __('Aide & Support') }}
                    </a>
                </nav>
                @endif

                <!-- Droite (CTA Rapide + Langues + Hamburger) -->
                <div class="flex items-center gap-3">
                    <!-- Dropdown de Langue Premium -->
                    <div class="relative hidden md:block" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors cursor-pointer select-none">
                            @if(app()->getLocale() === 'en')
                                <img src="/images/flags/gb.png" alt="English" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span class="uppercase">EN</span>
                            @elseif(app()->getLocale() === 'de')
                                <img src="/images/flags/de.png" alt="Deutsch" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span class="uppercase">DE</span>
                            @elseif(app()->getLocale() === 'es')
                                <img src="/images/flags/es.png" alt="Español" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span class="uppercase">ES</span>
                            @elseif(app()->getLocale() === 'it')
                                <img src="/images/flags/it.png" alt="Italiano" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span class="uppercase">IT</span>
                            @elseif(app()->getLocale() === 'nl')
                                <img src="/images/flags/nl.png" alt="Nederlands" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span class="uppercase">NL</span>
                            @else
                                <img src="/images/flags/fr.png" alt="Français" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span class="uppercase">FR</span>
                            @endif
                            <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1.5 w-36 origin-top-right rounded-lg border border-slate-100 bg-white p-1 shadow-lg ring-1 ring-black/5 z-50" style="display: none;">
                            <a href="{{ route('locale.switch', 'fr') }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <img src="/images/flags/fr.png" alt="Français" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span>Français</span>
                            </a>
                            <a href="{{ route('locale.switch', 'en') }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <img src="/images/flags/gb.png" alt="English" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span>English</span>
                            </a>
                            <a href="{{ route('locale.switch', 'de') }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <img src="/images/flags/de.png" alt="Deutsch" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span>Deutsch</span>
                            </a>
                            <a href="{{ route('locale.switch', 'es') }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <img src="/images/flags/es.png" alt="Español" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span>Español</span>
                            </a>
                            <a href="{{ route('locale.switch', 'it') }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <img src="/images/flags/it.png" alt="Italiano" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span>Italiano</span>
                            </a>
                            <a href="{{ route('locale.switch', 'nl') }}" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                <img src="/images/flags/nl.png" alt="Nederlands" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                                <span>Nederlands</span>
                            </a>
                        </div>
                    </div>

                    <!-- Bouton Déconnexion pour l'Agent -->
                    @if((request()->routeIs('agent.chat') || request()->is('agent/*')) && session('chat_agent_authorized') === true)
                        <a href="{{ url('/agent/chat-dashboard?logout=1') }}" 
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-250 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition-colors cursor-pointer select-none">
                            🚪 {{ __('Déconnexion') }}
                        </a>
                    @endif

                    <!-- Hamburger Button -->
                    @if(!request()->routeIs('agent.chat') && !request()->is('agent/*'))
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden inline-flex items-center justify-center rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors cursor-pointer" aria-label="Menu principal">
                        <!-- Menu Icon (Hamburger) -->
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <!-- Close Icon -->
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Mobile Menu Dropdown -->
            @if(!request()->routeIs('agent.chat') && !request()->is('agent/*'))
            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden border-t border-slate-100 bg-white px-4 py-3 space-y-1 shadow-inner"
                 style="display: none;">
                <a href="#presentation" @click="mobileMenuOpen = false" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    {{ __('Présentation') }}
                </a>
                <a href="#avantages" @click="mobileMenuOpen = false" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    {{ __('Avantages') }}
                </a>
                <a href="#fonctionnement" @click="mobileMenuOpen = false" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    {{ __('Comment ça marche') }}
                </a>
                <a href="#faq" @click="mobileMenuOpen = false" class="block rounded-lg px-3 py-2 text-base font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                    {{ __('Aide & FAQ') }}
                </a>

                <!-- Sélecteur de langue mobile -->
                <div class="border-t border-slate-100 pt-3 mt-3">
                    <span class="block px-3 text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">{{ __('Langue') }}</span>
                    <div class="grid grid-cols-3 gap-2 px-3">
                        <a href="{{ route('locale.switch', 'fr') }}" class="flex items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'fr' ? 'border-blue-500 bg-blue-50/50 text-blue-600' : 'border-slate-200 text-slate-700' }}">
                            <img src="/images/flags/fr.png" alt="FR" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                            <span>FR</span>
                        </a>
                        <a href="{{ route('locale.switch', 'en') }}" class="flex items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'en' ? 'border-blue-500 bg-blue-50/50 text-blue-600' : 'border-slate-200 text-slate-700' }}">
                            <img src="/images/flags/gb.png" alt="EN" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                            <span>EN</span>
                        </a>
                        <a href="{{ route('locale.switch', 'de') }}" class="flex items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'de' ? 'border-blue-500 bg-blue-50/50 text-blue-600' : 'border-slate-200 text-slate-700' }}">
                            <img src="/images/flags/de.png" alt="DE" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                            <span>DE</span>
                        </a>
                        <a href="{{ route('locale.switch', 'es') }}" class="flex items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'es' ? 'border-blue-500 bg-blue-50/50 text-blue-600' : 'border-slate-200 text-slate-700' }}">
                            <img src="/images/flags/es.png" alt="ES" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                            <span>ES</span>
                        </a>
                        <a href="{{ route('locale.switch', 'it') }}" class="flex items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'it' ? 'border-blue-500 bg-blue-50/50 text-blue-600' : 'border-slate-200 text-slate-700' }}">
                            <img src="/images/flags/it.png" alt="IT" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                            <span>IT</span>
                        </a>
                        <a href="{{ route('locale.switch', 'nl') }}" class="flex items-center justify-center gap-1.5 rounded-lg border py-2 text-xs font-semibold hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'nl' ? 'border-blue-500 bg-blue-50/50 text-blue-600' : 'border-slate-200 text-slate-700' }}">
                            <img src="/images/flags/nl.png" alt="NL" class="h-3.5 w-5 rounded-sm object-cover shadow-sm">
                            <span>NL</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Footer Complet & Assorti (Thème Sombre Premium) -->
        <div class="h-1.5 w-full bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-700"></div>
        <footer class="bg-slate-950 text-slate-400 border-t border-slate-900 pt-16 pb-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Col 1: Brand & Desc -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="flex items-center gap-2.5">
                            <!-- Logo Icon -->
                            <div class="h-8 w-8 flex items-center justify-center">
                                <svg class="h-8 w-8 text-blue-500" viewBox="0 0 32 32" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <linearGradient id="logoGradFooter" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#3b82f6" />
                                            <stop offset="100%" stop-color="#1d4ed8" />
                                        </linearGradient>
                                        <linearGradient id="shieldGradFooter" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#10b981" />
                                            <stop offset="100%" stop-color="#059669" />
                                        </linearGradient>
                                    </defs>
                                    <rect x="2" y="6" width="22" height="15" rx="3" fill="url(#logoGradFooter)"
                                        fill-opacity="0.85" />
                                    <line x1="2" y1="11" x2="24" y2="11" stroke="#ffffff" stroke-opacity="0.3"
                                        stroke-width="2" />
                                    <rect x="5" y="14" width="4" height="3" rx="0.5" fill="#ffffff"
                                        fill-opacity="0.6" />
                                    <path
                                        d="M23 18.5c0 3.2-2.5 5.5-5.5 6.5-3-1-5.5-3.3-5.5-6.5v-4.5l5.5-2.2 5.5 2.2v4.5z"
                                        fill="#020617" stroke="#3b82f6" stroke-width="1.5" stroke-linejoin="round" />
                                    <path d="M15 18.5l1.5 1.5 2.5-2.5" stroke="url(#shieldGradFooter)" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span class="font-extrabold text-lg tracking-tight text-white">Card<span
                                    class="text-blue-500">Verify</span></span>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                            {{ __('CardVerify est une plateforme indépendante et hautement sécurisée de support, d\'activation et de remboursement pour vos coupons et cartes prépayées.') }}
                        </p>
                    </div>

                    <!-- Col 2: Liens du site -->
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">{{ __('Plan du site') }}</h4>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="#presentation"
                                    class="hover:text-blue-400 text-slate-400 transition-colors">{{ __('Accueil / Présentation') }}</a></li>
                            <li><a href="#fonctionnement"
                                    class="hover:text-blue-400 text-slate-400 transition-colors">{{ __('Comment ça marche ?') }}</a>
                            </li>
                            <li><a href="#avantages" class="hover:text-blue-400 text-slate-400 transition-colors">{{ __('Nos Avantages') }}</a></li>
                            <li><a href="#ou-acheter" class="hover:text-blue-400 text-slate-400 transition-colors">{{ __('Où Acheter ?') }}</a></li>
                        </ul>
                    </div>

                    <!-- Col 3: Informations Légales -->
                    <div>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">{{ __('Informations') }}</h4>
                        <ul class="space-y-2.5 text-xs text-slate-400">
                            <li><button @click="showTerms = true"
                                    class="hover:text-blue-400 text-slate-400 transition-colors text-left focus:outline-none cursor-pointer">{{ __('Conditions d\'utilisation') }}</button></li>
                            <li><button @click="showPrivacy = true"
                                    class="hover:text-blue-400 text-slate-400 transition-colors text-left focus:outline-none cursor-pointer">{{ __('Politique de confidentialité') }}</button></li>
                            <li><button @click="showAbout = true"
                                    class="hover:text-blue-400 text-slate-400 transition-colors text-left focus:outline-none cursor-pointer">{{ __('Nous Connaître') }}</button></li>
                            <li><a href="#faq" class="hover:text-blue-400 text-slate-400 transition-colors">{{ __('Aide & FAQ') }}</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Footer bottom -->
                <div
                    class="mt-12 pt-8 border-t border-slate-900 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-500">&copy; {{ date('Y') }} CardVerify. {{ __('Tous droits réservés. Service de vérification de vouchers indépendant.') }}</p>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] text-slate-650 font-bold uppercase tracking-wider">{{ __('Sécurisé par') }}</span>
                        <div
                            class="px-2 py-0.5 bg-slate-900/80 rounded border border-slate-800/50 text-[9px] font-black text-slate-400">
                            SSL 256-BIT</div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Modales Légales -->
        <!-- 1. Conditions d'utilisation -->
        <div x-show="showTerms" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            style="display: none;">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]"
                @click.away="showTerms = false">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Conditions d\'utilisation') }}</h3>
                    <button @click="showTerms = false"
                        class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer focus:outline-none">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto text-sm text-slate-600 space-y-4">
                    <p class="font-semibold text-slate-800">1. Présentation du service</p>
                    <p>CardVerify propose un service indépendant de vérification, d'activation et d'assistance au
                        remboursement pour divers types de recharges et de coupons prépayés (Transcash, PCS, Neosurf,
                        Amazon, Steam, etc.). Nous ne sommes pas affiliés aux émetteurs officiels des coupons.</p>

                    <p class="font-semibold text-slate-800">2. Utilisation et exactitude des informations</p>
                    <p>En soumettant un coupon sur notre site, vous confirmez être le propriétaire légitime de ce coupon
                        et garantissez l'exactitude des informations saisies (nom, e-mail, téléphone, montant et code).
                        Toute tentative de soumission de codes frauduleux fera l'objet d'un signalement aux autorités
                        compétentes.</p>

                    <p class="font-semibold text-slate-800">3. Processus de validation</p>
                    <p>Nos agents procèdent à une vérification manuelle auprès des réseaux émetteurs. Le traitement
                        standard prend en moyenne moins de 2 heures pendant les heures d'ouverture (7j/7, de 8h à 22h).
                        Nous déclinons toute responsabilité en cas de retard causé par une panne des réseaux
                        partenaires.</p>

                    <p class="font-semibold text-slate-800">4. Limitation de responsabilité</p>
                    <p>CardVerify fournit un service d'assistance de bonne foi. Nous ne saurions être tenus responsables
                        des pertes financières résultant de l'utilisation de coupons sur des plateformes tierces non
                        sécurisées, ou de l'invalidation d'un coupon par son émetteur d'origine.</p>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button @click="showTerms = false"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">{{ __('Fermer') }}</button>
                </div>
            </div>
        </div>

        <!-- 2. Politique de confidentialité -->
        <div x-show="showPrivacy" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            style="display: none;">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]"
                @click.away="showPrivacy = false">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Politique de Confidentialité') }}</h3>
                    <button @click="showPrivacy = false"
                        class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer focus:outline-none">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto text-sm text-slate-600 space-y-4">
                    <p class="font-semibold text-slate-800">1. Collecte des données personnelles</p>
                    <p>We collect the data that you voluntarily provide when submitting a request: your name/nickname, email address, phone number (for activation), type of voucher, amount, and security code.</p>

                    <p class="font-semibold text-slate-800">2. Utilisation de vos données</p>
                    <p>Vos informations sont utilisées exclusivement pour traiter votre demande d'activation ou de
                        remboursement, effectuer la vérification manuelle et vous envoyer les confirmations d'état par
                        e-mail ou téléphone.</p>

                    <p class="font-semibold text-slate-800">3. Sécurité des codes et transmission</p>
                    <p>Toutes les données échangées transitent par des connexions chiffrées SSL/HTTPS. Vos codes de
                        coupons sont transmis directement à nos agents pour traitement. Nous ne stockons pas
                        vos codes actifs à long terme dans une base de données publique.</p>

                    <p class="font-semibold text-slate-800">4. Partage et conservation</p>
                    <p>Vos informations personnelles ne sont jamais revendues ni partagées avec des tiers à des fins
                        publicitaires. Elles sont conservées le temps nécessaire au traitement et à la finalisation de
                        la demande, puis purgées conformément à la réglementation RGPD.</p>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button @click="showPrivacy = false"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">{{ __('Fermer') }}</button>
                </div>
            </div>
        </div>

        <!-- 3. Nous Connaître -->
        <div x-show="showAbout" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
            style="display: none;">
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]"
                @click.away="showAbout = false">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('À Propos de CardVerify') }}</h3>
                    <button @click="showAbout = false"
                        class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer focus:outline-none">&times;</button>
                </div>
                <div class="p-6 overflow-y-auto text-sm text-slate-600 space-y-4">
                    <p class="font-semibold text-slate-800">Qui sommes-nous ?</p>
                    <p>CardVerify est un service d'assistance et de vérification indépendant né de la volonté de
                        sécuriser l'usage des coupons de recharge prépayés. Notre rôle est d'agir comme un intermédiaire
                        de confiance pour valider l'authenticité de vos codes et vous assister dans l'obtention de
                        remboursements.</p>

                    <p class="font-semibold text-slate-800">Notre Mission</p>
                    <p>Simplifier les démarches de vérification pour les utilisateurs de cartes cadeaux. Grâce à notre
                        équipe d'agents dédiés et nos outils de communication sécurisés, nous permettons d'éviter les
                        arnaques en validant vos coupons en toute sérénité.</p>

                    <p class="font-semibold text-slate-800 font-medium">Nos Engagements</p>
                    <ul class="list-disc pl-5 space-y-2">
                        <li><strong>Rapidité :</strong> Examen de vos demandes en moins de 2 minutes.</li>
                        <li><strong>Confidentialité :</strong> Vos codes de coupons ne sont pas exposés à des bases de
                            données publiques non sécurisées.</li>
                        <li><strong>Support Humain :</strong> Une équipe de professionnels répond à vos besoins et
                            effectue la liaison avec les émetteurs.</li>
                    </ul>
                </div>
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
                    <button @click="showAbout = false"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors cursor-pointer">{{ __('Fermer') }}</button>
                </div>
            </div>
        </div>

    </div>

    <livewire:chat-widget />

    @livewireScripts
</body>

</html>
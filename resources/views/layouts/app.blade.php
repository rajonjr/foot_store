<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#11120f">
        <meta name="description" content="ONZE — maillots de football officiels, rétro et éditions limitées avec flocage personnalisé.">

        <title>{{ $title ?? 'ONZE — Le vestiaire football' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,600;0,700;0,800;0,900;1,700&family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-canvas font-sans text-ink antialiased selection:bg-acid selection:text-ink">
        <a href="#contenu" class="fixed left-3 top-3 z-[100] -translate-y-24 bg-acid px-4 py-3 text-sm font-extrabold text-ink transition-transform focus:translate-y-0">
            Aller au contenu
        </a>

        <div class="bg-ink px-4 py-2 text-center text-[0.625rem] font-extrabold uppercase tracking-[0.18em] text-white sm:text-[0.6875rem]">
            Livraison offerte dès 120 €
            <span class="mx-2 text-acid" aria-hidden="true">/</span>
            Retours prolongés 30 jours
        </div>

        <header class="sticky top-0 z-40 border-b border-ink/10 bg-white/95 backdrop-blur-md">
            <div class="mx-auto flex h-16 max-w-screen-2xl items-center justify-between gap-5 px-4 sm:px-6 lg:px-10">
                <a href="/" class="flex shrink-0 items-center gap-2" aria-label="ONZE, accueil">
                    <span class="flex size-8 items-center justify-center bg-acid font-display text-lg font-black italic text-ink">11</span>
                    <span>
                        <span class="block font-display text-2xl font-black uppercase leading-[0.75] tracking-[-0.04em]">ONZE</span>
                        <span class="mt-1 block text-[0.45rem] font-extrabold uppercase leading-none tracking-[0.28em] text-muted">Le vestiaire</span>
                    </span>
                </a>

                <nav class="hidden h-full items-center gap-7 lg:flex" aria-label="Navigation principale">
                    <a href="/shop?collection=nouveautes" class="flex h-full items-center border-b-2 border-blue text-xs font-extrabold uppercase tracking-[0.11em] text-ink">Nouveautés</a>
                    <a href="/shop?type=clubs" class="flex h-full items-center border-b-2 border-transparent text-xs font-extrabold uppercase tracking-[0.11em] text-ink transition-colors hover:text-blue">Clubs</a>
                    <a href="/shop?type=selections" class="flex h-full items-center border-b-2 border-transparent text-xs font-extrabold uppercase tracking-[0.11em] text-ink transition-colors hover:text-blue">Sélections</a>
                    <a href="/shop?collection=retro" class="flex h-full items-center border-b-2 border-transparent text-xs font-extrabold uppercase tracking-[0.11em] text-ink transition-colors hover:text-blue">Rétro</a>
                    <a href="/shop?collection=limitees" class="flex h-full items-center gap-2 border-b-2 border-transparent text-xs font-extrabold uppercase tracking-[0.11em] text-ink transition-colors hover:text-blue">
                        Limités
                        <span class="bg-acid px-1.5 py-0.5 text-[0.5rem] tracking-wider">Drop</span>
                    </a>
                </nav>

                <div class="flex shrink-0 items-center gap-1">
                    <button type="button" class="flex size-10 items-center justify-center text-ink transition-colors hover:bg-canvas hover:text-blue" aria-label="Rechercher">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.6"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="1.6"/></svg>
                    </button>
                    <a href="/compte" class="hidden size-10 items-center justify-center text-ink transition-colors hover:bg-canvas hover:text-blue sm:flex" aria-label="Mon compte">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="1.6"/><path d="M5 21c.7-4 3-6 7-6s6.3 2 7 6" stroke="currentColor" stroke-width="1.6"/></svg>
                    </a>
                    <a href="/panier" class="relative flex size-10 items-center justify-center text-ink transition-colors hover:bg-canvas hover:text-blue" aria-label="Panier">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h2l1.6 9.2a2 2 0 0 0 2 1.7h7.9a2 2 0 0 0 1.9-1.4L21 8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" stroke="currentColor" stroke-width="1.6"/></svg>
                        <span
                            class="absolute right-0 top-0 flex size-4 items-center justify-center bg-blue text-[0.5625rem] font-extrabold text-white"
                            x-data="{ count: {{ $panier_count ?? count(session()->get('panier', [])) }} }"
                            x-on:panier-mis-a-jour.window="count = $event.detail.count"
                            x-text="count"
                        >{{ $panier_count ?? count(session()->get('panier', [])) }}</span>
                    </a>
                    <button type="button" class="flex size-10 items-center justify-center text-ink lg:hidden" aria-label="Ouvrir le menu">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18" stroke="currentColor" stroke-width="1.7"/></svg>
                    </button>
                </div>
            </div>
        </header>

        <div id="contenu">
            {{ $slot }}
        </div>

        <footer class="bg-ink text-white">
            <div class="mx-auto flex max-w-screen-2xl flex-col gap-5 px-5 py-8 sm:flex-row sm:items-end sm:justify-between sm:px-8 lg:px-10">
                <div>
                    <p class="font-display text-3xl font-black uppercase tracking-tight">Le football se porte.</p>
                    <p class="mt-1 text-xs text-white/50">© {{ date('Y') }} ONZE. Tous droits réservés.</p>
                </div>
                <nav class="flex flex-wrap gap-x-5 gap-y-2 text-[0.625rem] font-bold uppercase tracking-[0.12em] text-white/60" aria-label="Navigation de pied de page">
                    <a href="/aide" class="hover:text-acid">Aide</a>
                    <a href="/livraison" class="hover:text-acid">Livraison</a>
                    <a href="/retours" class="hover:text-acid">Retours</a>
                    <a href="/contact" class="hover:text-acid">Contact</a>
                    <a href="/mentions-legales" class="hover:text-acid">Mentions légales</a>
                </nav>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>

<?php

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app', ['title' => 'Maillot France Domicile 2026 — ONZE'])] class extends Component
{
    public array $maillot = [];

    public string $taille_selectionnee = '';

    public string $version_selectionnee = 'Supporter';

    public string $flocage_nom = '';

    public string $flocage_numero = '';

    public bool $flocage_actif = false;

    public bool $badge_manche = false;

    public string $image_selectionnee = 'avant';

    public bool $confirmation_visible = false;

    public int $panier_count = 0;

    public function mount(): void
    {
        $this->maillot = [
            'id' => 2026,
            'slug' => 'france-domicile-2026',
            'equipe' => 'Équipe de France',
            'nom' => 'Maillot France Domicile 2026',
            'collection' => 'Coupe du monde 2026',
            'description' => 'Un bleu nuit profond, une coupe pensée pour le mouvement et les détails tricolores qui racontent chaque match.',
            'prix' => [
                'Supporter' => 94.90,
                'Joueur' => 139.90,
            ],
            'versions' => [
                'Supporter' => [
                    'label' => 'Supporter',
                    'detail' => 'Coupe confort · tissu respirant',
                ],
                'Joueur' => [
                    'label' => 'Authentic / Joueur',
                    'detail' => 'Coupe ajustée · ultra-léger',
                ],
            ],
            'tailles' => ['S', 'M', 'L', 'XL', 'XXL'],
            'stock' => [
                'Supporter' => ['S' => 8, 'M' => 12, 'L' => 3, 'XL' => 6, 'XXL' => 2],
                'Joueur' => ['S' => 4, 'M' => 7, 'L' => 5, 'XL' => 2, 'XXL' => 0],
            ],
            'images' => [
                'avant' => [
                    'src' => '/images/jersey-france-front.jpg',
                    'alt' => 'Vue avant du maillot France domicile 2026 bleu nuit',
                    'label' => 'Avant',
                ],
                'dos' => [
                    'src' => '/images/jersey-france-back.jpg',
                    'alt' => 'Vue dos du maillot France domicile 2026 bleu nuit',
                    'label' => 'Dos',
                ],
                'detail' => [
                    'src' => '/images/jersey-france-detail.jpg',
                    'alt' => 'Détail du tissu respirant et du col tricolore',
                    'label' => 'Détail',
                ],
            ],
            'flocage_prix' => 18.00,
            'badge_prix' => 7.00,
        ];

        $this->panier_count = count(session()->get('panier', []));
    }

    #[Computed]
    public function prix(): float
    {
        return (float) ($this->maillot['prix'][$this->version_selectionnee] ?? 0);
    }

    #[Computed]
    public function prixTotal(): float
    {
        return $this->prix
            + ($this->flocage_actif ? (float) $this->maillot['flocage_prix'] : 0)
            + ($this->badge_manche ? (float) $this->maillot['badge_prix'] : 0);
    }

    public function updatedVersionSelectionnee(): void
    {
        if ($this->taille_selectionnee === '') {
            return;
        }

        $stock = $this->maillot['stock'][$this->version_selectionnee][$this->taille_selectionnee] ?? 0;

        if ($stock === 0) {
            $this->taille_selectionnee = '';
        }
    }

    public function updatedFlocageActif(): void
    {
        $this->resetValidation(['flocage_nom', 'flocage_numero']);
    }

    public function selectionnerImage(string $image): void
    {
        if (array_key_exists($image, $this->maillot['images'])) {
            $this->image_selectionnee = $image;
        }
    }

    public function ajouterAuPanier(): void
    {
        $rules = [
            'taille_selectionnee' => [
                'required',
                Rule::in($this->maillot['tailles']),
            ],
            'version_selectionnee' => [
                'required',
                Rule::in(array_keys($this->maillot['versions'])),
            ],
            'badge_manche' => ['boolean'],
        ];

        if ($this->flocage_actif) {
            $rules['flocage_nom'] = ['required', 'string', 'min:2', 'max:12', "regex:/^[\pL\s'-]+$/u"];
            $rules['flocage_numero'] = ['required', 'integer', 'between:0,99'];
        }

        $messages = [
            'taille_selectionnee.required' => 'Choisissez votre taille avant de continuer.',
            'flocage_nom.required' => 'Indiquez le nom à floquer.',
            'flocage_nom.regex' => 'Utilisez uniquement des lettres, espaces, apostrophes ou tirets.',
            'flocage_nom.max' => 'Le nom est limité à 12 caractères.',
            'flocage_numero.required' => 'Indiquez un numéro.',
            'flocage_numero.integer' => 'Le numéro doit contenir uniquement des chiffres.',
            'flocage_numero.between' => 'Choisissez un numéro entre 0 et 99.',
        ];

        $this->validate($rules, $messages);

        $stock = $this->maillot['stock'][$this->version_selectionnee][$this->taille_selectionnee] ?? 0;

        if ($stock < 1) {
            $this->addError('taille_selectionnee', 'Cette taille vient d’être épuisée. Choisissez-en une autre.');

            return;
        }

        $panier = session()->get('panier', []);
        $panier[] = [
            'ligne_id' => (string) Str::uuid(),
            'produit_id' => $this->maillot['id'],
            'nom' => $this->maillot['nom'],
            'taille' => $this->taille_selectionnee,
            'version' => $this->version_selectionnee,
            'flocage' => $this->flocage_actif ? [
                'nom' => Str::upper(trim($this->flocage_nom)),
                'numero' => str_pad($this->flocage_numero, 2, '0', STR_PAD_LEFT),
            ] : null,
            'badge_manche' => $this->badge_manche,
            'prix_unitaire' => $this->prixTotal,
            'quantite' => 1,
        ];

        session()->put('panier', $panier);

        $this->panier_count = count($panier);
        $this->confirmation_visible = true;
        $this->dispatch('panier-mis-a-jour', count: $this->panier_count);
    }
};
?>

<div class="min-h-screen bg-canvas text-ink">
    <div class="border-b border-ink/10 bg-white">
        <nav class="mx-auto flex max-w-screen-2xl items-center gap-2 px-4 py-3 text-[0.6875rem] font-bold uppercase tracking-[0.14em] text-muted sm:px-6 lg:px-10" aria-label="Fil d’Ariane">
            <a href="/" class="transition-colors hover:text-ink">Accueil</a>
            <svg class="size-3 text-ink/30" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <a href="/shop" class="transition-colors hover:text-ink">Sélections</a>
            <svg class="size-3 text-ink/30" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="m6 3 5 5-5 5" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="truncate text-ink">France 2026</span>
        </nav>
    </div>

    @if ($confirmation_visible)
        <div
            class="fixed inset-x-4 top-4 z-50 ml-auto max-w-md border border-ink bg-ink p-4 text-white shadow-2xl sm:inset-x-auto sm:right-6 sm:top-6"
            role="status"
            aria-live="polite"
            wire:transition.opacity.duration.200ms
        >
            <div class="flex items-start gap-3">
                <span class="flex size-9 shrink-0 items-center justify-center bg-acid text-ink">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m5 12.5 4.2 4.2L19.5 6.5" stroke="currentColor" stroke-width="2" stroke-linecap="square"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="font-display text-lg font-bold uppercase tracking-wide">Ajouté au vestiaire</p>
                    <p class="mt-0.5 text-sm text-white/65">{{ $taille_selectionnee }} · {{ $version_selectionnee }} — {{ number_format($this->prixTotal, 2, ',', ' ') }} €</p>
                </div>
                <button type="button" class="p-1 text-white/60 transition-colors hover:text-white" wire:click="$set('confirmation_visible', false)" aria-label="Fermer la notification">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.75"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <main class="mx-auto grid max-w-screen-2xl grid-cols-1 lg:grid-cols-[minmax(0,1.15fr)_minmax(420px,0.85fr)]">
        <section class="border-b border-ink/10 bg-gallery p-4 sm:p-6 lg:sticky lg:top-[73px] lg:h-[calc(100vh-73px)] lg:border-r lg:border-b-0 lg:p-8" aria-label="Galerie produit">
            @php($image = $maillot['images'][$image_selectionnee] ?? $maillot['images']['avant'])

            <div class="relative flex h-full min-h-[29rem] flex-col">
                <div class="absolute left-3 top-3 z-10 flex flex-col items-start gap-2 sm:left-4 sm:top-4">
                    <span class="bg-acid px-3 py-1.5 text-[0.625rem] font-extrabold uppercase tracking-[0.16em] text-ink">Nouveauté</span>
                    <span class="bg-ink px-3 py-1.5 text-[0.625rem] font-extrabold uppercase tracking-[0.16em] text-white">Édition limitée</span>
                </div>

                <button type="button" class="absolute right-3 top-3 z-10 flex size-10 items-center justify-center border border-ink/10 bg-white/90 text-ink transition-all duration-200 hover:-translate-y-0.5 hover:border-ink sm:right-4 sm:top-4" aria-label="Ajouter aux favoris">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" stroke="currentColor" stroke-width="1.6"/>
                    </svg>
                </button>

                <div class="relative flex min-h-0 flex-1 items-center justify-center overflow-hidden">
                    <img
                        src="{{ asset($image['src']) }}"
                        alt="{{ $image['alt'] }}"
                        class="h-full max-h-[71vh] w-full object-contain mix-blend-multiply transition-opacity duration-300"
                        width="1024"
                        height="1024"
                    >

                    @if ($image_selectionnee === 'dos' && $flocage_actif && ($flocage_nom !== '' || $flocage_numero !== ''))
                        <div class="pointer-events-none absolute left-1/2 top-[30%] -translate-x-1/2 text-center text-white drop-shadow-lg" aria-hidden="true">
                            <p class="font-display text-[clamp(0.75rem,2vw,1.35rem)] font-black uppercase tracking-[0.12em]">{{ $flocage_nom ?: 'VOTRE NOM' }}</p>
                            <p class="font-display text-[clamp(2.5rem,7vw,5rem)] font-black leading-none">{{ $flocage_numero ?: '10' }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex items-end justify-between gap-4">
                    <div class="flex gap-2" role="list" aria-label="Vues du produit">
                        @foreach ($maillot['images'] as $key => $media)
                            <button
                                type="button"
                                wire:key="image-{{ $key }}"
                                wire:click="selectionnerImage('{{ $key }}')"
                                class="group relative size-16 overflow-hidden border bg-white transition-all duration-200 sm:size-20 {{ $image_selectionnee === $key ? 'border-ink ring-1 ring-ink' : 'border-ink/10 hover:border-ink/50' }}"
                                aria-label="Afficher la vue {{ strtolower($media['label']) }}"
                                aria-pressed="{{ $image_selectionnee === $key ? 'true' : 'false' }}"
                            >
                                <img src="{{ asset($media['src']) }}" alt="" class="size-full object-cover mix-blend-multiply transition-transform duration-300 group-hover:scale-105">
                                <span class="absolute inset-x-0 bottom-0 bg-white/90 py-0.5 text-[0.5rem] font-extrabold uppercase tracking-wider text-ink">{{ $media['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <p class="hidden items-center gap-2 text-[0.625rem] font-bold uppercase tracking-[0.16em] text-muted sm:flex">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m13-5h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3m13 5h3a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        Survolez pour explorer
                    </p>
                </div>
            </div>
        </section>

        <section class="bg-white px-5 py-8 sm:px-8 sm:py-10 lg:px-10 xl:px-14 xl:py-12" aria-labelledby="product-title">
            <div class="mx-auto max-w-2xl">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-[0.6875rem] font-extrabold uppercase tracking-[0.18em] text-blue">{{ $maillot['collection'] }}</p>
                    <div class="flex items-center gap-1" aria-label="Note 4,9 sur 5">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="size-3.5 fill-current text-ink" viewBox="0 0 20 20" aria-hidden="true"><path d="m10 1.7 2.5 5 5.5.8-4 3.9.9 5.5-4.9-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8 2.5-5Z"/></svg>
                        @endfor
                        <a href="#avis" class="ml-1 text-xs font-semibold text-muted underline decoration-ink/20 underline-offset-4 hover:text-ink">126 avis</a>
                    </div>
                </div>

                <h1 id="product-title" class="mt-4 max-w-xl font-display text-[clamp(2.8rem,6vw,5.2rem)] font-black uppercase leading-[0.85] tracking-[-0.035em] text-ink">
                    {{ $maillot['nom'] }}
                </h1>

                <div class="mt-5 flex items-end justify-between gap-4 border-b border-ink/10 pb-6">
                    <div>
                        <p class="font-price text-3xl font-semibold tracking-[-0.04em] text-ink">{{ number_format($this->prix, 2, ',', ' ') }} €</p>
                        <p class="mt-1 text-xs text-muted">TVA incluse · paiement en 3× sans frais</p>
                    </div>
                    <span class="flex items-center gap-2 text-xs font-bold text-success">
                        <span class="size-2 bg-success" aria-hidden="true"></span>
                        En stock
                    </span>
                </div>

                <p class="mt-6 max-w-xl text-sm leading-6 text-muted sm:text-base">{{ $maillot['description'] }}</p>

                <form wire:submit="ajouterAuPanier" class="mt-8 space-y-8" novalidate>
                    <fieldset>
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <legend class="text-xs font-extrabold uppercase tracking-[0.15em] text-ink">1. Choisissez votre version</legend>
                            <span class="text-[0.6875rem] font-medium text-muted">Prix actualisé instantanément</span>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach ($maillot['versions'] as $key => $version)
                                <label class="relative cursor-pointer" wire:key="version-{{ $key }}">
                                    <input class="peer sr-only" type="radio" name="version" value="{{ $key }}" wire:model.live="version_selectionnee">
                                    <span class="block min-h-24 border border-ink/15 p-4 transition-all duration-200 hover:border-ink peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue">
                                        <span class="flex items-start justify-between gap-3">
                                            <span>
                                                <span class="block text-sm font-extrabold">{{ $version['label'] }}</span>
                                                <span class="mt-1 block text-xs leading-5 opacity-60">{{ $version['detail'] }}</span>
                                            </span>
                                            <span class="mt-0.5 block size-4 border border-current p-[3px]">
                                                <span class="block size-full bg-current opacity-0 peer-checked:opacity-100"></span>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <legend class="text-xs font-extrabold uppercase tracking-[0.15em] text-ink">2. Sélectionnez votre taille</legend>
                            <button type="button" class="text-xs font-semibold text-muted underline decoration-ink/20 underline-offset-4 transition-colors hover:text-ink">Guide des tailles</button>
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            @foreach ($maillot['tailles'] as $taille)
                                @php($stock = $maillot['stock'][$version_selectionnee][$taille] ?? 0)
                                <label class="relative {{ $stock === 0 ? 'cursor-not-allowed' : 'cursor-pointer' }}" wire:key="taille-{{ $version_selectionnee }}-{{ $taille }}">
                                    <input
                                        class="peer sr-only"
                                        type="radio"
                                        name="taille"
                                        value="{{ $taille }}"
                                        wire:model.live="taille_selectionnee"
                                        @disabled($stock === 0)
                                    >
                                    <span class="relative flex h-12 items-center justify-center border border-ink/15 text-sm font-extrabold transition-all duration-200 hover:border-ink peer-checked:border-ink peer-checked:bg-ink peer-checked:text-white peer-disabled:bg-canvas peer-disabled:text-ink/25 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue">
                                        {{ $taille }}
                                        @if ($stock === 0)
                                            <span class="absolute h-px w-7 -rotate-45 bg-ink/20" aria-hidden="true"></span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('taille_selectionnee')
                            <p class="mt-2 flex items-center gap-2 text-xs font-semibold text-error" role="alert">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v5m0 3.5v.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="square"/></svg>
                                {{ $message }}
                            </p>
                        @enderror

                        @if ($taille_selectionnee && ($maillot['stock'][$version_selectionnee][$taille_selectionnee] ?? 0) <= 3)
                            <p class="mt-2 text-xs font-semibold text-orange-600">Plus que {{ $maillot['stock'][$version_selectionnee][$taille_selectionnee] }} en stock dans cette configuration.</p>
                        @endif
                    </fieldset>

                    <fieldset class="border-y border-ink/10 py-6">
                        <legend class="sr-only">Personnalisation</legend>

                        <label class="flex cursor-pointer items-start justify-between gap-4">
                            <span>
                                <span class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-[0.15em] text-ink">
                                    3. Ajoutez votre flocage
                                    <span class="bg-acid px-1.5 py-0.5 text-[0.5625rem] tracking-[0.1em]">+ {{ number_format($maillot['flocage_prix'], 0, ',', ' ') }} €</span>
                                </span>
                                <span class="mt-1.5 block text-xs leading-5 text-muted">Nom et numéro officiels, appliqués dans notre atelier.</span>
                            </span>
                            <span class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 items-center border border-ink bg-canvas transition-colors has-checked:bg-ink">
                                <input type="checkbox" class="peer sr-only" wire:model.live="flocage_actif">
                                <span class="ml-0.5 size-4 bg-ink transition-transform duration-200 peer-checked:translate-x-5 peer-checked:bg-acid"></span>
                            </span>
                        </label>

                        @if ($flocage_actif)
                            <div class="mt-5 grid grid-cols-[minmax(0,1fr)_7rem] gap-3" wire:transition.opacity.duration.200ms>
                                <label>
                                    <span class="mb-2 block text-[0.625rem] font-bold uppercase tracking-[0.14em] text-muted">Nom sur le maillot</span>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.250ms="flocage_nom"
                                        maxlength="12"
                                        autocomplete="off"
                                        placeholder="EX. MBAPPÉ"
                                        class="h-12 w-full border border-ink/20 bg-canvas px-4 text-sm font-extrabold uppercase outline-none transition-colors placeholder:text-ink/25 focus:border-blue focus:ring-1 focus:ring-blue"
                                        aria-invalid="{{ $errors->has('flocage_nom') ? 'true' : 'false' }}"
                                    >
                                    @error('flocage_nom') <span class="mt-1.5 block text-xs font-semibold text-error" role="alert">{{ $message }}</span> @enderror
                                </label>

                                <label>
                                    <span class="mb-2 block text-[0.625rem] font-bold uppercase tracking-[0.14em] text-muted">Numéro</span>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.250ms="flocage_numero"
                                        maxlength="2"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        placeholder="10"
                                        class="h-12 w-full border border-ink/20 bg-canvas px-4 text-center font-price text-sm font-bold outline-none transition-colors placeholder:text-ink/25 focus:border-blue focus:ring-1 focus:ring-blue"
                                        aria-invalid="{{ $errors->has('flocage_numero') ? 'true' : 'false' }}"
                                    >
                                    @error('flocage_numero') <span class="mt-1.5 block text-xs font-semibold text-error" role="alert">{{ $message }}</span> @enderror
                                </label>

                                <button
                                    type="button"
                                    wire:click="selectionnerImage('dos')"
                                    class="col-span-2 flex items-center gap-2 text-left text-xs font-semibold text-blue transition-colors hover:text-ink"
                                >
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.5"/></svg>
                                    Prévisualiser le dos personnalisé
                                </button>
                            </div>
                        @endif
                    </fieldset>

                    <label class="flex cursor-pointer items-center justify-between gap-4 border border-ink/10 p-4 transition-colors hover:border-ink/35">
                        <span class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center bg-canvas text-ink">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 5 4-2h6l4 2 2.5 5-4 2-1.5-2v11H8V10l-1.5 2-4-2L5 5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M9 3c.4 1.4 1.4 2 3 2s2.6-.6 3-2" stroke="currentColor" stroke-width="1.5"/></svg>
                            </span>
                            <span>
                                <span class="block text-sm font-extrabold text-ink">Badge compétition sur la manche</span>
                                <span class="mt-0.5 block text-xs text-muted">Patch thermocollé premium · + {{ number_format($maillot['badge_prix'], 2, ',', ' ') }} €</span>
                            </span>
                        </span>
                        <span class="relative flex size-5 shrink-0 items-center justify-center border border-ink/30 has-checked:border-ink has-checked:bg-ink">
                            <input type="checkbox" class="peer sr-only" wire:model.live="badge_manche">
                            <svg class="size-3 scale-0 text-acid transition-transform peer-checked:scale-100" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m3 8 3 3 7-7" stroke="currentColor" stroke-width="2"/></svg>
                        </span>
                    </label>

                    <div>
                        <button
                            type="submit"
                            class="group flex h-16 w-full items-center justify-between bg-acid px-5 text-ink transition-all duration-200 hover:-translate-y-1 hover:bg-ink hover:text-white hover:shadow-[0_10px_0_#2458ff] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue disabled:cursor-wait disabled:opacity-70"
                            wire:loading.attr="disabled"
                            wire:target="ajouterAuPanier"
                        >
                            <span class="flex items-center gap-3 font-display text-xl font-black uppercase tracking-[0.04em]">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 5h2l1.6 9.2a2 2 0 0 0 2 1.7h7.9a2 2 0 0 0 1.9-1.4L21 8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm9 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="square"/>
                                </svg>
                                <span wire:loading.remove wire:target="ajouterAuPanier">Ajouter au panier</span>
                                <span wire:loading wire:target="ajouterAuPanier">Ajout en cours…</span>
                            </span>
                            <span class="font-price text-lg font-bold">{{ number_format($this->prixTotal, 2, ',', ' ') }} €</span>
                        </button>

                        <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2 text-[0.6875rem] font-semibold text-muted">
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-ink" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 7h11v10H3V7Zm11 3h4l3 3v4h-7v-7Z" stroke="currentColor" stroke-width="1.5"/><circle cx="7" cy="18" r="2" fill="white" stroke="currentColor" stroke-width="1.5"/><circle cx="18" cy="18" r="2" fill="white" stroke="currentColor" stroke-width="1.5"/></svg>
                                Expédié sous 24/48 h
                            </span>
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-ink" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2-5.3L4 9m0-5v5h5" stroke="currentColor" stroke-width="1.5"/></svg>
                                Retours sous 30 jours
                            </span>
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-ink" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 10V7a5 5 0 0 1 10 0v3M5 10h14v11H5V10Z" stroke="currentColor" stroke-width="1.5"/></svg>
                                Paiement sécurisé
                            </span>
                            <span class="flex items-center gap-2">
                                <svg class="size-4 text-ink" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 2.2 2.2 3.1-.4.4 3.1L20 10l-1.4 2.8.9 3-3 1-1 3-3-.9-2.8 1.4-2.2-2.2-3.1.4-.4-3.1L1.7 13l1.4-2.8-.9-3 3-1 1-3 3 .9L12 3Z" stroke="currentColor" stroke-width="1.4"/><path d="m8.5 11.7 2.1 2.1 4.6-4.6" stroke="currentColor" stroke-width="1.5"/></svg>
                                Produit authentifié
                            </span>
                        </div>
                    </div>
                </form>

                <div class="mt-10 divide-y divide-ink/10 border-t border-ink/10">
                    <details class="group py-5" open>
                        <summary class="flex cursor-pointer list-none items-center justify-between text-xs font-extrabold uppercase tracking-[0.15em]">
                            Détails & composition
                            <svg class="size-4 transition-transform group-open:rotate-45" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.5"/></svg>
                        </summary>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-muted">Tissu technique respirant, 100 % polyester recyclé. Empiècements latéraux ventilés, col côtelé et finition anti-humidité. Lavage à 30 °C, sur l’envers.</p>
                    </details>
                    <details class="group py-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between text-xs font-extrabold uppercase tracking-[0.15em]">
                            Livraison & retours
                            <svg class="size-4 transition-transform group-open:rotate-45" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="1.5"/></svg>
                        </summary>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-muted">Livraison offerte dès 120 €. Les articles personnalisés sont préparés sous 2 à 4 jours ouvrés et ne peuvent être retournés qu’en cas de défaut.</p>
                    </details>
                </div>
            </div>
        </section>
    </main>

    <section class="border-t border-ink bg-blue text-white" aria-label="Nos engagements">
        <div class="mx-auto grid max-w-screen-2xl grid-cols-1 divide-y divide-white/20 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="flex gap-4 p-6 sm:p-8">
                <span class="font-display text-4xl font-black leading-none text-acid">01</span>
                <div><h2 class="font-display text-xl font-black uppercase tracking-wide">Toujours authentique</h2><p class="mt-1 text-xs leading-5 text-white/65">Chaque maillot est contrôlé avant expédition.</p></div>
            </div>
            <div class="flex gap-4 p-6 sm:p-8">
                <span class="font-display text-4xl font-black leading-none text-acid">02</span>
                <div><h2 class="font-display text-xl font-black uppercase tracking-wide">Flocage atelier</h2><p class="mt-1 text-xs leading-5 text-white/65">Une pose précise avec les codes officiels.</p></div>
            </div>
            <div class="flex gap-4 p-6 sm:p-8">
                <span class="font-display text-4xl font-black leading-none text-acid">03</span>
                <div><h2 class="font-display text-xl font-black uppercase tracking-wide">Expédition rapide</h2><p class="mt-1 text-xs leading-5 text-white/65">Préparé à Paris et suivi jusqu’à chez vous.</p></div>
            </div>
        </div>
    </section>
</div>

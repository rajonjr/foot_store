# Architecture e-commerce — ONZE

## 1. Positionnement UX

ONZE est pensé comme un **vestiaire éditorial** plutôt qu'un catalogue générique : visuels très présents, hiérarchie typographique nette, informations de stock immédiates et achat sans friction. La page produit conserve toutes les décisions dans un seul flux — version, taille, flocage, badge — puis affiche un prix total en temps réel.

Principes directeurs :

- **Quick Buy** depuis les cartes catalogue pour les produits simples ; passage par la fiche pour les personnalisations.
- **Mobile-first** avec cible tactile minimale de 44 px, CTA immédiatement identifiable et checkout sans distraction.
- **Réassurance au moment de la décision** : authenticité, délai, retours et paiement restent proches du bouton d'achat.
- **Performance visuelle** : images WebP/AVIF responsives, chargement différé hors écran et placeholders de ratio fixe.
- **Accessibilité** : navigation clavier, vrais `fieldset`/`legend`, libellés persistants, contrastes AA et messages d'erreur annoncés.

## 2. Design system Tailwind CSS v4

La configuration est CSS-first dans `resources/css/app.css` :

```css
@theme {
    --color-ink: #11120f;       /* noir mat principal */
    --color-canvas: #f3f2ec;    /* fond chaud */
    --color-gallery: #e9e9e2;   /* surfaces produit */
    --color-acid: #d8ff3e;      /* CTA / badges new */
    --color-blue: #2458ff;      /* accent électrique */
    --color-muted: #6f726b;     /* texte secondaire */
    --color-success: #167a48;
    --color-error: #c73131;

    --font-display: 'Barlow Condensed', 'Arial Narrow', ui-sans-serif, sans-serif;
    --font-sans: 'Manrope', ui-sans-serif, system-ui, sans-serif;
    --font-price: 'Space Grotesk', ui-monospace, monospace;
}
```

### Typographies

- **Barlow Condensed 800/900** : titres, noms de collections et CTA. Sa largeur condensée donne un rythme de presse sportive sans nuire à l'impact.
- **Manrope 400–800** : navigation, descriptifs, formulaires et checkout. Très lisible sur petits écrans.
- **Space Grotesk 600/700** : prix, quantités et numéros de maillot ; chiffres ouverts et stables.

Alternative auto-hébergée : **Archivo Narrow** pour les titres et **Inter Variable** pour le corps afin d'éviter toute requête Google Fonts en production.

## 3. Carte des pages

### Accueil

1. Header avec méga-menu Clubs / Sélections / Rétro / Éditions limitées.
2. Hero du dernier lancement avec CTA « Découvrir le drop ».
3. Carrousel meilleures ventes chargé en `defer` Livewire.
4. Grille éditoriale des championnats (Ligue 1, Premier League, Liga, Serie A, Bundesliga).
5. Sélection rétro et bandeau de personnalisation.
6. Réassurance : authenticité, expédition 24/48 h, retours 30 jours.
7. Newsletter et contenu social.

### Catalogue `/shop`

- Composant page `pages::shop.index`.
- Filtres réactifs synchronisés avec l'URL : championnat, équipe, type, taille, version, prix et disponibilité.
- Panneau mobile en drawer ; barre latérale persistante sur desktop.
- Grille paginée avec `wire:island` pour limiter les re-rendus et skeletons au changement de filtre.
- Tri nouveautés / prix / popularité.
- Carte `product-card` avec favoris, variantes couleur et Quick Buy.

### Produit `/maillots/{product:slug}`

- Galerie zoomable, vidéo courte optionnelle et aperçu du dos.
- Sélection version / taille avec stock par variante.
- Personnalisation : nom, numéro, badge de manche et aperçu.
- Prix total dynamique, ajout au panier idempotent et recommandations complémentaires.
- Avis, guide de taille, entretien, livraison et retours.

Le prototype est implémenté dans `resources/views/pages/produit/⚡show.blade.php` au format **Single-File Component Livewire v4**.

### Panier `/panier`

- Drawer global pour le retour immédiat et page complète pour l'édition.
- Lignes regroupées par configuration exacte (produit + version + taille + flocage + badge).
- Modification quantité, suppression optimiste, code promotionnel et seuil de livraison offert.
- Recommandations non intrusives (short, chaussettes, patch).

### Checkout `/checkout`

- Tunnel en trois étapes : coordonnées → livraison → paiement.
- Achat invité par défaut, création de compte proposée après paiement.
- Adresses préremplies, validation serveur, récapitulatif sticky desktop.
- Intégration Stripe Payment Element / wallets, webhooks signés et clé d'idempotence.
- Page de confirmation avec suivi, facture et partage du maillot personnalisé.

### Espace client

Commandes, retours, adresses, favoris, alertes de restock et préférences de communication.

### Back-office

Laravel Nova ou Filament pour produits, variantes, stocks, drops, promotions, commandes, retours et contenus éditoriaux.

## 4. Architecture métier recommandée

```text
app/
├── Actions/Cart/                 # AddItem, UpdateQuantity, MergeGuestCart
├── Actions/Checkout/             # CreateOrder, CapturePayment
├── Data/                         # DTO immuables (CartItemData, CustomizationData)
├── Enums/                        # JerseyVersion, OrderStatus, BadgeType
├── Events/                       # CartUpdated, OrderPaid, StockLow
├── Jobs/                         # emails, facture, synchronisation stock
├── Models/                       # Product, Variant, Team, League, Order...
├── Policies/                     # autorisations
├── Services/                     # PricingService, InventoryService, PaymentGateway
└── ValueObjects/                 # Money, Sku, Personalization

resources/views/
├── components/                   # composants Livewire réutilisables
├── layouts/                      # layouts Livewire v4
└── pages/                        # pages routables Route::livewire()
```

### Modèle de données minimal

- `leagues` → `teams` → `products`
- `products` → `product_images`, `product_variants`, `product_badges`
- `product_variants` : `version`, `size`, `sku`, `price`, `stock`, `reserved_stock`
- `carts` → `cart_items` avec JSON de personnalisation normalisé
- `orders` → `order_items`, `payments`, `shipments`, `returns`
- `customers` / `addresses` / `wishlists`

Les prix sont stockés en **centimes entiers**, jamais en flottants. Le composant de démonstration utilise des flottants uniquement parce qu'il est autonome et sans base de données.

## 5. Flux panier et stock

1. Le navigateur envoie uniquement les identifiants de variante et options.
2. `PricingService` recalcule le prix côté serveur ; aucun prix client n'est accepté.
3. `InventoryService` contrôle le stock disponible dans une transaction.
4. Le panier invité est lié à un token signé en session, puis fusionné à la connexion.
5. Au début du paiement, les unités sont réservées avec une expiration courte.
6. Le webhook de paiement confirme la commande ; un job libère les réservations expirées.

## 6. Performance, SEO et qualité

- Cache tags Redis pour navigation, facettes et fiches ; invalidation à la mise à jour produit.
- Recherche Meilisearch/Typesense pour tolérance aux fautes et facettes rapides.
- Images sur stockage objet + CDN, `srcset`, AVIF/WebP et dimensions obligatoires.
- Données structurées `Product`, `Offer`, `BreadcrumbList` et `AggregateRating`.
- Canonicals pour filtres, sitemap segmenté et métadonnées Open Graph par produit.
- Tests de composants Livewire, tests de panier/pricing, tests navigateur du tunnel de commande.
- Observabilité : Laravel Pulse, logs structurés, suivi webhooks et alertes de stock.

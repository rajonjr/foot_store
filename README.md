# ONZE — boutique de maillots de football

Prototype e-commerce construit avec **Laravel 13**, **Livewire 4** (Single-File Component) et **Tailwind CSS 4**. Il présente une fiche produit immersive avec variantes Joueur/Supporter, stocks par taille, flocage personnalisé, badge de manche, galerie et ajout au panier en session.

## Stack

- PHP 8.3+
- Laravel 13
- Livewire 4.4+
- Tailwind CSS 4 + Vite 8
- SQLite par défaut

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

Ouvrir ensuite `http://127.0.0.1:8000`. La racine redirige vers la page produit.

Pour le développement :

```bash
composer run dev
```

## Fichiers principaux

- `resources/views/pages/produit/⚡show.blade.php` — SFC Livewire v4 complet.
- `resources/views/layouts/app.blade.php` — shell e-commerce responsive.
- `resources/css/app.css` — thème Tailwind CSS v4 CSS-first.
- `routes/web.php` — route de page Livewire.
- `docs/architecture-ecommerce.md` — architecture UX, métier et technique recommandée.
- `tests/Feature/ProductPageTest.php` — scénarios fonctionnels de la fiche produit.

## Tests et qualité

```bash
php artisan test
npm run build
```

> Le panier du prototype est volontairement stocké en session. Pour la production, utiliser les modèles et services décrits dans `docs/architecture-ecommerce.md`, recalculer les prix côté serveur en centimes et réserver le stock au checkout.

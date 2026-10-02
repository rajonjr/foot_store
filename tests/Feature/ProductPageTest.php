<?php

namespace Tests\Feature;

use Livewire\Livewire;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    public function test_product_page_is_accessible(): void
    {
        $this->get('/maillots/france-domicile-2026')
            ->assertOk()
            ->assertSee('Maillot France Domicile 2026')
            ->assertSee('Ajouter au panier');
    }

    public function test_price_changes_with_selected_version(): void
    {
        Livewire::test('pages::produit.show')
            ->assertSet('version_selectionnee', 'Supporter')
            ->assertSee('94,90')
            ->set('version_selectionnee', 'Joueur')
            ->assertSee('139,90');
    }

    public function test_size_is_required_before_adding_to_cart(): void
    {
        Livewire::test('pages::produit.show')
            ->call('ajouterAuPanier')
            ->assertHasErrors(['taille_selectionnee' => 'required'])
            ->assertSet('confirmation_visible', false);
    }

    public function test_custom_flocking_is_validated(): void
    {
        Livewire::test('pages::produit.show')
            ->set('taille_selectionnee', 'M')
            ->set('flocage_actif', true)
            ->set('flocage_nom', 'M10!')
            ->set('flocage_numero', '101')
            ->call('ajouterAuPanier')
            ->assertHasErrors(['flocage_nom', 'flocage_numero']);
    }

    public function test_configured_jersey_can_be_added_to_session_cart(): void
    {
        Livewire::test('pages::produit.show')
            ->set('version_selectionnee', 'Joueur')
            ->set('taille_selectionnee', 'L')
            ->set('flocage_actif', true)
            ->set('flocage_nom', 'Henry')
            ->set('flocage_numero', '14')
            ->set('badge_manche', true)
            ->call('ajouterAuPanier')
            ->assertHasNoErrors()
            ->assertSet('confirmation_visible', true)
            ->assertSet('panier_count', 1)
            ->assertDispatched('panier-mis-a-jour', count: 1);

        $this->assertCount(1, session('panier'));
        $this->assertSame('HENRY', session('panier.0.flocage.nom'));
        $this->assertSame('14', session('panier.0.flocage.numero'));
        $this->assertSame(164.90, session('panier.0.prix_unitaire'));
    }
}

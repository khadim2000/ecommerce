<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class EcommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@ecommerce.com',
            'phone' => '0123456789',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // Créer un utilisateur client
        User::create([
            'name' => 'Client Test',
            'email' => 'client@test.com',
            'phone' => '0987654321',
            'role' => 'client',
            'password' => Hash::make('password'),
        ]);

        // Créer des catégories
        $categories = [
            ['name' => 'Vêtements', 'slug' => 'vetements'],
            ['name' => 'Électronique', 'slug' => 'electronique'],
            ['name' => 'Maison & Jardin', 'slug' => 'maison-jardin'],
            ['name' => 'Sports', 'slug' => 'sports'],
            ['name' => 'Livres', 'slug' => 'livres'],
            ['name' => 'Beauté', 'slug' => 'beaute'],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Créer des couleurs - Palette complète
        $colors = [
            // Couleurs primaires
            ['name' => 'Rouge', 'hex_code' => '#FF0000'],
            ['name' => 'Bleu', 'hex_code' => '#0000FF'],
            ['name' => 'Vert', 'hex_code' => '#00FF00'],
            ['name' => 'Jaune', 'hex_code' => '#FFFF00'],
            
            // Couleurs secondaires
            ['name' => 'Orange', 'hex_code' => '#FFA500'],
            ['name' => 'Violet', 'hex_code' => '#800080'],
            ['name' => 'Rose', 'hex_code' => '#FFC0CB'],
            ['name' => 'Cyan', 'hex_code' => '#00FFFF'],
            
            // Couleurs neutres
            ['name' => 'Noir', 'hex_code' => '#000000'],
            ['name' => 'Blanc', 'hex_code' => '#FFFFFF'],
            ['name' => 'Gris', 'hex_code' => '#808080'],
            ['name' => 'Gris foncé', 'hex_code' => '#404040'],
            ['name' => 'Gris clair', 'hex_code' => '#C0C0C0'],
            
            // Couleurs terre
            ['name' => 'Marron', 'hex_code' => '#8B4513'],
            ['name' => 'Beige', 'hex_code' => '#F5F5DC'],
            ['name' => 'Crème', 'hex_code' => '#FFFDD0'],
            ['name' => 'Taupe', 'hex_code' => '#8B7D6B'],
            
            // Bleus variés
            ['name' => 'Bleu marine', 'hex_code' => '#000080'],
            ['name' => 'Bleu ciel', 'hex_code' => '#87CEEB'],
            ['name' => 'Bleu turquoise', 'hex_code' => '#40E0D0'],
            ['name' => 'Bleu royal', 'hex_code' => '#4169E1'],
            ['name' => 'Bleu acier', 'hex_code' => '#4682B4'],
            
            // Rouges variés
            ['name' => 'Rouge foncé', 'hex_code' => '#8B0000'],
            ['name' => 'Rouge bordeaux', 'hex_code' => '#800020'],
            ['name' => 'Rouge cerise', 'hex_code' => '#DE3163'],
            ['name' => 'Rouge corail', 'hex_code' => '#FF7F50'],
            ['name' => 'Rouge tomate', 'hex_code' => '#FF6347'],
            
            // Verts variés
            ['name' => 'Vert foncé', 'hex_code' => '#006400'],
            ['name' => 'Vert lime', 'hex_code' => '#32CD32'],
            ['name' => 'Vert menthe', 'hex_code' => '#98FB98'],
            ['name' => 'Vert olive', 'hex_code' => '#808000'],
            ['name' => 'Vert émeraude', 'hex_code' => '#50C878'],
            ['name' => 'Vert forêt', 'hex_code' => '#228B22'],
            
            // Violets variés
            ['name' => 'Violet foncé', 'hex_code' => '#4B0082'],
            ['name' => 'Lavande', 'hex_code' => '#E6E6FA'],
            ['name' => 'Mauve', 'hex_code' => '#E0B0FF'],
            ['name' => 'Indigo', 'hex_code' => '#4B0082'],
            
            // Jaunes variés
            ['name' => 'Jaune doré', 'hex_code' => '#FFD700'],
            ['name' => 'Jaune citron', 'hex_code' => '#FFFACD'],
            ['name' => 'Jaune moutarde', 'hex_code' => '#FFDB58'],
            ['name' => 'Jaune canari', 'hex_code' => '#FFFF99'],
            
            // Oranges variés
            ['name' => 'Orange foncé', 'hex_code' => '#FF8C00'],
            ['name' => 'Orange pêche', 'hex_code' => '#FFCCCB'],
            ['name' => 'Orange abricot', 'hex_code' => '#FBCEB1'],
            ['name' => 'Orange mandarine', 'hex_code' => '#F28500'],
            
            // Couleurs métalliques
            ['name' => 'Or', 'hex_code' => '#FFD700'],
            ['name' => 'Argent', 'hex_code' => '#C0C0C0'],
            ['name' => 'Bronze', 'hex_code' => '#CD7F32'],
            ['name' => 'Cuivre', 'hex_code' => '#B87333'],
            
            // Couleurs pastel
            ['name' => 'Rose pastel', 'hex_code' => '#FFB6C1'],
            ['name' => 'Bleu pastel', 'hex_code' => '#ADD8E6'],
            ['name' => 'Vert pastel', 'hex_code' => '#98FB98'],
            ['name' => 'Violet pastel', 'hex_code' => '#DDA0DD'],
            ['name' => 'Jaune pastel', 'hex_code' => '#FFFFE0'],
            
            // Couleurs spéciales
            ['name' => 'Magenta', 'hex_code' => '#FF00FF'],
            ['name' => 'Turquoise', 'hex_code' => '#40E0D0'],
            ['name' => 'Sarcelle', 'hex_code' => '#008080'],
            ['name' => 'Fuchsia', 'hex_code' => '#FF00FF'],
            ['name' => 'Chartreuse', 'hex_code' => '#7FFF00'],
        ];

        foreach ($colors as $colorData) {
            Color::create($colorData);
        }

        // Créer des produits
        $products = [
            [
                'name' => 'T-shirt en coton bio',
                'description' => 'T-shirt confortable en coton biologique, parfait pour tous les jours.',
                'price' => 29.99,
                'stock' => 50,
                'size' => ['S', 'M', 'L', 'XL'],
                'category_id' => 1,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400',
            ],
            [
                'name' => 'Smartphone Android',
                'description' => 'Smartphone dernière génération avec écran OLED et appareil photo haute résolution.',
                'price' => 599.99,
                'stock' => 25,
                'size' => null,
                'category_id' => 2,
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400',
            ],
            [
                'name' => 'Livre de cuisine',
                'description' => 'Recettes traditionnelles et modernes pour tous les niveaux.',
                'price' => 24.99,
                'stock' => 100,
                'size' => null,
                'category_id' => 5,
                'image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400',
            ],
            [
                'name' => 'Chaussures de sport',
                'description' => 'Chaussures de running avec amorti et respirabilité optimale.',
                'price' => 89.99,
                'stock' => 30,
                'size' => ['38', '39', '40', '41', '42', '43', '44'],
                'category_id' => 4,
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400',
            ],
            [
                'name' => 'Lampadaire design',
                'description' => 'Lampadaire moderne avec éclairage LED et design épuré.',
                'price' => 149.99,
                'stock' => 15,
                'size' => null,
                'category_id' => 3,
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
            ],
            [
                'name' => 'Crème hydratante',
                'description' => 'Crème hydratante naturelle pour tous types de peau.',
                'price' => 19.99,
                'stock' => 75,
                'size' => null,
                'category_id' => 6,
                'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400',
            ],
            [
                'name' => 'Pantalon jean',
                'description' => 'Jean classique en denim stretch, coupe slim.',
                'price' => 79.99,
                'stock' => 40,
                'size' => ['28', '30', '32', '34', '36', '38'],
                'category_id' => 1,
                'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=400',
            ],
            [
                'name' => 'Casque audio sans fil',
                'description' => 'Casque Bluetooth avec réduction de bruit active.',
                'price' => 199.99,
                'stock' => 20,
                'size' => null,
                'category_id' => 2,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400',
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            
            // Associer des couleurs aléatoires aux produits
            $randomColors = Color::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $product->colors()->attach($randomColors);
        }
    }
}
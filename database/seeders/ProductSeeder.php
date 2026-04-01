<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Seed 10,000 products with reviews and tags.
     * Uses bulk inserts for speed — runs in ~10-20 seconds.
     */
    public function run(): void
    {
        $categories = [
            'electronics',
            'clothing',
            'home',
            'sports',
            'books',
            'toys',
            'food',
            'automotive',
            'health',
            'garden',
        ];

        $adjectives = ['Premium', 'Basic', 'Ultra', 'Pro', 'Lite', 'Max', 'Mini', 'Super', 'Mega', 'Eco'];
        $nouns = ['Widget', 'Gadget', 'Device', 'Tool', 'Kit', 'Pack', 'Set', 'Bundle', 'Unit', 'System'];
        $authors = ['Alice', 'Bob', 'Charlie', 'Diana', 'Eve', 'Frank', 'Grace', 'Hank', 'Ivy', 'Jack'];
        $reviewBodies = [
            'Great product, highly recommended!',
            'Decent quality for the price.',
            'Not what I expected, but okay.',
            'Absolutely love it. Will buy again.',
            'Terrible quality. Broke after a week.',
            'Good value. Works as described.',
            'Exceeded my expectations!',
            'Average product. Nothing special.',
            'Best purchase I made this year.',
            'Would not recommend to anyone.',
        ];

        $now = now()->toDateTimeString();

        // ---- Seed Tags ----
        $tagNames = [
            'sale', 'new-arrival', 'bestseller', 'limited-edition', 'clearance',
            'eco-friendly', 'handmade', 'imported', 'organic', 'premium',
            'budget', 'trending', 'exclusive', 'refurbished', 'bundle-deal',
        ];

        $tags = [];
        foreach ($tagNames as $name) {
            $tags[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('tags')->insert($tags);

        $tagIds = DB::table('tags')->pluck('id')->toArray();

        // ---- Seed Products in chunks ----
        $totalProducts = 10000;
        $chunkSize = 500;

        for ($offset = 0; $offset < $totalProducts; $offset += $chunkSize) {
            $products = [];
            $count = min($chunkSize, $totalProducts - $offset);

            for ($i = 0; $i < $count; $i++) {
                $idx = $offset + $i;
                $products[] = [
                    'name' => $adjectives[$idx % count($adjectives)] . ' ' . $nouns[$idx % count($nouns)] . ' ' . ($idx + 1),
                    'description' => 'Product description for item #' . ($idx + 1) . '. This is a sample product used for benchmarking the autoresearch pattern on a Laravel API endpoint.',
                    'price' => round(mt_rand(100, 99999) / 100, 2),
                    'category' => $categories[$idx % count($categories)],
                    'sku' => 'SKU-' . str_pad($idx + 1, 6, '0', STR_PAD_LEFT),
                    'stock_quantity' => mt_rand(0, 500),
                    'is_active' => mt_rand(0, 10) > 1, // 90% active
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('products')->insert($products);
        }

        // ---- Seed Reviews (0-5 per product, ~25,000 total) ----
        $productIds = DB::table('products')->pluck('id')->toArray();
        $reviews = [];
        $batchCount = 0;

        foreach ($productIds as $productId) {
            $numReviews = mt_rand(0, 5);
            for ($r = 0; $r < $numReviews; $r++) {
                $reviews[] = [
                    'product_id' => $productId,
                    'author' => $authors[array_rand($authors)],
                    'rating' => mt_rand(1, 5),
                    'body' => $reviewBodies[array_rand($reviewBodies)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $batchCount++;

                if ($batchCount >= 500) {
                    DB::table('reviews')->insert($reviews);
                    $reviews = [];
                    $batchCount = 0;
                }
            }
        }
        if (!empty($reviews)) {
            DB::table('reviews')->insert($reviews);
        }

        // ---- Seed Product-Tag pivot (1-4 tags per product) ----
        $pivots = [];
        $batchCount = 0;

        foreach ($productIds as $productId) {
            $numTags = mt_rand(1, 4);
            $assignedTags = array_rand(array_flip($tagIds), $numTags);
            if (!is_array($assignedTags)) {
                $assignedTags = [$assignedTags];
            }

            foreach ($assignedTags as $tagId) {
                $pivots[] = [
                    'product_id' => $productId,
                    'tag_id' => $tagId,
                ];
                $batchCount++;

                if ($batchCount >= 500) {
                    DB::table('product_tag')->insert($pivots);
                    $pivots = [];
                    $batchCount = 0;
                }
            }
        }
        if (!empty($pivots)) {
            DB::table('product_tag')->insert($pivots);
        }

        $this->command->info("Seeded {$totalProducts} products with reviews and tags.");
    }
}

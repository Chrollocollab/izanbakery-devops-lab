<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 4 categories
        $categories = [
            ['name' => 'Kek', 'slug' => 'kek', 'image' => 'kek.jpg'],
            ['name' => 'Kuih Melayu', 'slug' => 'kuih-melayu', 'image' => 'kuih-melayu.jpg'],
            ['name' => 'Cookies', 'slug' => 'cookies', 'image' => 'cookies.jpg'],
            ['name' => 'Goreng', 'slug' => 'goreng', 'image' => 'goreng.jpg'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        // Create brand
        $brand = Brand::firstOrCreate(
            ['slug' => 'izan-bakery'],
            ['name' => 'Izan Bakery', 'slug' => 'izan-bakery', 'image' => 'izan-bakery.jpg']
        );

        // Products data
        $products = [
            // Kek
            [
                'name' => 'Kek Pisang Original',
                'slug' => 'kek-pisang-original',
                'short_description' => 'Kek pisang original yang lembut',
                'description' => 'Kek pisang homemade menggunakan pisang masak yang manis. Tekstur lembut dan aroma yang wangi.',
                'regular_price' => 18.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 30,
                'category' => 'kek',
            ],
            [
                'name' => 'Kek Pisang Coklat',
                'slug' => 'kek-pisang-coklat',
                'short_description' => 'Kek pisang dengan topping coklat meleleh',
                'description' => 'Gabungan kek pisang lembut dengan topping coklat premium. Sesuai untuk pencinta coklat.',
                'regular_price' => 22.00,
                'sale_price' => 20.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 25,
                'category' => 'kek',
            ],
            [
                'name' => 'Kek Pisang Dua Benua',
                'slug' => 'kek-pisang-dua-benua',
                'short_description' => 'Kek pisang dua rasa dalam satu loyang',
                'description' => 'Kek pisang unik dengan dua rasa - original dan coklat. Dua kenikmatan dalam satu gigitan.',
                'regular_price' => 25.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 20,
                'category' => 'kek',
            ],
            [
                'name' => 'Kek Brownies',
                'slug' => 'kek-brownies',
                'short_description' => 'Brownies coklat pekat dan fudgy',
                'description' => 'Brownies coklat premium dengan tekstur fudgy dan lembut. Menggunakan coklat berkualiti.',
                'regular_price' => 28.00,
                'sale_price' => 25.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 35,
                'category' => 'kek',
            ],
            [
                'name' => 'Kek Marble',
                'slug' => 'kek-marble',
                'short_description' => 'Kek marble klasik dengan corak cantik',
                'description' => 'Kek marble dengan kombinasi vanilla dan coklat. Corak marmar yang cantik dan rasa yang enak.',
                'regular_price' => 20.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 25,
                'category' => 'kek',
            ],
            [
                'name' => 'Congo Bar',
                'slug' => 'congo-bar',
                'short_description' => 'Congo bar rangup dengan chocolate chips',
                'description' => 'Congo bar dengan chocolate chips yang rangup di luar dan lembut di dalam. Kegemaran ramai.',
                'regular_price' => 15.00,
                'sale_price' => 12.00,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 40,
                'category' => 'kek',
            ],

            // Kuih Melayu
            [
                'name' => 'Apam Gula Hangus',
                'slug' => 'apam-gula-hangus',
                'short_description' => 'Apam dengan rasa gula hangus yang unik',
                'description' => 'Apam lembut. Rasa manis yang unik dan tekstur gebu.',
                'regular_price' => 6.00,
                'sale_price' => 5.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 70,
                'category' => 'kuih-melayu',
            ],
            [
                'name' => 'Popia Sira',
                'slug' => 'popia-sira',
                'short_description' => 'Popia dengan inti sira manis',
                'description' => 'Popia dengan inti sira (kelapa dan gula) yang manis. Kuih tradisional kegemaran ramai.',
                'regular_price' => 8.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 55,
                'category' => 'kuih-melayu',
            ],
            [
                'name' => 'Kaswi Pandan',
                'slug' => 'kaswi-pandan',
                'short_description' => 'Kuih kaswi dengan perisa pandan',
                'description' => 'Kuih kaswi tradisional dengan aroma pandan yang wangi. Tekstur lembut dan kenyal.',
                'regular_price' => 5.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 65,
                'category' => 'kuih-melayu',
            ],
            [
                'name' => 'Kuih Bom Bijan',
                'slug' => 'kuih-bom-bijan',
                'short_description' => 'Kuih bom dengan salutan bijan',
                'description' => 'Kuih bom dengan inti kacang hijau dan salutan bijan yang rangup. Kuih popular di majlis keraian.',
                'regular_price' => 10.00,
                'sale_price' => 8.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 50,
                'category' => 'kuih-melayu',
            ],

            // Cookies

            // Goreng
            [
                'name' => 'Mee Goreng',
                'slug' => 'mee-goreng',
                'short_description' => 'Mee goreng pedas ala mamak',
                'description' => 'Mee goreng ala mamak dengan sayur dan telur. Pedas dan sedap untuk sarapan atau makan malam.',
                'regular_price' => 8.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 100,
                'category' => 'goreng',
            ],
            [
                'name' => 'Kuey Teow Goreng',
                'slug' => 'kuey-teow-goreng',
                'short_description' => 'Kuey teow goreng dengan udang dan sotong',
                'description' => 'Kuey teow goreng dengan udang, sotong dan sayur. Rasa char kuey teow yang asli.',
                'regular_price' => 10.00,
                'sale_price' => 8.00,
                'stock_status' => 'instock',
                'featured' => true,
                'quantity' => 80,
                'category' => 'goreng',
            ],
            [
                'name' => 'Bihun Goreng',
                'slug' => 'bihun-goreng',
                'short_description' => 'Bihun goreng dengan sos kicap',
                'description' => 'Bihun goreng dengan sayur dan telur. Rasa yang sedap dan ringan untuk sarapan.',
                'regular_price' => 7.00,
                'sale_price' => null,
                'stock_status' => 'instock',
                'featured' => false,
                'quantity' => 90,
                'category' => 'goreng',
            ],
        ];

        // Photos available in public/assets/images/products, keyed by product slug.
        $productImages = [
            'kek-pisang-original' => 'kek-pisang-original.JPG',
            'kaswi-pandan'        => 'kaswi.jpg',
            'popia-sira'          => 'popiaSira.jpeg',
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();
            unset($productData['category']);

            $productData['category_id'] = $category ? $category->id : null;
            $productData['brand_id'] = $brand->id;
            $productData['image'] = $productData['slug'] . '.jpg';

            $product = Product::firstOrCreate(
                ['slug' => $productData['slug']],
                $productData
            );

            if (isset($productImages[$product->slug]) && $product->getMedia('product')->isEmpty()) {
                $imagePath = public_path('assets/images/products/' . $productImages[$product->slug]);

                if (file_exists($imagePath)) {
                    $product->addMedia($imagePath)
                        ->preservingOriginal()
                        ->toMediaCollection('product');
                }
            }
        }

        $this->command->info('Products seeded successfully! Created 19 products in 4 categories.');
    }
}

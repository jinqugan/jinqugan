<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory;
use Illuminate\Support\Carbon;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

        for ($i=0; $i <= 50 ; $i++) {
            $posts[] = [
                'name' => $faker->sentence(3),
                'price' => $faker->unique()->numberBetween(50,300),
                'description' => $faker->text($maxNbChars = 300),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        Product::insert($posts);
    }
}

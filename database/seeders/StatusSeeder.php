<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $methods = [
            [
                'name' => 'pending',
                'lang_code' => 'pending',
                'type' => 'order',
            ],
            [
                'name' => 'completed',
                'lang_code' => 'completed',
                'type' => 'order',
            ],
            [
                'name' => 'cancelled',
                'lang_code' => 'cancelled',
                'type' => 'order',
            ],
            [
                'name' => 'refunded',
                'lang_code' => 'refunded',
                'type' => 'order',
            ],
            [
                'name' => 'pending',
                'lang_code' => 'pending',
                'type' => 'transaction',
            ],
            [
                'name' => 'paid',
                'lang_code' => 'paid',
                'type' => 'transaction',
            ],
            [
                'name' => 'cancelled',
                'lang_code' => 'cancelled',
                'type' => 'transaction',
            ],
            [
                'name' => 'refunded',
                'lang_code' => 'refunded',
                'type' => 'transaction',
            ]
        ];

        Status::insert($methods);
    }
}

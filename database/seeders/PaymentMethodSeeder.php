<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
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
                'name' => 'cash',
                'lang_code' => 'cash',
            ],
            [
                'name' => 'debit_card',
                'lang_code' => 'debit_card',
            ],
            [
                'name' => 'credit_card',
                'lang_code' => 'credit_card',
            ]
        ];

        PaymentMethod::insert($methods);
    }
}

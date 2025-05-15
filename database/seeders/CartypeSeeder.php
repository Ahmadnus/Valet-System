<?php

namespace Database\Seeders;

use App\Models\Cartype;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carTypes = [
            ['en' => 'BMW', 'ar' => 'بي إم دبليو'],
            ['en' => 'Mercedes', 'ar' => 'مرسيدس'],
            ['en' => 'Toyota', 'ar' => 'تويوتا'],
            ['en' => 'Hyundai', 'ar' => 'هيونداي'],
            ['en' => 'Kia', 'ar' => 'كيا'],
        ];

        foreach ($carTypes as $type) {
            Cartype::create([
                'en' => ['type' => $type['en']],
                'ar' => ['type' => $type['ar']],
            ]);
        }
    }
}

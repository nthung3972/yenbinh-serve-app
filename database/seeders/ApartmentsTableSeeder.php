<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Apartment;
use Carbon\Carbon;

class ApartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i < 200; $i++) {
            Apartment::insert([
                [
                    'apartment_id' => "$i",
                    'number' => "00$i",
                    'floor' => rand(1, 30),
                    'area' => rand(80, 100),
                    'status' => rand(0, 1),
                    'building_id' => rand(1, 7),
                    'resident_id' => "$i",
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]
            ]);
        }
    }
}

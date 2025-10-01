<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['category_name' => 'Alat Peraga Matematika',]);
        Category::create(['category_name' => 'Alat Peraga IPA',]);
        Category::create(['category_name' => 'Peralatan Laboratorium',]);
        Category::create(['category_name' => 'Media Pembelajaran',]);
        Category::create(['category_name' => 'Elektronik Edukasi',]);
        Category::create(['category_name' => 'Furniture Sekolah',]);
    }
}

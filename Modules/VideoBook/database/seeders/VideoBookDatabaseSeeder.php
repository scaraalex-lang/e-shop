<?php

namespace Modules\VideoBook\Database\Seeders;

use Illuminate\Database\Seeder;

class VideoBookDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PaginaTemplateSeeder::class);
    }
}

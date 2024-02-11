<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 'Manager' role
        $manager = new Role();
        $manager->name = 'Manager';
        $manager->save();

        // Seed 'Teammate' role
        $teammate = new Role();
        $teammate->name = 'Teammate';
        $teammate->save();
    }
}

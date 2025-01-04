<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Level;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Level::insert([
            [
                'name' => 'Pioneer',
                'points_required' => 0,
                'icon_svg' => '/img/levels/newbie.png',
            ],
            [
                'name' => 'Nomad',
                'points_required' => 100,
                'icon_svg' => '/img/levels/explorer.png',
            ],
            [
                'name' => 'Contributor',
                'points_required' => 300,
                'icon_svg' => '/img/levels/contributor.png',
            ],
            [
                'name' => 'Collaborator',
                'points_required' => 500,
                'icon_svg' => '/img/levels/collaborator.png',
            ],
            [
                'name' => 'Leader',
                'points_required' => 700,
                'icon_svg' => '/img/levels/leader.png',
            ],
            [
                'name' => 'Expert',
                'points_required' => 900,
                'icon_svg' => '/img/levels/expert.png',
            ],
            [
                'name' => 'Legend',
                'points_required' => 1500,
                'icon_svg' => '/img/levels/legend.png',
            ],
        ]);
    }
}

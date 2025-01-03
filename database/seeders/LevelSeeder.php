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
                'name' => 'Newbie',
                'points_required' => 0,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="#4CAF50" viewBox="0 0 64 64"><path d="M32 2C26 2 22 10 22 16c0 6 6 10 10 14s10 10 10 14c0 6-4 14-10 14s-10-8-10-14H6c0 12 8 22 18 22s18-10 18-22c0-4-4-8-8-12s-8-10-8-14C28 10 30 6 32 6s4 4 4 10h12C48 10 40 2 32 2z" /></svg>',
            ],
            [
                'name' => 'Explorer',
                'points_required' => 100,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="#FFA500" viewBox="0 0 64 64"><circle cx="32" cy="32" r="30" stroke="#000" stroke-width="2" fill="none"/><polygon points="32,8 40,56 24,56" fill="#FFA500" stroke="#000" stroke-width="2"/><line x1="32" y1="8" x2="32" y2="56" stroke="#000" stroke-width="2"/></svg>',
            ],
            [
                'name' => 'Contributor',
                'points_required' => 300,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" fill="#FFD700" viewBox="0 0 64 64"><path d="M10 30h12v6H10z" fill="#FFD700"/><path d="M42 30h12v6H42z" fill="#FFD700"/><path d="M22 36h20v6H22z" fill="#FFD700"/><path d="M18 24h28v6H18z" fill="#FFD700"/></svg>',
            ],
            [
                'name' => 'Collaborator',
                'points_required' => 500,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="#3498DB" width="64" height="64">
                                <circle cx="32" cy="32" r="30" stroke="#000" stroke-width="2" fill="none"/>
                                <circle cx="32" cy="32" r="20" fill="#3498DB"/>
                                <path d="M12,32 L52,32 M32,12 L32,52" stroke="#000" stroke-width="2"/>
                                </svg>',
            ],
            [
                'name' => 'Leader',
                'points_required' => 700,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="#FFD700" width="64" height="64">
                                <path d="M20 24h24v12H20z" fill="#FFD700"/>
                                <path d="M16 36h32v4H16z" fill="#FFD700"/>
                                <rect x="22" y="40" width="20" height="12" fill="#FFD700"/>
                                </svg>',
            ],
            [
                'name' => 'Expert',
                'points_required' => 900,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="#3498DB" width="64" height="64">
                                <path d="M10 12h44v40H10z" fill="#3498DB"/>
                                <path d="M32 12v40" stroke="#FFF" stroke-width="2"/>
                                </svg>',
            ],
            [
                'name' => 'Legend',
                'points_required' => 1500,
                'icon_svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="#FFD700" width="64" height="64">
                                <path d="M8 40h48v16H8z" fill="#FFD700"/>
                                <path d="M16 24l16 16 16-16" fill="#FFD700" stroke="#000" stroke-width="2"/>
                                </svg>',
            ],
        ]);
    }
}

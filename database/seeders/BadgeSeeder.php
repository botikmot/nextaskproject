<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Badge::insert([
            [
                'name' => 'Explorer',
                'description' => 'Awarded after exploring all major features (e.g., creating a task, joining a project, sending a message).',
                'icon' => '/img/badges/explorer.png',
                'category' => 'general',
                'threshold' => 20,
            ],
            [
                'name' => 'Achiever',
                'description' => 'Awarded for completing a series of milestones. (Completing a set number of tasks, projects, or challenges.)',
                'icon' => '/img/badges/achiever.png',
                'category' => 'task',
                'threshold' => 10,
            ],
            [
                'name' => 'Social Star',
                'description' => 'Earned for making a certain number of posts or comments on the social network.',
                'icon' => '/img/badges/social.png',
                'category' => 'social',
                'threshold' => 20,
            ],
            [
                'name' => 'Task Starter',
                'description' => 'Awarded after completing the first task.',
                'icon' => '/img/badges/starter.png',
                'category' => 'task',
                'threshold' => 1,
            ],
            [
                'name' => 'Task Master',
                'description' => 'Awarded for completing a large number of tasks (e.g., 100 tasks).',
                'icon' => '/img/badges/master.png',
                'category' => 'task',
                'threshold' => 100,
            ],
            [
                'name' => 'Task Ninja',
                'description' => 'Awarded for completing tasks ahead of deadlines consistently.',
                'icon' => '/img/badges/ninja.png',
                'category' => 'task',
                'threshold' => 1,
            ],
            [
                'name' => 'Team Player',
                'description' => 'Awarded for collaborating on multiple projects.',
                'icon' => '/img/badges/team.png',
                'category' => 'project',
                'threshold' => 3,
            ],
            [
                'name' => 'Project Leader',
                'description' => 'Earned for creating and managing a project successfully.',
                'icon' => '/img/badges/leader.png',
                'category' => 'project',
                'threshold' => 1,
            ],
            [
                'name' => 'Multitasker',
                'description' => 'Earned for actively contributing to more than 3 projects simultaneously.',
                'icon' => '/img/badges/multitasker.png',
                'category' => 'project',
                'threshold' => 4,
            ],
            [
                'name' => 'Conversation Starter',
                'description' => 'Awarded after sending the first message.',
                'icon' => '/img/badges/conversation.png',
                'category' => 'message',
                'threshold' => 1,
            ],
            [
                'name' => 'Chatterbox',
                'description' => 'Earned after sending 100+ messages.',
                'icon' => '/img/badges/chatterbox.png',
                'category' => 'message',
                'threshold' => 100,
            ],
            [
                'name' => 'Connector',
                'description' => 'Earned for starting 5+ group chats.',
                'icon' => '/img/badges/connector.png',
                'category' => 'message',
                'threshold' => 5,
            ],
            [
                'name' => 'Rookie',
                'description' => 'Awarded after completing the first challenge.',
                'icon' => '/img/badges/rookie.png',
                'category' => 'challenge',
                'threshold' => 5,
            ],
            [
                'name' => 'Conqueror',
                'description' => 'Earned after completing a high number of challenges.',
                'icon' => '/img/badges/conqueror.png',
                'category' => 'challenge',
                'threshold' => 50,
            ],
            [
                'name' => 'Top Competitor',
                'description' => 'Awarded for achieving top results in challenges.',
                'icon' => '/img/badges/top.png',
                'category' => 'challenge',
                'threshold' => 50,
            ],
        ]);
    }
}

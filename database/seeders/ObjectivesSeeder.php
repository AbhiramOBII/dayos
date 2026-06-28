<?php

namespace Database\Seeders;

use App\Models\QuarterlyObjective;
use App\Models\User;
use Illuminate\Database\Seeder;

class ObjectivesSeeder extends Seeder
{
    /**
     * Standard monthly objective templates.
     * These are seeded for the current month for the admin user.
     */
    private array $templates = [
        [
            'title'            => 'Revenue Target',
            'measurement_type' => 'currency',
            'target'           => 500000,
            'notes'            => 'Total revenue earned this month.',
        ],
        [
            'title'            => 'Podcasts',
            'measurement_type' => 'number',
            'target'           => 12,
            'notes'            => 'Number of podcast episodes recorded or published.',
        ],
        [
            'title'            => 'New Customer Acquisitions',
            'measurement_type' => 'number',
            'target'           => 10,
            'notes'            => 'New paying customers onboarded this quarter.',
        ],
        [
            'title'            => 'Health Routines',
            'measurement_type' => 'number',
            'target'           => 60,
            'notes'            => 'Health routine completions across the quarter.',
        ],
        [
            'title'            => 'Content Assets',
            'measurement_type' => 'number',
            'target'           => 20,
            'notes'            => 'Blog posts, videos, or other content pieces created.',
        ],
        [
            'title'            => 'Webinars',
            'measurement_type' => 'number',
            'target'           => 4,
            'notes'            => 'Number of webinars hosted or delivered.',
        ],
    ];

    public function run(): void
    {
        $user = User::where('is_admin', true)->first();

        if (! $user) {
            $this->command->warn('No admin user found. Run DatabaseSeeder first.');
            return;
        }

        $startDate = now()->toDateString();

        foreach ($this->templates as $template) {
            QuarterlyObjective::firstOrCreate(
                [
                    'user_id'    => $user->id,
                    'title'      => $template['title'],
                    'start_date' => $startDate,
                ],
                [
                    'measurement_type' => $template['measurement_type'],
                    'target'           => $template['target'],
                    'notes'            => $template['notes'],
                    'is_active'        => true,
                ]
            );
        }

        $this->command->info('Seeded ' . count($this->templates) . ' standard objectives (starting ' . $startDate . ').');
    }
}

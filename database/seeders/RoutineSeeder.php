<?php

namespace Database\Seeders;

use App\Models\Routine;
use Illuminate\Database\Seeder;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $behavioural = [
            [
                'title'       => '10 min Exercise / Yoga',
                'description' => 'Start the day with at least 10 minutes of physical movement — exercise, yoga, or stretching.',
                'sort_order'  => 1,
            ],
            [
                'title'       => '30 min Screen Away Time',
                'description' => 'Step away from all screens for 30 minutes. Rest your eyes and reset your focus.',
                'sort_order'  => 2,
            ],
            [
                'title'       => 'Vishnu Sahasranamam',
                'description' => 'Recite or listen to the Vishnu Sahasranamam as a daily spiritual practice.',
                'sort_order'  => 3,
            ],
            [
                'title'       => 'Contact One New Business Owner',
                'description' => 'Reach out to one new business owner every day to build your network and create opportunities.',
                'sort_order'  => 4,
            ],
            [
                'title'       => '2 Minute Vlog',
                'description' => 'Record a short 2-minute video log capturing your thoughts, progress, or highlights of the day.',
                'sort_order'  => 5,
            ],
        ];

        $reflective = [
            [
                'title'       => 'Morning Vision',
                'description' => 'Visualize your ideal day before it begins. Set the tone with intention.',
                'prompt'      => 'What does your ideal day look like today? Close your eyes, visualize it clearly, and write it down.',
                'sort_order'  => 1,
            ],
            [
                'title'       => 'Journal',
                'description' => 'A free-form daily journal entry to process thoughts, feelings, and experiences.',
                'prompt'      => 'What is on your mind today? Write freely — no rules, no filters.',
                'sort_order'  => 2,
            ],
            [
                'title'       => 'Manifestation',
                'description' => 'Write your intentions and desires as if they have already been achieved.',
                'prompt'      => 'What are you calling into your life? Write it in the present tense, as if it is already yours.',
                'sort_order'  => 3,
            ],
            [
                'title'       => 'Gratitude Log',
                'description' => 'Acknowledge the things you are grateful for each day to cultivate a positive mindset.',
                'prompt'      => 'What are three things you are deeply grateful for today, and why?',
                'sort_order'  => 4,
            ],
        ];

        foreach ($behavioural as $data) {
            Routine::firstOrCreate(
                ['title' => $data['title'], 'type' => 'behavioural'],
                array_merge($data, ['type' => 'behavioural', 'is_active' => true])
            );
        }

        foreach ($reflective as $data) {
            Routine::firstOrCreate(
                ['title' => $data['title'], 'type' => 'reflective'],
                array_merge($data, ['type' => 'reflective', 'is_active' => true])
            );
        }
    }
}

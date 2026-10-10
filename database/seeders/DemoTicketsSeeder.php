<?php

namespace Database\Seeders;

use App\Models\QueueTicket;
use App\Support\TriageRules;
use Illuminate\Database\Seeder;

# optional: fills today with sample patients so the board and summary look alive
class DemoTicketsSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(7);

        $cases = [
            ['child', ['cough', 'colds'], 'today', []],
            ['adult', ['headache', 'body_pain'], '1_2_days', []],
            ['child', ['fever', 'rash'], '1_2_days', []],
            ['senior', ['cough', 'fever'], '3_plus_days', []],
            ['adult', ['injury'], 'today', []],
            ['infant', ['fever'], 'today', []],
            ['adult', ['diarrhea', 'vomiting'], '3_plus_days', []],
            ['adult', ['abdominal_pain'], 'today', ['bleeding']],
            ['child', ['sore_throat'], 'today', []],
            ['senior', ['body_pain'], '1_2_days', ['chest_pain']],
            ['adult', ['eye_ear'], '1_2_days', []],
            ['adult', ['cough'], '3_plus_days', []],
        ];

        $total = 24;

        for ($i = 0; $i < $total; $i++) {
            [$age, $symptoms, $duration, $danger] = $cases[$i % count($cases)];
            $answers = [
                'age_group' => $age,
                'pregnant' => false,
                'symptoms' => $symptoms,
                'duration' => $duration,
                'danger' => $danger,
                'notes' => null,
            ];
            [$suggested] = TriageRules::suggest($answers);

            $arrived = now()->subMinutes(($total - $i) * 11);
            $ticket = QueueTicket::createForToday([
                'source' => $i % 6 === 5 ? 'staff' : 'self',
                'answers' => $answers,
                'suggested_priority' => $suggested,
            ]);

            $changes = ['created_at' => $arrived];

            if ($i < $total - 5) {
                $called = $arrived->copy()->addMinutes(mt_rand(5, 40));
                $finished = $called->copy()->addMinutes(mt_rand(4, 12));
                $changes += [
                    'priority' => $i % 9 === 4 ? 'urgent' : $suggested,
                    'status' => $i % 10 === 7 ? 'no_show' : 'done',
                    'called_at' => $called,
                    'finished_at' => $finished,
                ];
            }

            $ticket->forceFill($changes)->save();
        }
    }
}

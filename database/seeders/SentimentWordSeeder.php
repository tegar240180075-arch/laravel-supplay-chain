<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SentimentWordSeeder extends Seeder
{
    public function run(): void
    {
        $positiveWords = [
            'growth', 'increase', 'profit', 'stable', 'improve', 'success', 'recovery',
            'surplus', 'gain', 'boost', 'expand', 'rise', 'positive', 'good', 'strong',
            'agreement', 'deal', 'advantage', 'benefit', 'boom', 'record', 'safe',
            'thriving', 'resilient', 'up', 'soar', 'surged', 'rebound', 'upgrade'
        ];

        $negativeWords = [
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'loss', 'decrease',
            'drop', 'fall', 'negative', 'bad', 'weak', 'conflict', 'tariff', 'deficit',
            'disruption', 'decline', 'crash', 'recession', 'shortage', 'threat',
            'risk', 'cancel', 'down', 'slump', 'plunge', 'downgrade', 'tension'
        ];

        foreach ($positiveWords as $word) {
            DB::table('positive_words')->updateOrInsert(['word' => $word]);
        }

        foreach ($negativeWords as $word) {
            DB::table('negative_words')->updateOrInsert(['word' => $word]);
        }
    }
}

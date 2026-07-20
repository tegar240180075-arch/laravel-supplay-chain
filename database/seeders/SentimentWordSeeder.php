<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SentimentWordSeeder extends Seeder
{
    public function run(): void
    {
        // ── 100+ Kata Positif (domain: ekonomi, logistik, perdagangan, geopolitik) ──
        $positiveWords = [
            // Pertumbuhan & Keuangan
            'growth', 'increase', 'profit', 'stable', 'improve', 'success', 'recovery',
            'surplus', 'gain', 'boost', 'expand', 'rise', 'positive', 'good', 'strong',
            'thriving', 'resilient', 'soar', 'surged', 'rebound', 'upgrade', 'record',
            'prosperity', 'profitable', 'revenue', 'earnings', 'surplus', 'wealth',
            'appreciation', 'dividend', 'investment', 'return', 'fund', 'capital',
            // Perdagangan & Logistik
            'agreement', 'deal', 'advantage', 'benefit', 'boom', 'safe', 'efficient',
            'on-time', 'optimized', 'streamlined', 'capacity', 'delivery', 'shipped',
            'cleared', 'fast', 'reliable', 'secured', 'opened', 'expanded', 'connected',
            'bilateral', 'multilateral', 'partnership', 'alliance', 'cooperation',
            'trade-deal', 'liberalization', 'duty-free', 'approved', 'ratified',
            // Infrastruktur & Teknologi
            'innovation', 'modernized', 'upgraded', 'advanced', 'digitized', 'automated',
            'renewable', 'sustainable', 'green', 'infrastructure', 'port-expansion',
            'smart', 'optimized', 'accelerated', 'launched', 'developed', 'breakthrough',
            // Ekonomi Makro
            'gdp', 'productivity', 'employment', 'jobs', 'hired', 'stimulus',
            'reform', 'deregulation', 'credit', 'rating', 'upgrade', 'investment-grade',
            'low-inflation', 'deflation-control', 'balanced', 'diversified',
        ];

        // ── 100+ Kata Negatif (domain: risiko rantai pasok & geopolitik) ──
        $negativeWords = [
            // Risiko & Krisis
            'war', 'crisis', 'inflation', 'delay', 'disaster', 'loss', 'decrease',
            'drop', 'fall', 'negative', 'bad', 'weak', 'conflict', 'tariff', 'deficit',
            'disruption', 'decline', 'crash', 'recession', 'shortage', 'threat',
            'risk', 'cancel', 'down', 'slump', 'plunge', 'downgrade', 'tension',
            // Sanksi & Geopolitik
            'sanction', 'embargo', 'blockade', 'occupation', 'invasion', 'attack',
            'terrorism', 'coup', 'unstable', 'protest', 'riot', 'strike', 'ban',
            'banned', 'restriction', 'prohibited', 'frozen', 'seized', 'expelled',
            // Logistik & Perdagangan
            'port-congestion', 'congestion', 'backlog', 'bottleneck', 'stranded',
            'damaged', 'lost', 'delayed', 'cancelled', 'diverted', 'hijacked',
            'piracy', 'smuggling', 'counterfeit', 'inspection', 'detained', 'seized',
            'overflow', 'overloaded', 'undercapacity', 'misrouted', 'accident',
            // Ekonomi Makro
            'hyperinflation', 'stagflation', 'devaluation', 'depreciation', 'default',
            'bankruptcy', 'debt', 'overdue', 'insolvent', 'collapse', 'meltdown',
            'negative-growth', 'poverty', 'unemployment', 'layoff', 'fired', 'closure',
            'shutdown', 'lockdown', 'quarantine', 'contamination', 'pandemic',
            // Cuaca & Bencana
            'storm', 'hurricane', 'typhoon', 'flood', 'earthquake', 'tsunami',
            'drought', 'wildfire', 'heatwave', 'blizzard', 'cyclone', 'tornado',
        ];

        // Hapus data lama dan isi ulang
        DB::table('positive_words')->truncate();
        DB::table('negative_words')->truncate();

        foreach (array_unique($positiveWords) as $word) {
            DB::table('positive_words')->insert(['word' => strtolower($word)]);
        }

        foreach (array_unique($negativeWords) as $word) {
            DB::table('negative_words')->insert(['word' => strtolower($word)]);
        }

        $posCount = DB::table('positive_words')->count();
        $negCount = DB::table('negative_words')->count();

        $this->command->info("✅ Sentiment words seeded: {$posCount} positif, {$negCount} negatif.");
    }
}

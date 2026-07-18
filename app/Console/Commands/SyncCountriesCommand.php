<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RestCountriesService;
use Illuminate\Support\Facades\DB;

class SyncCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sync:countries {--force : Force re-sync all countries}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch and sync all countries from RestCountries API into the database';

    public function handle(RestCountriesService $service)
    {
        $this->info('Fetching all countries from RestCountries API...');

        $countries = $service->getAllCountries();

        if (empty($countries)) {
            $this->error('Failed to fetch countries from API. Check your internet connection.');
            return Command::FAILURE;
        }

        $count = count($countries);
        $this->info("Found {$count} countries from API.");

        $bar = $this->output->createProgressBar(count($countries));
        $bar->start();

        $inserted = 0;
        $updated = 0;

        foreach ($countries as $country) {
            $existing = DB::table('countries')->where('code', $country['code'])->first();

            if ($existing) {
                if ($this->option('force')) {
                    DB::table('countries')->where('code', $country['code'])->update([
                        'name' => $country['name'],
                        'region' => $country['region'],
                        'currency_code' => $country['currency_code'],
                        'lat' => $country['lat'],
                        'lng' => $country['lng'],
                        'updated_at' => now(),
                    ]);
                    $updated++;
                }
            } else {
                DB::table('countries')->insert([
                    'name' => $country['name'],
                    'code' => $country['code'],
                    'region' => $country['region'],
                    'currency_code' => $country['currency_code'],
                    'lat' => $country['lat'],
                    'lng' => $country['lng'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Sync complete! Inserted: {$inserted}, Updated: {$updated}");
        $this->info("Total countries in database: " . DB::table('countries')->count());

        return Command::SUCCESS;
    }
}

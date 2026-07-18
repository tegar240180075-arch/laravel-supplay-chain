<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Country;
use App\Models\CountryEconomicData;
use App\Services\WorldBankService;

class FillEconomicData extends Command
{
    protected $signature = 'economic:fill';
    protected $description = 'Fill missing economic data (GDP, Inflation, Population) for all countries using World Bank API';

    public function handle(WorldBankService $worldBank)
    {
        // Get countries that have incomplete economic data (null GDP or null inflation) or no data at all
        $countriesWithNoData = Country::whereNotIn('id', function($q) {
            $q->select('country_id')->from('country_economic_data');
        })->get();

        $countriesWithIncomplete = Country::whereIn('id', function($q) {
            $q->select('country_id')
              ->from('country_economic_data')
              ->whereNull('gdp_billions')
              ->orWhereNull('inflation_rate');
        })->get();

        $countries = $countriesWithNoData->merge($countriesWithIncomplete)->unique('id');
        
        $this->info("Found {$countries->count()} countries with missing economic data.");
        
        if ($countries->count() === 0) {
            $this->info('All countries have complete economic data!');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();
        
        $success = 0;
        $failed = 0;

        foreach ($countries as $country) {
            usleep(300000); // 0.3 second delay to be respectful to API
            
            try {
                $result = $worldBank->getEconomicData($country);
                if ($result) {
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
                \Log::warning("Economic fill failed for {$country->code}: " . $e->getMessage());
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done! Success: {$success}, Failed: {$failed}");
        
        return Command::SUCCESS;
    }
}

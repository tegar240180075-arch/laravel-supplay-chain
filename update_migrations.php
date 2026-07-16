<?php

$dir = __DIR__ . '/database/migrations/';

function updateMigration($dir, $suffix, $schemaBody) {
    $files = glob($dir . '*' . $suffix);
    if (!empty($files)) {
        $file = $files[0];
        $content = file_get_contents($file);
        
        $pattern = '/public function up\(\): void\s*\{\s*Schema::create\(\'[^\']+\', function \(Blueprint \$table\) \{\s*\$table->id\(\);\s*\$table->timestamps\(\);\s*\}\);\s*\}/s';
        
        $replacement = "public function up(): void\n    {\n        " . $schemaBody . "\n    }";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($file, $newContent);
        echo "Updated " . basename($file) . "\n";
    }
}

// 1. Countries
updateMigration($dir, '_create_countries_table.php', <<<EOD
Schema::create('countries', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('code', 3)->unique(); // ISO alpha-2 or alpha-3
            \$table->string('region')->nullable();
            \$table->string('currency_code', 10)->nullable();
            \$table->decimal('lat', 10, 8)->nullable();
            \$table->decimal('lng', 11, 8)->nullable();
            \$table->timestamps();
        });
EOD
);

// 2. Country Economic Data
updateMigration($dir, '_create_country_economic_data_table.php', <<<EOD
Schema::create('country_economic_data', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('country_id')->constrained()->onDelete('cascade');
            \$table->integer('year');
            \$table->decimal('gdp_billions', 15, 2)->nullable();
            \$table->decimal('inflation_rate', 5, 2)->nullable();
            \$table->bigInteger('population')->nullable();
            \$table->decimal('exports_billions', 15, 2)->nullable();
            \$table->decimal('imports_billions', 15, 2)->nullable();
            \$table->timestamps();
            
            \$table->unique(['country_id', 'year']);
        });
EOD
);

// 3. Risk Scores
updateMigration($dir, '_create_risk_scores_table.php', <<<EOD
Schema::create('risk_scores', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('country_id')->constrained()->onDelete('cascade');
            \$table->decimal('weather_risk', 5, 2)->default(0);
            \$table->decimal('inflation_risk', 5, 2)->default(0);
            \$table->decimal('news_risk', 5, 2)->default(0);
            \$table->decimal('currency_risk', 5, 2)->default(0);
            \$table->decimal('total_score', 5, 2)->default(0);
            \$table->string('risk_level', 20)->default('Low'); // Low, Medium, High, Critical
            \$table->timestamp('last_calculated_at')->nullable();
            \$table->timestamps();
        });
EOD
);

// 4. Risk Score Histories
updateMigration($dir, '_create_risk_score_histories_table.php', <<<EOD
Schema::create('risk_score_histories', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('country_id')->constrained()->onDelete('cascade');
            \$table->decimal('total_score', 5, 2);
            \$table->string('risk_level', 20);
            \$table->date('record_date');
            \$table->timestamps();
            
            \$table->unique(['country_id', 'record_date']);
        });
EOD
);

// 5. News Caches
updateMigration($dir, '_create_news_caches_table.php', <<<EOD
Schema::create('news_caches', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            \$table->string('title');
            \$table->text('description')->nullable();
            \$table->string('url')->unique();
            \$table->string('source_name')->nullable();
            \$table->timestamp('published_at')->nullable();
            \$table->string('category', 50)->nullable();
            \$table->timestamps();
        });
EOD
);

// 6. News Sentiments
updateMigration($dir, '_create_news_sentiments_table.php', <<<EOD
Schema::create('news_sentiments', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('news_cache_id')->constrained('news_caches')->onDelete('cascade');
            \$table->integer('positive_score')->default(0);
            \$table->integer('negative_score')->default(0);
            \$table->string('sentiment_label', 20)->nullable(); // Positive, Neutral, Negative
            \$table->timestamps();
        });
EOD
);

// 7. Ports
updateMigration($dir, '_create_ports_table.php', <<<EOD
Schema::create('ports', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            \$table->decimal('lat', 10, 8)->nullable();
            \$table->decimal('lng', 11, 8)->nullable();
            \$table->string('type', 50)->nullable();
            \$table->string('size', 50)->nullable();
            \$table->timestamps();
        });
EOD
);

// 8. Watchlists
updateMigration($dir, '_create_watchlists_table.php', <<<EOD
Schema::create('watchlists', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->onDelete('cascade');
            \$table->foreignId('country_id')->constrained()->onDelete('cascade');
            \$table->timestamps();
            
            \$table->unique(['user_id', 'country_id']);
        });
EOD
);

// 9. Articles
updateMigration($dir, '_create_articles_table.php', <<<EOD
Schema::create('articles', function (Blueprint \$table) {
            \$table->id();
            \$table->string('title');
            \$table->text('content');
            \$table->foreignId('user_id')->constrained()->onDelete('cascade'); // Author
            \$table->string('status', 20)->default('published');
            \$table->timestamps();
        });
EOD
);

// 10. Positive Words
updateMigration($dir, '_create_positive_words_table.php', <<<EOD
Schema::create('positive_words', function (Blueprint \$table) {
            \$table->id();
            \$table->string('word')->unique();
            \$table->timestamps();
        });
EOD
);

// 11. Negative Words
updateMigration($dir, '_create_negative_words_table.php', <<<EOD
Schema::create('negative_words', function (Blueprint \$table) {
            \$table->id();
            \$table->string('word')->unique();
            \$table->timestamps();
        });
EOD
);

// 12. Currency Rates
updateMigration($dir, '_create_currency_rates_table.php', <<<EOD
Schema::create('currency_rates', function (Blueprint \$table) {
            \$table->id();
            \$table->string('base_currency', 10)->default('USD');
            \$table->string('target_currency', 10);
            \$table->decimal('rate', 15, 6);
            \$table->timestamp('last_updated_at')->nullable();
            \$table->timestamps();
            
            \$table->unique(['base_currency', 'target_currency']);
        });
EOD
);

// 13. Currency Rate Histories
updateMigration($dir, '_create_currency_rate_histories_table.php', <<<EOD
Schema::create('currency_rate_histories', function (Blueprint \$table) {
            \$table->id();
            \$table->string('base_currency', 10)->default('USD');
            \$table->string('target_currency', 10);
            \$table->decimal('rate', 15, 6);
            \$table->date('record_date');
            \$table->timestamps();
            
            \$table->unique(['base_currency', 'target_currency', 'record_date'], 'crh_unique');
        });
EOD
);

// 14. Weather Data
updateMigration($dir, '_create_weather_data_table.php', <<<EOD
Schema::create('weather_data', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('country_id')->constrained()->onDelete('cascade');
            \$table->decimal('temperature', 5, 2)->nullable();
            \$table->decimal('precipitation', 8, 2)->nullable(); // mm
            \$table->decimal('wind_speed', 5, 2)->nullable(); // km/h
            \$table->string('condition')->nullable(); // Clear, Rain, Storm, etc.
            \$table->timestamp('last_updated_at')->nullable();
            \$table->timestamps();
        });
EOD
);

// 15. Api Logs
updateMigration($dir, '_create_api_logs_table.php', <<<EOD
Schema::create('api_logs', function (Blueprint \$table) {
            \$table->id();
            \$table->string('service_name'); // OpenMeteo, GNews, etc.
            \$table->string('endpoint');
            \$table->integer('status_code');
            \$table->text('response_message')->nullable();
            \$table->timestamps();
        });
EOD
);

// 16. Country Comparisons
updateMigration($dir, '_create_country_comparisons_table.php', <<<EOD
Schema::create('country_comparisons', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->onDelete('cascade');
            \$table->foreignId('country_1_id')->constrained('countries')->onDelete('cascade');
            \$table->foreignId('country_2_id')->constrained('countries')->onDelete('cascade');
            \$table->timestamps();
        });
EOD
);

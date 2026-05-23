<?php

namespace App\Console\Commands;

use App\Models\WildfireIncident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;

class ImportWildfires extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fire:import {path : The path to the CSV file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import wildfire incidents from a CSV file';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Importing wildfire incidents from {$path}...");

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $this->error("Failed to open file: {$path}");
            return 1;
        }

        // Get header
        $header = fgetcsv($handle);
        if ($header === false) {
            $this->error("Failed to read header from CSV.");
            fclose($handle);
            return 1;
        }

        $rowCount = 0;
        $successCount = 0;
        $errorCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $rowCount++;
            $rowData = array_combine($header, $data);

            if ($rowData === false) {
                $this->warn("Skipping row {$rowCount}: Column count mismatch.");
                $errorCount++;
                continue;
            }

            $validator = Validator::make($rowData, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'burned_area' => 'required|numeric',
                'fire_date' => 'required|date',
                'severity' => 'required|in:Low,Medium,High,Extreme',
                'country' => 'required|string',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer',
                'duration' => 'required|integer',
                'name' => 'required|string',
            ]);

            if ($validator->fails()) {
                $this->warn("Skipping row {$rowCount}: " . implode(', ', $validator->errors()->all()));
                $errorCount++;
                continue;
            }

            try {
                WildfireIncident::create($validator->validated());
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to import row {$rowCount}: " . $e->getMessage());
                $errorCount++;
            }
        }

        fclose($handle);

        $this->info("Import completed!");
        $this->info("Total rows processed: {$rowCount}");
        $this->info("Successfully imported: {$successCount}");
        $this->info("Failed/Skipped: {$errorCount}");

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;

class ImportMasterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:masterdata';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import master data from CSV files into the API Webshop database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Define CSV file paths
        $customerCsvPath = storage_path('app/public/customers.csv');
        $productCsvPath = storage_path('app/public/products.csv');

        // Import Customer data
        $this->info('Importing Customer data...');
        $customerData = $this->importCsv($customerCsvPath, ['ID', 'Job Title', 'Email Address', 'FirstName LastName', 'registered_since', 'phone'], 'Customer');


        // Import Product data
        $this->info('Importing Product data...');
        $productData = $this->importCsv($productCsvPath, ['ID', 'productname', 'price'], 'Product');

        // Log import results into Laravel.log file
        Log::info('Import completed successfully.');
        Log::info("Imported {$customerData['imported']} Customer records");
        Log::info("Imported {$productData['imported']} Product records");
        Log::info("Skipped {$customerData['skipped']} Customer records (already existing)");
        Log::info("Skipped {$productData['skipped']} Product records (already existing)");

        // Log import results into console
        $this->info('Import completed successfully.');
        $this->info("Imported {$customerData['imported']} Customer records");
        $this->info("Imported {$productData['imported']} Product records");
        $this->info("Skipped {$customerData['skipped']} Customer records (already existing)");
        $this->info("Skipped {$productData['skipped']} Product records (already existing)");
    }

    // Import CSV data
    private function importCsv($filePath, $columns, $model): array
    {
        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);
        $data = $csv->getRecords($columns);

        $imported = 0;
        $skipped = 0;

        foreach ($data as $record)
        {
            // Check if the record already exists
            $existingRecord = DB::table(strtolower($model) . 's')->where('ID', $record['ID'])->first();

            // Remove the "ID" column from the record
            unset($record['ID']);

            // Define a mapping array for column name changes
            $columnMappings = [
                'FirstName LastName' => ['first_name', 'last_name'],
                'Job Title' => ['job_title'],
                'Email Address' => ['email'],
            ];

            // Iterate through the mapping array
            foreach ($columnMappings as $oldColumnName => $newColumnNames)
            {
                // Check if the old column name exists
                if (isset($record[$oldColumnName]))
                {
                    // Split "FirstName LastName" into separate "first_name" and "last_name" columns
                    if ($oldColumnName === 'FirstName LastName')
                    {
                        $nameParts = explode(' ', $record[$oldColumnName], 2);
                        $record['first_name'] = $nameParts[0] ?? '';
                        $record['last_name'] = $nameParts[1] ?? '';
                    }
                    else
                    {
                        // Iterate through the new column names
                        foreach ($newColumnNames as $newColumnName)
                        {
                            // If the new column name is different from the old one, set it
                            if ($oldColumnName !== $newColumnName)
                            {
                                $record[$newColumnName] = $record[$oldColumnName];
                            }
                        }
                    }

                    // Remove the old column name from the record
                    unset($record[$oldColumnName]);
                }
            }

            if (isset($record['registered_since']))
            {
                $dateString = $record['registered_since'];

                // Extract the date portion and parse it using DateTime
                preg_match('/[A-Za-z]+\s*,\s*([A-Za-z]+\s*\d{1,2}\s*,\s*\d{4})/', $dateString, $matches);

                if (isset($matches[1]))
                {
                    // Parse the date using DateTime
                    $extractedDate = DateTime::createFromFormat('F j, Y', $matches[1]);

                    if ($extractedDate !== false)
                    {
                        // Format the extracted date to 'Y-m-d' using Carbon
                        $formattedDate = \Carbon\Carbon::parse($extractedDate)->format('Y-m-d');
                        $record['registered_since'] = $formattedDate;
                    }
                }
            }

            // Set timestamps
            $record['created_at'] = now();
            $record['updated_at'] = now();

            if (!$existingRecord)
            {
                // Record doesn't exist, import it
                DB::table(strtolower($model) . 's')->insert($record);
                $imported++;
            }
            else {
                // Record already exists, skip it
                $skipped++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }
}

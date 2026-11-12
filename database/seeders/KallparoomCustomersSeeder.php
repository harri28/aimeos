<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Imports Kallparoom customer rows from a private CSV file.
 * Copy the CSV to storage/app/private/kallparoom_customers.csv, then run:
 * php artisan db:seed --class='Database\Seeders\KallparoomCustomersSeeder'
 * Run after `php artisan aimeos:setup` has created the Aimeos tables.
 */
class KallparoomCustomersSeeder extends Seeder
{
    private const EXPECTED_ROWS = 73;
    private const SITE_ID = '1.';
    private const CSV_PATH = 'app/private/kallparoom_customers.csv';

    public function run(): void
    {
        $path = storage_path(self::CSV_PATH);
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Customer import CSV not found: ' . $path);
        }

        try {
            $headers = fgetcsv($handle);
            $required = [
                'siteid', 'code', 'label', 'firstname', 'lastname', 'address1',
                'city', 'state', 'langid', 'countryid', 'email', 'password', 'status',
            ];

            if (!is_array($headers) || array_diff($required, $headers)) {
                throw new RuntimeException('Customer import CSV is missing required columns.');
            }

            $records = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count($headers) !== count($values)) {
                    throw new RuntimeException('Customer import CSV contains an invalid row.');
                }

                $record = array_combine($headers, $values);
                if ($record['siteid'] !== self::SITE_ID || !preg_match('/^\d{8}$/', $record['code'])) {
                    throw new RuntimeException('Customer import CSV contains an invalid site ID or DNI.');
                }

                $records[] = array_intersect_key($record, array_flip($required));
            }
        } finally {
            fclose($handle);
        }

        $codes = array_column($records, 'code');
        if (count($records) !== self::EXPECTED_ROWS || count(array_unique($codes)) !== self::EXPECTED_ROWS) {
            throw new RuntimeException('Customer import CSV must contain exactly 73 unique customers.');
        }

        $now = now()->toDateTimeString();
        DB::transaction(function () use ($records, $now): void {
            foreach ($records as $record) {
                $key = [
                    'siteid' => $record['siteid'],
                    'code' => $record['code'],
                ];

                unset($record['siteid'], $record['code']);
                $record['status'] = (int) $record['status'];
                $record['mtime'] = $now;
                $record['ctime'] = $now;
                $record['editor'] = 'kallparoom-customer-seeder';

                DB::table('mshop_customer')->updateOrInsert($key, $record);
            }
        });
    }
}
<?php

namespace Database\Seeders;

use Aimeos\MShop;
use Aimeos\MShop\Exception as MShopException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Imports Kallparoom customers from storage/app/private/kallparoom_customers.csv.
 * Run after `php artisan aimeos:setup`:
 * php artisan db:seed --class='Database\Seeders\KallparoomCustomersSeeder'
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
                'city', 'state', 'langid', 'countryid', 'email', 'status',
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

        $context = app('aimeos.context')->get(false, 'command');
        $context->setEditor('kallparoom-customer-seeder');
        $localeManager = MShop::create($context, 'locale');
        $locale = $localeManager->bootstrap(config('shop.mshop.locale.site', 'default'), '', '', false, null, true);
        $context->setLocale($locale);
        $manager = MShop::create($context, 'customer');
        $processed = 0;

        foreach ($records as $record) {
            try {
                $item = $manager->find($record['code']);
                $new = false;
            } catch (MShopException $exception) {
                $item = $manager->create();
                $new = true;
            }

            $item->setCode($record['code']);
            $item->setLabel($record['label']);
            $item->setStatus((int) $record['status']);

            if ($new) {
                $item->setPassword(bin2hex(random_bytes(32)));
            }

            $address = $item->getPaymentAddress();
            $address->setFirstname($record['firstname']);
            $address->setLastname($record['lastname']);
            $address->setEmail($record['email']);
            $address->setAddress1($record['address1']);
            $address->setCity($record['city']);
            $address->setState($record['state']);
            $address->setLanguageId($record['langid']);
            $address->setCountryId($record['countryid']);

            $manager->save($item);
            $processed++;
        }

        $removed = DB::table('mshop_customer')
            ->where('siteid', self::SITE_ID)
            ->where('editor', 'kallparoom-customer-seeder')
            ->delete();

        $this->command?->info("Processed {$processed} customers; removed {$removed} rows from the unused Aimeos customer table.");
    }
}
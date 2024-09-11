<?php

namespace Database\Seeders;

use App\Models\Staff;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Uid\Ulid;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = base_path('database/data/staffs.csv');
        $chunkSize = 1; // Adjust the chunk size as needed

        foreach ($this->csvToArray($filePath, $chunkSize) as $chunk) {
            $staffs = [];

            foreach ($chunk as $staff) {
                Log::info('staff', $staff);
                if ($staff["﻿name"] == '') {
                    continue;
                }

                $staffs[] =
                    [
                        'id' => (string) Ulid::generate(),
                        'name' => $staff["﻿name"],
                        'nid' => $staff['nid'],
                        'staff_code' => $staff['staff_code'],
                        'phone' => $staff['phone'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
            }

            Staff::insert($staffs);
        }
    }

    private function csvToArray($csvFile, $chunkSize = 1000)
    {
        $file_to_read = fopen($csvFile, 'r');
        if (! $file_to_read) {
            return []; // Handle file opening error
        }

        // Read the header
        $header = fgetcsv($file_to_read, 1000, ',');
        if (! $header) {
            fclose($file_to_read);

            return []; // Handle empty file or header reading error
        }

        $data = [];
        $rowCounter = 0;
        while (($line = fgetcsv($file_to_read, 1000, ',')) !== false) {
            $data[] = array_combine($header, $line);
            $rowCounter++;

            // If the chunk size is reached, return the chunk and reset the array
            if ($rowCounter % $chunkSize === 0) {
                yield $data;
                $data = [];
            }
        }

        // Yield any remaining data
        if (! empty($data)) {
            yield $data;
        }

        fclose($file_to_read);
    }
}

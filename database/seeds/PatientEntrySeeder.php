<?php

use Illuminate\Database\Seeder;

class PatientEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('patient_entries')->insert([
            'first_name' => str_random(14),
            'last_name' => str_random(14),
            'pdf_file_id' => -1,
            'pdf_html_id' => -1
        ]);
    }
}

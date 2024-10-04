<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPersonAddresses;
use App\Models\Person\NonNaturalPersonAddresses;
use App\Models\Person\NaturalPersonIdentificationDocument;
use App\Models\Person\AdditionalInfo;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LookupTypesSeeder::class);
        $this->call(RoleAndPermissionSeeder::class);
    }
}

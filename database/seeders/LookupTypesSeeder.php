<?php

namespace Database\Seeders;

use App\Models\BusinessAddress;
use App\Models\BusinessComposition;
use App\Models\LookupType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LookupTypesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run()
    {
        LookupType::insert([
            [
                'name' => 'Registered Business Address',
                'group' => BusinessAddress::class,
                'type' => 'REGISTERED_BUSINESS_ADDRESS'
            ],
            [
                'name' => 'Operational Business Address',
                'group' => BusinessAddress::class,
                'type' => 'OPERATIONAL_BUSINESS_ADDRESS'
            ],
            [
                'name' => 'UBO Position',
                'group' => BusinessComposition::POSITION_LOOKUP_GROUP,
                'type' => 'UBO'
            ],
            [
                'name' => 'Director Position',
                'group' => BusinessComposition::POSITION_LOOKUP_GROUP,
                'type' => 'DIR'
            ],
            [
                'name' => 'Signatory Position',
                'group' => BusinessComposition::POSITION_LOOKUP_GROUP,
                'type' => 'SIG'
            ],
            [
                'name' => 'Shareholder Position',
                'group' => BusinessComposition::POSITION_LOOKUP_GROUP,
                'type' => 'SH'
            ],
        ]);
    }
}

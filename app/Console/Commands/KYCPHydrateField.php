<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KYCP\Facades\KYCP;
use App\Models\LookupType;
use Illuminate\Support\Arr;

class KYCPHydrateField extends Command
{
    protected $signature = 'kycp:hydrate-fields';
    protected $description = 'Hydrate table for KYCP values';
    protected $programId;
    protected $model;
    protected $mappingPath;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->line('Hydrating table based from KYCP...');
        $this->line('');
        $this->line('Make sure you have the following value');
        $this->line('');
        $this->line('Program ID (integer) check the KYCP Portal');
        $this->line('Model Path (table) (e.g. App\Models\COT\Field)');
        $this->line('Mapping Resources (file/constant) (e.g. constants/bp-kycp.php)');

        if ($this->confirm('Do you wish to continue?')) {
            $this->programId = $this->ask("Program ID");
            $this->model = $this->ask("Model Path");
            $this->mappingPath = $this->ask("Mapping Resources Path");
            $this->line('');

            $this->line('Fetching Program Id: '. $this->programId);
            $entities = KYCP::getEntities($this->programId)->json();

            foreach ($entities['Values'] as $entity) {
                $getFields = KYCP::getEntityFields($this->programId, $entity['id'])->json();
                $this->line('Updated Entity ID: '. $entity['id']);
                $this->line('..................................');
                $this->addFields($getFields['Fields'], $getFields['EntityTypeId'], $id);
            }
            $this->line('');
        }
        $this->line('Updated Successfully!');
        return 0;
    }

    public function getResources(): array
    {
        return include(resource_path($this->mappingPath));
    }

    public function addFields(array $allFields, int $entityTypeId, int $programId)
    {
        foreach ($allFields as $key => $field) {
            if (! Arr::isAssoc($field)) {
                foreach ($field[0] as $index => $value) {
                    $this->addOrUpdateField($programId, $entityTypeId, $index, $value);
                }
                $field = $field[0];
            }
            $this->addOrUpdateField($programId, $entityTypeId, $key, $field);
        }
    }

    private function addOrUpdateField($programId, $entityTypeId, $key, $field)
    {
        $resource = $this->getResources();

        $mappingKey = $this->model::where([
            'program_id' => $programId,
            'entity_id' => $entityTypeId,
            'key' => $key,
        ])->first();


       if ($mappingKey) {
            $mappingKey->update([
                'type' => $field['KycpDataType'] ?? null,
                'lookup_id' => $field['KycpLookupTypeId'] ?? null,
                'repeater' => isset($field['KycpRepeater']) ? true : false,
                'required' => isset($field['KycpRequired']) ?? false,
                'mapping_table' => $resource[$entityTypeId]['fields'][$key] ?? null
            ]);
       } else {
            $this->model::firstOrCreate([
                'program_id' => $programId,
                'entity_id' => $entityTypeId,
                'key' => $key,
                'type' => $field['KycpDataType'] ?? null,
                'lookup_id' => $field['KycpLookupTypeId'] ?? null,
                'repeater' => isset($field['KycpRepeater']) ? true : false,
                'required' => isset($field['KycpRequired']) ?? false,
                'mapping_table' => $resource[$entityTypeId]['fields'][$key] ?? null
            ]);

       }
    }
}

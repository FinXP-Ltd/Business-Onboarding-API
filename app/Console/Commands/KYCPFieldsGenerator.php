<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\KYCP\Facades\KYCP;
use App\Models\LookupType;
use Illuminate\Support\Arr;

class KYCPFieldsGenerator extends Command
{
    protected $signature = 'kycp:generate-fields';
    protected $description = 'Lookup table for KYCP values';
    protected $programId;
    protected $model;
    protected $mappingPath;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
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

            $this->line('Generate fields based from KYCP');
            $this->line('Fetching Program Id: '. $this->programId);
            $entities = KYCP::getEntities($this->programId)->json();

            foreach ($entities['Values'] as $entity) {
                $getFields = KYCP::getEntityFields($this->programId, $entity['id'])->json();
                $this->line('Adding Entity ID: '. $entity['id']);
                $this->line('..................................');
                $this->addFields($getFields['Fields'], $getFields['EntityTypeId'], $this->programId);
            }
            $this->line('');
            $this->line('Added Successfully!');
            return 0;
        }
    }

    public function getResources(): array
    {
        return include(resource_path($this->mappingPath));
    }

    public function addFields(array $allFields, int $entityTypeId, int $programId)
    {
        foreach ($allFields as $key => $field) {
            if (! Arr::isAssoc($field)) {
                $field = $field[0];
            }

            $this->addField($programId, $entityTypeId, $key, $field);

            if (isset($field['KycpLookupTypeId'])) {
                $this->addLookupTypes($key, $field);
            }
        }
    }

    public function addField($programId, $entityTypeId, $key, $field)
    {
        $resource = $this->getResources();

        if (! Arr::isAssoc($field)) {
            $field = $field[0];
        }
        $mapping =isset($resource[$entityTypeId]['fields'][$key]) ? $resource[$entityTypeId]['fields'][$key]: null;

        $this->model::firstOrCreate([
            'program_id' => $programId,
            'entity_id' => $entityTypeId,
            'key' => $key,
            'type' => $field['KycpDataType'] ?? null,
            'lookup_id' => $field['KycpLookupTypeId'] ?? null,
            'repeater' => isset($field['KycpRepeater']) ? true : false,
            'required' => isset($field['KycpRequired']) ?? false,
            'mapping_table' => (is_array($mapping)) ? json_encode($mapping) : $mapping
        ]);
    }

    public function addLookupTypes($key, array $fields)
    {
        $resource = $this->getResources();
        $lookup = KYCP::getLookupOptions($fields['KycpLookupTypeId'])->json();

        foreach ($lookup["Values"] as $type) {
            LookupType::firstOrCreate([
                'name' => $type['name'],
                'description' => isset($resource['label'][$key]) ? $resource['label'][$key] : null,
                'type' => $fields['KycpDataType'] ?? null,
                'group' => $key,
                'lookup_type_id' => $fields['KycpLookupTypeId'],
                'lookup_id' => $type['id'],
            ]);
        }
    }
}

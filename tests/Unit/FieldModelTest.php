<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\COT\Field;

class FieldModelTest extends TestCase
{
    protected $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Field();
        Field::query()->forceDelete();
    }

    public function testItShouldGetByProgramIdAndEntityId()
    {
        $programId = rand(1, 2);
        $entityId = rand(1, 2);

        $items = Field::factory()->create([
            'program_id' => $programId,
            'entity_id' => $entityId,
            'key' => 'GenName'
        ]);

        $models = Field::getByProgramIdAndEntityId($programId, $entityId);
        $this->assertEquals($items->count(), $models->count());
    }

    public function testItShouldTransformToFieldType()
    {
        $this->assertEquals('datetime', Field::$cotFields[Field::COT_DATETIME]);
        $this->assertEquals('date', Field::$cotFields[Field::COT_DATE]);
        $this->assertEquals('select', Field::$cotFields[Field::COT_LOOKUP]);
        $this->assertEquals('textarea', Field::$cotFields[Field::COT_FREETEXT]);
        $this->assertEquals('text', Field::$cotFields[Field::COT_STRING]);
        $this->assertEquals('number', Field::$cotFields[Field::COT_INTEGER]);
        $this->assertEquals('number', Field::$cotFields[Field::COT_DECIMAL]);
    }

    public function testItShouldQueryScopeProgram()
    {
        $programId = rand(1, 2);

        Field::factory()->create([
            'program_id' => $programId,
            'entity_id' => 1,
            'key' => 'GenName'
        ]);

        $fields = Field::associatedProgram($programId)->get();
        $selected = rand(0, sizeof($fields) - 1);

        $this->assertEquals($programId, $fields[$selected]->program_id);
    }

    public function testItShouldQueryScopeEntity()
    {
        $entityId = rand(1, 2);

        Field::factory()->create([
            'program_id' => 1,
            'entity_id' => $entityId,
            'key' => 'GenTitle'
        ]);

        $fields = Field::associatedEntity($entityId)->get();
        $selected = rand(0, sizeof($fields) - 1);

        $this->assertEquals($entityId, $fields[$selected]->entity_id);
    }
}

<?php

namespace App\Models\COT;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Field extends Model
{
    use HasFactory;

    const COT_DATETIME = 'DateTime';
    const COT_DATE = 'Date';
    const COT_LOOKUP = 'Lookup';
    const COT_FREETEXT = 'FreeText';
    const COT_STRING = 'String';
    const COT_INTEGER = 'Integer';
    const COT_DECIMAL = 'Decimal';
    const COT_MOBILE = 'Mobile';
    const COT_URL = 'Url';

    public static $cotFields = [
        self::COT_DATETIME  => 'datetime',
        self::COT_DATE      => 'date',
        self::COT_LOOKUP    => 'select',
        self::COT_FREETEXT  => 'textarea',
        self::COT_STRING    => 'text',
        self::COT_INTEGER   => 'number',
        self::COT_DECIMAL   => 'number',
        self::COT_MOBILE    => 'mobile',
        self::COT_URL       => 'url'
    ];

    public $timestamps = false;

    protected $table = 'kycp_fields';

    protected $fillable = [
        'program_id', 'entity_id', 'key', 'type', 'lookup_id',
        'repeater', 'required','internal','mapping_table'
    ];

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'repeater' => 'boolean',
        'required' => 'boolean'
    ];

    public static function getByProgramIdAndEntityId($programId, $entityId)
    {
        return self::associatedProgram($programId)
            ->associatedEntity($entityId)
            ->get();
    }

    public function scopeAssociatedProgram(Builder $query, $program)
    {
        return $query->where('program_id', $program);
    }

    public function scopeAssociatedEntity(Builder $query, $entity)
    {
        return $query->where('entity_id', $entity);
    }
}

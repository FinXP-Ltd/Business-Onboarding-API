<?php

namespace App\Models;

use Database\Factories\LookupTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LookupType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'group', 'type', 'lookup_type_id', 'lookup_id'];
    protected $visible = ['id', 'lookup_id', 'name'];


    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return LookupTypeFactory::new();
    }
}

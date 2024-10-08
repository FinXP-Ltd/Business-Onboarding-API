<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Declaration extends Model
{
    use HasFactory;

    protected $fillable = ['company_information_id', 'file_name', 'file_type', 'size'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}

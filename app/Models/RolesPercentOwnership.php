<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RolesPercentOwnership extends Model
{
    use HasFactory;
    use HasUuids;
    protected $table = 'roles_percent_ownership';
    protected $fillable = ['roles_in_company', 'iban4u_rights', 'percent_ownership' , 'index', 'order'];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_representative_id'];
}

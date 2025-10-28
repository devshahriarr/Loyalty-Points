<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use TenantAwareModel;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'points_required',
        'status',
        'tenant_id',
    ];
}

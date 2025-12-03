<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantPermission extends Model
{
    protected $fillable = [];

    public function tenant(){
        return $this->belongsTo(Tenant::class);
    }
}

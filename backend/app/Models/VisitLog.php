<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitLog extends Model
{
    use TenantAwareModel;
    
    protected $fillable = [
        'customer_id',
        'branch_id',
        'visit_date',
        'notes',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

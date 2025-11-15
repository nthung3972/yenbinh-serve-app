<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingFee extends Model
{
    use HasFactory;
    protected $table = 'building_fees';

    protected $primaryKey = 'building_fee_id';

    protected $fillable = [
        'fee_types_id', 'fee_subtypes_id', 'building_id', 'price', 'unit', 'effective_from', 'effective_to', 'note'
    ];

    public function feeType()
    {
        return $this->belongsTo(FeeType::class, 'fee_types_id', 'fee_types_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function feeSubtype()
    {
        return $this->belongsTo(FeeSubtype::class, 'fee_subtypes_id', 'fee_subtypes_id');
    }
}

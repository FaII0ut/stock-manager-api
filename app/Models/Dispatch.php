<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispatch extends Model
{
    use HasFactory, SoftDeletes, HasUlids;

    protected $fillable = [
        'staff_id',
        'item_id',
        'quantity',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

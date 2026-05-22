<?php

namespace App\Models;

use Database\Factories\McuLabResultFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McuLabResult extends Model
{
    /** @use HasFactory<McuLabResultFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'registration_id',
        'lab_user_id',
        'item_id',
        'nilai',
        'keterangan',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(McuRegistration::class);
    }

    public function labUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lab_user_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(McuPackageItem::class);
    }
}

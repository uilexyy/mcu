<?php

namespace App\Models;

use Database\Factories\McuRadiologyResultFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class McuRadiologyResult extends Model
{
    /** @use HasFactory<McuRadiologyResultFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'registration_id',
        'radio_user_id',
        'interpretasi',
        'file_path',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(McuRegistration::class);
    }

    public function radioUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'radio_user_id');
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_path ? Storage::url($this->file_path) : null,
        );
    }
}

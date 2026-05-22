<?php

namespace App\Models;

use App\Traits\LogActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class McuRegistration extends Model
{
    use HasFactory, LogActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'status',
        'tanggal_jadwal',
        'catatan_admin',
        'foto_ktp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_jadwal' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(McuPackage::class);
    }

    public function physicalExam(): HasOne
    {
        return $this->hasOne(McuPhysicalExam::class, 'registration_id');
    }

    public function labResults(): HasMany
    {
        return $this->hasMany(McuLabResult::class, 'registration_id');
    }

    public function radiologyResult(): HasOne
    {
        return $this->hasOne(McuRadiologyResult::class, 'registration_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(McuResult::class, 'registration_id');
    }

    public function getActivityLabel(): string
    {
        return "{$this->user?->name} - {$this->package?->nama_paket} (#{$this->id})";
    }
}

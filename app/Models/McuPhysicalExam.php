<?php

namespace App\Models;

use App\Traits\LogActivity;
use Database\Factories\McuPhysicalExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McuPhysicalExam extends Model
{
    /** @use HasFactory<McuPhysicalExamFactory> */
    use HasFactory, LogActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'registration_id',
        'doctor_id',
        'tekanan_darah',
        'berat_badan',
        'tinggi_badan',
        'imt',
        'anamnesis',
        'catatan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'berat_badan' => 'decimal:2',
            'tinggi_badan' => 'decimal:2',
            'imt' => 'decimal:2',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(McuRegistration::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function getActivityLabel(): string
    {
        return "Registration #{$this->registration_id}";
    }
}

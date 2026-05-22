<?php

namespace App\Models;

use Database\Factories\McuResultFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class McuResult extends Model
{
    /** @use HasFactory<McuResultFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'registration_id',
        'pdf_path',
        'generated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(McuRegistration::class);
    }

    protected function pdfUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->pdf_path ? Storage::url($this->pdf_path) : null,
        );
    }
}

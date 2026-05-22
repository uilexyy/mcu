<?php

namespace App\Models;

use App\Traits\LogActivity;
use Database\Factories\McuPackageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McuPackage extends Model
{
    /** @use HasFactory<McuPackageFactory> */
    use HasFactory, LogActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_paket',
        'deskripsi',
        'harga',
        'is_active',
        'has_radiologi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'is_active' => 'boolean',
            'has_radiologi' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(McuPackageItem::class, 'package_id');
    }
}

<?php

namespace App\Models;

use Database\Factories\McuPackageItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McuPackageItem extends Model
{
    /** @use HasFactory<McuPackageItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'package_id',
        'nama_pemeriksaan',
        'satuan',
        'nilai_normal',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(McuPackage::class, 'package_id');
    }
}

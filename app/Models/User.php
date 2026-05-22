<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\LogActivity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, LogActivity, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'gelar_depan',
        'gelar_belakang',
        'email',
        'password',
        'nip',
        'departemen',
        'tanggal_lahir',
        'jenis_kelamin',
        'role',
        'signature',
    ];

    protected $appends = ['nama_lengkap'];

    public function getNamaLengkapAttribute(): string
    {
        $gelarDepan = $this->gelar_depan ? $this->gelar_depan.' ' : '';
        $gelarBelakang = $this->gelar_belakang ? ', '.$this->gelar_belakang : '';

        return $gelarDepan.$this->name.$gelarBelakang;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature ? Storage::url($this->signature) : null;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(McuRegistration::class);
    }
}

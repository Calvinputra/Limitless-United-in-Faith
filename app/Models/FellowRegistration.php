<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FellowRegistration extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'gender',
        'umur',
        'whatsapp',
        'gereja_lokal',
        'bukti_tf_path',
        'team',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'umur' => 'integer',
            'team' => 'integer',
        ];
    }

    public function buktiTfUrl(): string
    {
        return route('admin.bukti-tf', $this);
    }

    public function hasBuktiTfPath(): bool
    {
        return is_string($this->bukti_tf_path) && $this->bukti_tf_path !== '';
    }

    public function hasBuktiTf(): bool
    {
        return $this->hasBuktiTfPath()
            && Storage::disk('public')->exists($this->bukti_tf_path);
    }

    public function buktiTfMissing(): bool
    {
        return $this->hasBuktiTfPath() && ! $this->hasBuktiTf();
    }

    public function teamLabel(): string
    {
        return $this->team ? 'Tim '.$this->team : 'Belum';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    /** @use HasFactory<\Database\Factories\KamarFactory> */
    use HasFactory;

    protected $table = 'kamar';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'nomor_kamar',
        'tipe_kamar',
        'tarif',
        'kapasitas',
        'status_kamar',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tarif' => 'decimal:2',
            'kapasitas' => 'integer',
        ];
    }
}

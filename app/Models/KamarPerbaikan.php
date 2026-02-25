<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KamarPerbaikan extends Model
{
    protected $table = 'kamar_perbaikan';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'kamar_id',
        'mulai',
        'selesai',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mulai' => 'date',
            'selesai' => 'date',
        ];
    }
}

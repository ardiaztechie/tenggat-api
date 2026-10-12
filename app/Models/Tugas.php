<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    // Nama tabel ditulis eksplisit agar tidak salah tebak bentuk jamak.
    protected $table = 'tugas';

    protected $fillable = [
        'judul', 'mata_kuliah', 'tenggat', 'prioritas', 'selesai', 'selesai_pada', 'catatan', 'foto',
    ];

    protected $hidden = ['foto'];

    protected $appends = ['foto_url'];

    // Alamat foto lewat route API (tanpa storage:link). Null jika tidak ada foto.
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto
            ? url('/api/tugas/'.$this->id.'/foto').'?v='.($this->updated_at?->timestamp ?? 0)
            : null;
    }

    // Tanggal sengaja tidak di-cast supaya dikirim apa adanya ("2026-10-17 23:59:00"),
    // tanpa konversi zona waktu.
    protected $casts = [
        'selesai' => 'boolean',
    ];
}

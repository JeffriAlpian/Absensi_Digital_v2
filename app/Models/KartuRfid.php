<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuRfid extends Model
{
    protected $table = 'kartu_rfid';

    protected $guarded = ['id'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function device()
    {
        return $this->belongsTo(RfidModel::class, 'device_id');
    }

}

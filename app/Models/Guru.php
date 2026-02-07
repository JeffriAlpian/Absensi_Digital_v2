<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru';
    protected $guarded = ['id'];

    public static function hitungGuru()
    {
        return self::count();
    }

    public function waliKelas()
    {
        return $this->hasOne(WaliKelas::class, 'guru_id');
    }

    public function kartuRfid()
    {
        return $this->hasOne(KartuRfid::class, 'guru_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'guru_id');
    }
}

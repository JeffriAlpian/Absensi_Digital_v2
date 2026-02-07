<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfidModel extends Model
{
    protected $table = 'rfid_model';

    protected $guarded = ['id'];

     public function kartuRfid()
    {
        return $this->hasMany(KartuRfid::class, 'device_id');
    }
}

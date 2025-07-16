<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacsAutorizados extends Model
{
    protected $table = 'macs_autorizados';
    protected $fillable = ['mac', 'placa', 'data_adicao'];
    public $timestamps = true;
}

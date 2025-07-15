<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telemetria extends Model
{
  protected $table = 'telemetria';
  protected $fillable = ['mac', 'direcao', 'datahora', 'status'];
}

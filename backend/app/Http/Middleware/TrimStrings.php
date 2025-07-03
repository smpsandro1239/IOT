<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class TrimStrings extends TransformsRequest
{
  protected $except = [
    'current_password',
    'password',
    'password_confirmation',
  ];

  protected function transform($key, $value)
  {
    if (in_array($key, $this->except, true)) {
      return $value;
    }

    return is_string($value) ? trim($value) : $value;
  }
}

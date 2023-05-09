<?php

namespace Laraditz\Payex\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayexMessage extends Model
{
    use HasFactory;

    protected $fillable = ['action', 'request', 'response'];
}

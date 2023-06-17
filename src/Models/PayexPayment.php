<?php

namespace Laraditz\Payex\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PayexPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'ref_no', 'txn_id', 'currency_code', 'amount', 'status', 'status_description', 'payment_status', 'payment_description',
        'customer_name', 'email', 'contact_no', 'description', 'return_url', 'callback_url', 'metadata', 'response', 'callback_response'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'json',
        'response' => 'json',
        'callback_response' => 'json',
    ];

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->id ?? (string) Str::orderedUuid();
        });
    }
}

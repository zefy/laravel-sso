<?php

namespace Zefy\LaravelSSO\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrokerUser extends Model
{
    use SoftDeletes;
    /**
    * Get the table associated with the model.
    *
    * @return string
    */
    public function getTable()
    {
        return config('laravel-sso.brokerUserTable', 'broker_user');
    }
}

<?php

namespace Zefy\LaravelSSO\Models;

use Illuminate\Database\Eloquent\Model;

class Broker extends Model
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('laravel-sso.brokersTable', 'brokers');
    }
}

<?php

namespace Zefy\LaravelSSO\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateBroker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:broker:create {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating new SSO broker.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $brokerClass = app(config('laravel-sso.brokersModel'));
        $broker = new $brokerClass;

        $broker->name = $this->argument('name');
        $broker->secret = Str::random(40);

        $broker->save();

        $this->info('Broker with name `' . $this->argument('name') . '` successfully created.');
        $this->info('Secret: ' . $broker->secret);
    }
}

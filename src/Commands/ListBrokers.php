<?php

namespace Zefy\LaravelSSO\Commands;

use Illuminate\Console\Command;

class ListBrokers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:broker:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all created brokers.';

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
        $headers = ['ID', 'Name', 'Secret'];

        $brokerClass = app(config('laravel-sso.brokersModel'));
        $brokers = $brokerClass::all(['id', 'name', 'secret'])->toArray();

        $this->table($headers, $brokers);
    }
}

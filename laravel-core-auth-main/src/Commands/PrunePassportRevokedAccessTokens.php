<?php

namespace AttractCores\LaravelCoreAuth\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Bridge\RefreshToken;
use Laravel\Passport\Token;

class PrunePassportRevokedAccessTokens extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kit:passport:prune-revoked-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command prune existing revoked tokens.';

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
     * @throws \Exception
     */
    public function handle()
    {
        $this->pruneOldTokens();
        $this->pruneOldRefreshTokens();

        $this->info('Done!!');
    }

    /**
     * Prune old revoked access tokens.
     *
     * @throws \Exception
     */
    protected function pruneOldTokens()
    {
        \DB::table('oauth_access_tokens')->where('revoked', true)->delete();
    }

    /**
     * Prune old revoked refresh tokens.
     *
     * @throws \Exception
     */
    protected function pruneOldRefreshTokens()
    {
        \DB::table('oauth_refresh_tokens')->where('revoked', true)->delete();
    }
}

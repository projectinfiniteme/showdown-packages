<?php

namespace AttractCores\LaravelCoreAuth\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Token;

class PassportRevokeExpiredTokens extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kit:passport:revoke-expired-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command revoke expired tokens.';

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
        $this->revokeOldTokens();
        $this->revokeOldRefreshTokens();

        $this->info('Done!!');
    }

    /**
     * Revoke old revoked access tokens.
     *
     * @throws \Exception
     */
    protected function revokeOldTokens()
    {
        \DB::table('oauth_access_tokens')->where('revoked', false)->where('expires_at', '<', now())->update([ 'revoked' => true ]);
    }

    /**
     * Revoke old revoked refresh tokens.
     *
     * @throws \Exception
     */
    protected function revokeOldRefreshTokens()
    {
        \DB::table('oauth_refresh_tokens')->where('revoked', false)->where('expires_at', '<', now())->update([ 'revoked' => true ]);
    }
}

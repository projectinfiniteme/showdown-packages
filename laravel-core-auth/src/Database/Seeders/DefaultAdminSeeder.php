<?php

namespace AttractCores\LaravelCoreAuth\Database\Seeders;

use AttractCores\LaravelCoreAuth\Resolvers\CoreUser;
use Illuminate\Database\Seeder;

/**
 * Class DefaultAdminSeeder
 *
 * @package AttractCores\LaravelCoreAuth\Database\Seeders
 * Date: 16.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class DefaultAdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if ( ! CoreUser::byEmail(config('kit-auth.start-user.email'))->first() ) {
            CoreUser::factory()->admin()->name('Attract', 'Admin')->make([
                'email'    => config('kit-auth.start-user.email'),
                'password' => config('kit-auth.start-user.password'),
            ])->save();
        }
    }

}
<?php

namespace Tests\Unit;

use AttractCores\LaravelCoreVerificationBroker\Contracts\VerificationBrokerContract;
use AttractCores\LaravelCoreVerificationBroker\Models\VerificationToken;
use ShortCode\Code;
use Tests\TestCase;

/**
 * Class VerificationBrokerTest
 *
 * @package Unit
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class VerificationBrokerTest extends TestCase
{

    /**
     * Test api login.
     *
     * @return void
     */
    public function testVerificationLinksSendingForPasswordReset()
    {
        \DB::table('users')->insert([
            'name'  => 'Test User',
            'email' => $email = 'test@test.com',
        ]);

        $result = app(VerificationBrokerContract::class)->sendVerificationLink([
            'email' => $email,
        ], 'passwords', function ($user, $tokens) use ($email) {
            $this->assertEquals($email, $user->email);
            $this->assertCount(2, $tokens);
            $this->assertEquals(64, mb_strlen($tokens[ 'web' ]));
            $this->assertEquals(config('verification-broker.length.mobile'), mb_strlen($tokens[ 'mobile' ]));
        });

        $this->assertEquals(VerificationBrokerContract::PASSWORD_VERIFICATION_LINK_SENT, $result);

        $result = app(VerificationBrokerContract::class)->sendVerificationLink([
            'email' => $email,
        ], 'passwords', function ($user, $tokens) use ($email) {

        });

        $this->assertEquals(VerificationBrokerContract::RESET_THROTTLED, $result);
    }

    /**
     * Test api login.
     *
     * @return void
     */
    public function testVerificationLinksSendingForVerification()
    {
        \DB::table('users')->insert([
            'name'  => 'Test User',
            'email' => $email = 'test@test.com',
        ]);

        $result = app(VerificationBrokerContract::class)->sendVerificationLink([
            'email' => $email,
        ], 'acc_verification', function ($user) use ($email) {
            $this->assertEquals($email, $user->email);
        });

        $this->assertEquals(VerificationBrokerContract::VERIFICATION_LINK_SENT, $result);

        $result = app(VerificationBrokerContract::class)->sendVerificationLink([
            'email' => $email,
        ], 'passwords', function ($user, $tokens) use ($email) {

        });

        $this->assertEquals(VerificationBrokerContract::PASSWORD_VERIFICATION_LINK_SENT, $result);

        $result = app(VerificationBrokerContract::class)->sendVerificationLink([
            'email' => $email,
        ], 'passwords', function ($user, $tokens) use ($email) {

        });

        $this->assertEquals(VerificationBrokerContract::RESET_THROTTLED, $result);
    }

    /**
     * Test api login.
     *
     * @return void
     */
    public function testVerificationTokenValidation()
    {
        \DB::table('users')->insert([
            'name'  => 'Test User',
            'email' => $email = 'test@test.com',
        ]);

        $generatedTokens = [];

        $result = app(VerificationBrokerContract::class)->sendVerificationLink($creds = [
            'email' => $email,
        ], $tokenType = 'acc_verification', function ($user, $tokens) use (&$generatedTokens) {
            $generatedTokens = $tokens;
        });

        $this->assertEquals(VerificationBrokerContract::VERIFICATION_LINK_SENT, $result);

        $result = app(VerificationBrokerContract::class)->verify([
            'email' => $email,
            'token' => $generatedTokens[ 'web' ],
        ], $tokenType, function ($user) use ($email) {
            $this->assertEquals($email, $user->email);
        });

        $this->assertEquals(VerificationBrokerContract::VERIFICATION_PASSES, $result);

        $this->assertEmpty(VerificationToken::all());
    }

    /**
     * Test api login.
     *
     * @return void
     */
    public function testVerificationPasswordsTokenValidation()
    {
        \DB::table('users')->insert([
            'name'  => 'Test User',
            'email' => $email = 'test@test.com',
        ]);

        $generatedTokens = [];

        $result = app(VerificationBrokerContract::class)
            ->setShortCodeFormat(Code::FORMAT_NUMBER)
            ->setShortCodeLength(6)
            ->sendVerificationLink($creds = [
                'email' => $email,
            ], $tokenType = 'passwords', function ($user, $tokens) use (&$generatedTokens) {
                $generatedTokens = $tokens;
            });

        $this->assertEquals(VerificationBrokerContract::PASSWORD_VERIFICATION_LINK_SENT, $result);
        $this->assertEquals(6, mb_strlen($generatedTokens[ 'mobile' ]));

        $newPassword = '123123123';

        $result = app(VerificationBrokerContract::class)->verify([
            'email'    => $email,
            'token'    => $generatedTokens[ 'web' ],
            'password' => $newPassword,
        ], $tokenType, function ($user, $password) use ($email, $newPassword) {
            $this->assertEquals($email, $user->email);
            $this->assertEquals($password, $newPassword);
        });

        $this->assertEquals(VerificationBrokerContract::PASSWORD_RESET_VERIFICATION_PASSES, $result);

        $this->assertEmpty(VerificationToken::all());
    }

}
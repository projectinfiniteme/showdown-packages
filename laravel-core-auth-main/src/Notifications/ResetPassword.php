<?php

namespace AttractCores\LaravelCoreAuth\Notifications;

use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Auth\Notifications\ResetPassword as LaravelResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

/**
 * Class ResetPassword
 *
 * @package AttractCores\LaravelCoreAuth\Notifications
 * Date: 14.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class ResetPassword extends LaravelResetPassword implements ShouldQueue
{

    use Queueable;

    /**
     * Array of tokens for pwd reset.
     *
     * @var array
     */
    public array $tokens;

    /**
     * Request side for password reset url generation.
     *
     * @var string
     */
    public string $requestSide;

    /**
     * The callback that should be used to build a default mail driver for mail message.
     *
     * @var \Closure|null
     */
    public static $mailDriverCallback;

    /**
     * ResetPassword constructor.
     *
     * @param array  $tokens
     * @param string $requestSide
     */
    public function __construct(array $tokens, $requestSide = CoreUserContract::FRONTEND_REQUEST_SIDE)
    {
        $this->tokens = $tokens;
        $this->requestSide = $requestSide;
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if ( static::$toMailCallback ) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->tokens, $this->requestSide);
        }

        if ( static::$createUrlCallback ) {
            $url = call_user_func(static::$createUrlCallback, $notifiable, $this->tokens, $this->requestSide);
        } else {
            $url = url(route('password.reset', [
                'token' => $this->tokens[ 'web' ],
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        }

        return $this->buildMailMessage($url);
    }

    /**
     * Get the reset password notification mail message for the given URL.
     *
     * @param string $url
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        $mailDriver = ( static::$mailDriverCallback ? call_user_func(static::$mailDriverCallback) :
            ( new MailMessage ) );

        $mailDriver->subject(Lang::get('Password Reset'))
                   ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
                   ->action(Lang::get('Reset Password'), $url)
                   ->line(Lang::get('This password reset link will expire in :count minutes.',
                       [ 'count' => config('verification-broker.lifetime.expires') ]))
                   ->line(Lang::get('If you did not request a password reset, no further action is required.'));

        if (
            config('kit-auth.enable_mobile_short_codes') &&
            get_class($mailDriver) == 'AttractCores\LaravelCoreKit\Libraries\MailMessage'
        ) {
            $mailDriver->line(Lang::get('If you have used the mobile app, then please enter this code:'))
                       ->addHrBlock($this->tokens[ 'mobile' ], [ 'text-align: center', 'font-weight: bold' ]);
        } elseif ( config('kit-auth.enable_mobile_short_codes') ) {
            $mailDriver->line(Lang::get('If you have used the mobile app, then please enter this code: <b>:code</b>.',
                [ 'code' => $this->tokens[ 'mobile' ] ]));
        }

        return $mailDriver;
    }

}
<?php

namespace AttractCores\LaravelCoreAuth\Notifications;

use AttractCores\LaravelCoreAuth\Resolvers\CoreUserContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use \Illuminate\Auth\Notifications\VerifyEmail as LaravelVerifyEmail;

/**
 * Class VerifyEmail
 *
 * @package AttractCores\LaravelCoreAuth\Notifications
 * Date: 15.12.2020
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class VerifyEmail extends LaravelVerifyEmail implements ShouldQueue
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
     * VerifyEmail constructor.
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
        $verificationUrl = $this->verificationUrl($notifiable);

        if ( static::$toMailCallback ) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->tokens, $this->requestSide,
                $verificationUrl);
        }

        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param mixed $notifiable
     *
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        if ( static::$createUrlCallback ) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->tokens, $this->requestSide);
        }

        return '';
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param string $url
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        $mailDriver = ( static::$mailDriverCallback ? call_user_func(static::$mailDriverCallback) :
            ( new MailMessage ) );

        $mailDriver->subject(Lang::get('Email Address Verification Process'))
                   ->line(Lang::get('Please click the button below to verify your email address.'))
                   ->action(Lang::get('Verify Email Address'), $url)
                   ->line(Lang::get('If you did not create an account, no further action is required.'));

        if (
            config('kit-auth.enable_mobile_short_codes') &&
            get_class($mailDriver) == 'AttractCores\LaravelCoreKit\Libraries\MailMessage'
        ) {
            $mailDriver->line(Lang::get('If you have used the mobile app to create your account, then please enter this code:'))
                       ->addHrBlock($this->tokens[ 'mobile' ], [ 'text-align: center', 'font-weight: bold' ]);
        } elseif ( config('kit-auth.enable_mobile_short_codes') ) {
            $mailDriver->line(Lang::get('If you have used the mobile app to create your account, then please enter this code: <b>:code</b>.',
                [ 'code' => $this->tokens[ 'mobile' ] ]));
        }

        return $mailDriver;
    }


}
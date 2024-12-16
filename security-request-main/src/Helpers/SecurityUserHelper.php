<?php
if ( ! function_exists('detectUser')) {
    /**
     * Detect user.
     *
     * @return array
     */
    function detectUser()
    {
        $signedIn = Auth::check();
        $user = null;
        if ( ! $signedIn) {
            $signedIn = Auth::guard('api')->check();
        }
        if ($signedIn) {
            $user = Auth::user();
            if ( ! $user) {
                $user = Auth::guard('api')->user();
            }
        }

        return (object) [
            'signedIn' => $signedIn,
            'user'     => $user,
        ];
    }
}
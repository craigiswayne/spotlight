<?php

namespace App\Handlers;

use SocialiteProviders\Manager\SocialiteWasCalled;

class OktaExtendSocialiteHandler
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('okta', 'App\Providers\OktaProvider');
    }
}

<?php

namespace SocialiteProviders\Gidru;

use SocialiteProviders\Manager\SocialiteWasCalled;

class GidruExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('gidru', Provider::class);
    }
}
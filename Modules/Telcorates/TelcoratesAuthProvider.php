<?php

namespace Modules\Telcorates\;

use App\Providers\AuthServiceProvider;

class TelcoratesAuthProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \Modules\Telcorates\Models\Telcorates::class => \Modules\Telcorates\Policies\TelcoratesPolicy::class,
    ];
}

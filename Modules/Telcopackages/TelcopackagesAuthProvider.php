<?php

namespace Modules\Telcopackages\;

use App\Providers\AuthServiceProvider;

class TelcopackagesAuthProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \Modules\Telcopackages\Models\Telcopackages::class => \Modules\Telcopackages\Policies\TelcopackagesPolicy::class,
    ];
}

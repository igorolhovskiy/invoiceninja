<?php

namespace Modules\ImportColt;

use App\Providers\AuthServiceProvider;

class ImportcoltAuthProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \Modules\Importcolt\Models\Importcolt::class => \Modules\Importcolt\Policies\ImportcoltPolicy::class,
    ];
}

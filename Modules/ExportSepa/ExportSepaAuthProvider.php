<?php

namespace Modules\ExportSepa;

use App\Providers\AuthServiceProvider;

class ExportsepaAuthProvider extends AuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        \Modules\Exportsepa\Models\Exportsepa::class => \Modules\Exportsepa\Policies\ExportsepaPolicy::class,
    ];
}

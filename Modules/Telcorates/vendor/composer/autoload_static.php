<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit19c33f503cd646f1fcedae439bd243a2
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Modules\\Telcorates\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Modules\\Telcorates\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Modules\\Telcorates\\Database\\Seeders\\TelcoratesDatabaseSeeder' => __DIR__ . '/../..' . '/Database/Seeders/TelcoratesDatabaseSeeder.php',
        'Modules\\Telcorates\\Datatables\\TelcoratesDatatable' => __DIR__ . '/../..' . '/Datatables/TelcoratesDatatable.php',
        'Modules\\Telcorates\\Http\\ApiControllers\\TelcoratesApiController' => __DIR__ . '/../..' . '/Http/ApiControllers/TelcoratesApiController.php',
        'Modules\\Telcorates\\Http\\Controllers\\TelcoratesController' => __DIR__ . '/../..' . '/Http/Controllers/TelcoratesController.php',
        'Modules\\Telcorates\\Http\\Requests\\CreateTelcoratesRequest' => __DIR__ . '/../..' . '/Http/Requests/CreateTelcoratesRequest.php',
        'Modules\\Telcorates\\Http\\Requests\\TelcoratesRequest' => __DIR__ . '/../..' . '/Http/Requests/TelcoratesRequest.php',
        'Modules\\Telcorates\\Http\\Requests\\UpdateTelcoratesRequest' => __DIR__ . '/../..' . '/Http/Requests/UpdateTelcoratesRequest.php',
        'Modules\\Telcorates\\Models\\TelcorateCode' => __DIR__ . '/../..' . '/Models/TelcorateCode.php',
        'Modules\\Telcorates\\Models\\Telcorates' => __DIR__ . '/../..' . '/Models/Telcorates.php',
        'Modules\\Telcorates\\Policies\\TelcoratesPolicy' => __DIR__ . '/../..' . '/Policies/TelcoratesPolicy.php',
        'Modules\\Telcorates\\Presenters\\TelcoratesPresenter' => __DIR__ . '/../..' . '/Presenters/TelcoratesPresenter.php',
        'Modules\\Telcorates\\Providers\\TelcoratesServiceProvider' => __DIR__ . '/../..' . '/Providers/TelcoratesServiceProvider.php',
        'Modules\\Telcorates\\Repositories\\TelcoratesRepository' => __DIR__ . '/../..' . '/Repositories/TelcoratesRepository.php',
        'Modules\\Telcorates\\Transformers\\TelcoratesTransformer' => __DIR__ . '/../..' . '/Transformers/TelcoratesTransformer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit19c33f503cd646f1fcedae439bd243a2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit19c33f503cd646f1fcedae439bd243a2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit19c33f503cd646f1fcedae439bd243a2::$classMap;

        }, null, ClassLoader::class);
    }
}
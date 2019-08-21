<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2808841254bee12f66606d623258475e
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Modules\\ImportColt\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Modules\\ImportColt\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
    );

    public static $classMap = array (
        'Modules\\ImportColt\\Database\\Seeders\\ImportColtDatabaseSeeder' => __DIR__ . '/../..' . '/Database/Seeders/ImportColtDatabaseSeeder.php',
        'Modules\\ImportColt\\Datatables\\ImportColtDatatable' => __DIR__ . '/../..' . '/Datatables/ImportColtDatatable.php',
        'Modules\\ImportColt\\Http\\ApiControllers\\ImportcoltApiController' => __DIR__ . '/../..' . '/Http/ApiControllers/ImportColtApiController.php',
        'Modules\\ImportColt\\Http\\Controllers\\ImportColtController' => __DIR__ . '/../..' . '/Http/Controllers/ImportColtController.php',
        'Modules\\ImportColt\\Http\\Requests\\CreateImportColtRequest' => __DIR__ . '/../..' . '/Http/Requests/CreateImportColtRequest.php',
        'Modules\\ImportColt\\Http\\Requests\\ImportColtRequest' => __DIR__ . '/../..' . '/Http/Requests/ImportColtRequest.php',
        'Modules\\ImportColt\\Http\\Requests\\UpdateImportColtRequest' => __DIR__ . '/../..' . '/Http/Requests/UpdateImportColtRequest.php',
        'Modules\\ImportColt\\Models\\ImportColt' => __DIR__ . '/../..' . '/Models/ImportColt.php',
        'Modules\\ImportColt\\Policies\\ImportcoltPolicy' => __DIR__ . '/../..' . '/Policies/ImportColtPolicy.php',
        'Modules\\ImportColt\\Presenters\\ImportcoltPresenter' => __DIR__ . '/../..' . '/Presenters/ImportColtPresenter.php',
        'Modules\\ImportColt\\Providers\\ImportColtServiceProvider' => __DIR__ . '/../..' . '/Providers/ImportColtServiceProvider.php',
        'Modules\\ImportColt\\Repositories\\ImportcoltRepository' => __DIR__ . '/../..' . '/Repositories/ImportColtRepository.php',
        'Modules\\ImportColt\\Transformers\\ImportcoltTransformer' => __DIR__ . '/../..' . '/Transformers/ImportColtTransformer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2808841254bee12f66606d623258475e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2808841254bee12f66606d623258475e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2808841254bee12f66606d623258475e::$classMap;

        }, null, ClassLoader::class);
    }
}
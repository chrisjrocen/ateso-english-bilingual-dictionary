<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite00346eea5b845ca3a70589c8a1b3e91
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'ATESO_ENG\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ATESO_ENG\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite00346eea5b845ca3a70589c8a1b3e91::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite00346eea5b845ca3a70589c8a1b3e91::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite00346eea5b845ca3a70589c8a1b3e91::$classMap;

        }, null, ClassLoader::class);
    }
}

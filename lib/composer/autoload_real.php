<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInite00346eea5b845ca3a70589c8a1b3e91
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInite00346eea5b845ca3a70589c8a1b3e91', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInite00346eea5b845ca3a70589c8a1b3e91', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInite00346eea5b845ca3a70589c8a1b3e91::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}

<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb7eb42cca483979d2d9e1001b2d215cc
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'ClimbPress\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ClimbPress\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb7eb42cca483979d2d9e1001b2d215cc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb7eb42cca483979d2d9e1001b2d215cc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb7eb42cca483979d2d9e1001b2d215cc::$classMap;

        }, null, ClassLoader::class);
    }
}

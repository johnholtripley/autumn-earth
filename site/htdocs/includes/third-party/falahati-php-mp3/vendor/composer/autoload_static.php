<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit77d225d798bbdab11b8ea23eaf095766
{
    public static $prefixLengthsPsr4 = array (
        'f' => 
        array (
            'falahati\\PHPMP3\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'falahati\\PHPMP3\\' => 
        array (
            0 => __DIR__ . '/..' . '/falahati/php-mp3/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit77d225d798bbdab11b8ea23eaf095766::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit77d225d798bbdab11b8ea23eaf095766::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}

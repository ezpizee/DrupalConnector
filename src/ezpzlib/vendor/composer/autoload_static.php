<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit71437e9a752949ff232404692d8051c5
{
    public static $files = array (
        '256c1545158fc915c75e51a931bdba60' => __DIR__ . '/..' . '/lcobucci/jwt/compat/class-aliases.php',
        '0d273777b2b0d96e49fb3d800c6b0e81' => __DIR__ . '/..' . '/lcobucci/jwt/compat/json-exception-polyfill.php',
        'd6b246ac924292702635bb2349f4a64b' => __DIR__ . '/..' . '/lcobucci/jwt/compat/lcobucci-clock-polyfill.php',
    );

    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'Lcobucci\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Lcobucci\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/lcobucci/jwt/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'U' => 
        array (
            'Unirest\\' => 
            array (
                0 => __DIR__ . '/..' . '/mashape/unirest-php/src',
            ),
        ),
        'H' => 
        array (
            'HandlebarsHelpers\\' => 
            array (
                0 => __DIR__ . '/..' . '/webconsol/php-hbs-helpers/src',
            ),
            'Handlebars' => 
            array (
                0 => __DIR__ . '/..' . '/webconsol/handlebars.php/src',
            ),
        ),
        'E' => 
        array (
            'Ezpizee\\Utils' => 
            array (
                0 => __DIR__ . '/..' . '/ezpizee/utils/src',
            ),
            'Ezpizee\\MicroservicesClient' => 
            array (
                0 => __DIR__ . '/..' . '/ezpizee/microservices-client/src',
            ),
            'Ezpizee\\ContextProcessor' => 
            array (
                0 => __DIR__ . '/..' . '/ezpizee/contextprocessor/src',
            ),
            'Ezpizee\\ConnectorUtils' => 
            array (
                0 => __DIR__ . '/..' . '/ezpizee/connector-utils/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit71437e9a752949ff232404692d8051c5::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit71437e9a752949ff232404692d8051c5::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit71437e9a752949ff232404692d8051c5::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit71437e9a752949ff232404692d8051c5::$classMap;

        }, null, ClassLoader::class);
    }
}
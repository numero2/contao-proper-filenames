<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2021 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright 2021 numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenames\DCAHelper;

use Contao\Backend;
use Contao\DataContainer;
use Contao\System;
use CoreBundle\DataContainer\PaletteManipulator;


class Settings extends Backend {


    /**
     * load and set the default value for the exclude file extension setting
     *
     * @param string $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public static function loadDefaultFileExtenstions( $varValue, DataContainer $dc ) {

        $configValue = Config::get('excludeFileExtensions');

        if( $configValue === null ) {
            Config::persist('excludeFileExtensions', 'js,css,scss,less,html,htm,ttf,ttc,otf,eot,woff,woff2');
            Config::set('excludeFileExtensions', 'js,css,scss,less,html,htm,ttf,ttc,otf,eot,woff,woff2');
            $varValue = 'js,css,scss,less,html,htm,ttf,ttc,otf,eot,woff,woff2';
        }

        return $varValue;
    }
}

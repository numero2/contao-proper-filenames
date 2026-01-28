<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2026, numero2 - Agentur für digitales Marketing GbR
 */


namespace numero2\ProperFilenamesBundle\EventListener\DataContainer;

use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Slug\ValidCharacters;
use Contao\DataContainer;
use Contao\System;


class SettingsListener {


    /**
     * Adjust the palettes
     *
     * @param Contao\DataContainer $dc
     *
     * @Callback(table="tl_settings", target="config.onload")
     */
    public function adjustPalettes( DataContainer $dc ): void {

        PaletteManipulator::create()
            ->addField('checkFilenames', 'imageHeight', PaletteManipulator::POSITION_AFTER)
            ->applyToPalette('default', 'tl_settings');

        $GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'checkFilenames';
        $GLOBALS['TL_DCA']['tl_settings']['subpalettes']['checkFilenames'] = 'filenameValidCharacters,filenameValidCharactersLocale,excludeFileExtensions,doNotTrimFilenames';
    }


    /**
     * Generate options for valid filename characters
     *
     * @return array
     *
     * @Callback(table="tl_settings", target="fields.filenameValidCharacters.options")
     */
    public function getValidCharacterOptions(): array {

        if( class_exists(ValidCharacters::class) ) {
            return System::getContainer()->get('contao.slug.valid_characters')->getOptions();
        }

        return [
            '\pN\p{Ll}' => 'unicodeLowercase',
            '\pN\pL' => 'unicode',
            '0-9a-z' => 'asciiLowercase',
            '0-9a-zA-Z' => 'ascii'
        ];
    }


    /**
     * load and set the default value for the "exclude file extensions" setting
     *
     * @param string $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string
     *
     * @Callback(table="tl_settings", target="fields.excludeFileExtensions.load")
     */
    public static function loadDefaultFileExtenstions( $varValue, DataContainer $dc ): string {

        $configValue = Config::get('excludeFileExtensions');

        if( $configValue === null ) {
            Config::persist('excludeFileExtensions', 'js,css,scss,less,map,html,htm,ttf,ttc,otf,eot,woff,woff2');
            Config::set('excludeFileExtensions', 'js,css,scss,less,map,html,htm,ttf,ttc,otf,eot,woff,woff2');
            $varValue = 'js,css,scss,less,map,html,htm,ttf,ttc,otf,eot,woff,woff2';
        }

        return $varValue;
    }


    /**
     * Generate options for languages
     *
     * @return array
     *
     * @Callback(table="tl_settings", target="fields.filenameValidCharactersLocale.options")
     */
    public static function getLanguages(): array {

        if( System::getContainer()->has('contao.intl.locales') ) {
            return System::getContainer()->get('contao.intl.locales')->getLanguages();
        } else {
            return System::getLanguages();
        }
    }
}

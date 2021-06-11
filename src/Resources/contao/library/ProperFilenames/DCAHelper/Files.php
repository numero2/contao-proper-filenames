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
use Contao\CoreBundle\Slug\ValidCharacters;


class Files extends Backend {


    /**
     * Provides the options for tl_settings.filenameValidCharacters
     *
     * @return array
     */
    public function getValidCharacterOptions() {

        if( class_exists(ValidCharacters::class) ) {
            return System::getContainer()->get('contao.slug.valid_characters')->getOptions();
        }

        return array(
            '\pN\p{Ll}' => 'unicodeLowercase',
            '\pN\pL' => 'unicode',
            '0-9a-z' => 'asciiLowercase',
            '0-9a-zA-Z' => 'ascii'
        );
    }


    /**
     * Adjust the palettes
     *
     * @param Contao\DataContainer $dc
     */
    public function adjustPalettes( DataContainer $dc ) {

        if( !$dc->id ) {
            return;
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        // Remove the donotSanitize field when editing folders
        if( !is_dir($projectDir . '/' . $dc->id) ) {
            PaletteManipulator::create()
                ->removeField('donotSanitize')
                ->applyToPalette('default', $dc->table)
            ;
        }
    }
}

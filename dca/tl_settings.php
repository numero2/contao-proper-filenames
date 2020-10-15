<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright 2017 numero2 - Agentur für Internetdienstleistungen
 */

use Contao\System;
use numero2\ProperFilenames\CheckFilenames;

/**
 * Add palettes to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace(
    ',imageHeight',
    ',imageHeight,checkFilenames,doNotTrimFilenames,filenameValidCharacters,filenameValidCharactersLocale',
    $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
);


/**
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['checkFilenames'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['checkFilenames'],
    'inputType' => 'checkbox',
    'eval'      => array( 'tl_class' => 'w50 cbx' ),
    'default'   => true
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['doNotTrimFilenames'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['doNotTrimFilenames'],
    'inputType' => 'checkbox',
    'eval'      => array( 'tl_class' => 'w50 cbx' ),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['filenameValidCharacters'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['filenameValidCharacters'],
    'inputType' => 'select',
    'options_callback' => array( CheckFilenames::class, 'getValidCharacterOptions' ),
    'reference' => &$GLOBALS['TL_LANG']['MSC']['validCharacters'],
    'eval'      => array( 'tl_class' => 'w50', 'includeBlankOption' => true, 'decodeEntities' => true ),
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['filenameValidCharactersLocale'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_settings']['filenameValidCharactersLocale'],
    'inputType' => 'select',
    'options_callback' => static function () {
        return System::getLanguages();
    },
    'eval'      => array( 'tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true ),
);

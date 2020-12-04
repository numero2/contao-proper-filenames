<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2020 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright 2020 numero2 - Agentur für digitales Marketing GbR
 */

use Contao\System;
use numero2\ProperFilenames\CheckFilenames;


/**
 * Add palettes to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['__selector__'][] = 'checkFilenames';
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace(
    ',imageHeight'
,   ',imageHeight,checkFilenames'
,   $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']
);

$GLOBALS['TL_DCA']['tl_settings']['subpalettes']['checkFilenames'] = 'filenameValidCharacters,filenameValidCharactersLocale,doNotTrimFilenames';


/**
 * Add fields to tl_settings
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['checkFilenames'] = [
    'label'                 => &$GLOBALS['TL_LANG']['tl_settings']['checkFilenames']
,   'inputType'             => 'checkbox'
,   'eval'                  => ['submitOnChange'=>true, 'tl_class' => 'w50 cbx']
,   'default'               => true
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['filenameValidCharacters'] = [
    'label'                 => &$GLOBALS['TL_LANG']['tl_settings']['filenameValidCharacters']
,   'inputType'             => 'select'
,   'options_callback'      => [CheckFilenames::class, 'getValidCharacterOptions']
,   'reference'             => &$GLOBALS['TL_LANG']['MSC']['validCharacters']
,   'eval'                  => ['tl_class' => 'w50', 'includeBlankOption' => true, 'decodeEntities' => true]
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['filenameValidCharactersLocale'] = [
    'label'                 => &$GLOBALS['TL_LANG']['tl_settings']['filenameValidCharactersLocale']
,   'inputType'             => 'select'
,   'options_callback'      => static function () {
        return System::getLanguages();
    }
,   'eval'                  => ['tl_class' => 'w50', 'includeBlankOption' => true, 'chosen' => true]
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['doNotTrimFilenames'] = [
    'label'                 => &$GLOBALS['TL_LANG']['tl_settings']['doNotTrimFilenames']
,   'inputType'             => 'checkbox'
,   'eval'                  => ['tl_class' => 'w50 cbx']
];

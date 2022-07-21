<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2022 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright 2022 numero2 - Agentur für digitales Marketing GbR
 */


$GLOBALS['TL_DCA']['tl_settings']['fields']['checkFilenames'] = [
    'inputType'     => 'checkbox'
,   'eval'          => ['submitOnChange'=>true, 'tl_class'=>'w50 cbx']
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['filenameValidCharacters'] = [
    'inputType'     => 'select'
,   'reference'     => &$GLOBALS['TL_LANG']['MSC']['validCharacters']
,   'eval'          => ['mandatory'=>true, 'includeBlankOption'=>true, 'decodeEntities'=>true, 'tl_class'=>'w50']
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['filenameValidCharactersLocale'] = [
    'inputType'     => 'select'
,   'eval'          => ['includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50']
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['excludeFileExtensions'] = [
    'inputType'     => 'text'
,   'eval'          => ['useRawRequestData'=>true, 'tl_class'=>'clr long']
];

$GLOBALS['TL_DCA']['tl_settings']['fields']['doNotTrimFilenames'] = [
    'inputType'     => 'checkbox'
,   'eval'          => ['tl_class'=>'w50 cbx']
];

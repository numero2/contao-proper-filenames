<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
 */


use Contao\CoreBundle\DataContainer\PaletteManipulator;


PaletteManipulator::create()
    ->addField('doNotSanitize','',PaletteManipulator::POSITION_PREPEND)
    ->applyToSubpalette('storeFile', 'tl_form_field');


$GLOBALS['TL_DCA']['tl_form_field']['fields']['doNotSanitize'] = [
    'inputType' => 'checkbox'
,   'eval'      => ['tl_class'=>'w50 cbx']
,   'sql'       => "char(1) NOT NULL default ''"
];

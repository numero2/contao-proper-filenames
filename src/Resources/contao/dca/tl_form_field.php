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


use CoreBundle\DataContainer\PaletteManipulator;


/**
 * Add palettes to tl_form_field
 */
 PaletteManipulator::create()
     ->addField('doNotSanitize','','append')
     ->applyToSubpalette('storeFile', 'tl_form_field');


/**
 * Add fields to tl_form_field
 */
$GLOBALS['TL_DCA']['tl_form_field']['fields']['doNotSanitize'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['doNotSanitize']
,   'inputType' => 'checkbox'
,   'eval'      => ['tl_class'=>'w50 cbx']
,   'sql'       => "char(1) NOT NULL default ''"
];

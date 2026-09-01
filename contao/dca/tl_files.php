<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL-3.0-or-later
 * @copyright Copyright (c) 2026, numero2 - Agentur für digitales Marketing GbR
 */


$GLOBALS['TL_DCA']['tl_files']['fields']['doNotSanitize'] = [
    'exclude'   => true
,   'inputType' => 'checkbox'
,   'eval'      => ['tl_class'=>'w50 cbx']
,   'sql'       => "char(1) NOT NULL default ''"
];

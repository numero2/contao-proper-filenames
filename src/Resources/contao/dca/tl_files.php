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


$GLOBALS['TL_DCA']['tl_files']['fields']['doNotSanitize'] = [
    'exclude'   => true
,   'inputType' => 'checkbox'
,   'eval'      => ['tl_class'=>'w50 cbx']
,   'sql'       => "char(1) NOT NULL default ''"
];

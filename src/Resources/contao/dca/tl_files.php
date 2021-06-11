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


/**
 * Add config to tl_files
 */
$GLOBALS['TL_DCA']['tl_files']['config']['onload_callback'][] = ['\numero2\ProperFilenames\DCAHelper\Files', 'adjustPalettes'];


/**
 * Add palettes to tl_files
 */
$GLOBALS['TL_DCA']['tl_files']['palettes']['default'] = str_replace(
    ',syncExclude'
,   ',syncExclude,doNotSanitize'
,   $GLOBALS['TL_DCA']['tl_files']['palettes']['default']
);


/**
 * Add fields to tl_files
 */
$GLOBALS['TL_DCA']['tl_files']['fields']['name']['save_callback'][] = ['\numero2\ProperFilenames\CheckFilenames', 'sanitizeFileOrFolderName'];

$GLOBALS['TL_DCA']['tl_files']['fields']['doNotSanitize'] = [
    'label'                 => &$GLOBALS['TL_LANG']['tl_files']['doNotSanitize']
,   'inputType'             => 'checkbox'
,   'eval'                  => ['tl_class'=>'w50 cbx']
,   'sql'                   => "char(1) NOT NULL default ''"
];

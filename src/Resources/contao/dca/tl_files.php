<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2020 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright 2020 numero2 - Agentur für digitales Marketing GbR
 */


/**
 * Add fields to tl_files
 */
$GLOBALS['TL_DCA']['tl_files']['fields']['name']['save_callback'][] = ['\numero2\ProperFilenames\CheckFilenames', 'sanitizeFileOrFolderName'];

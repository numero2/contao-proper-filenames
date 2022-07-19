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
 * Hooks
 */
$GLOBALS['TL_HOOKS']['postUpload'][] = ['\numero2\ProperFilenames\CheckFilenames', 'renameFiles'];
$GLOBALS['TL_HOOKS']['loadFormField'][] = ['\numero2\ProperFilenames\CheckFilenames', 'renameFormUploads'];

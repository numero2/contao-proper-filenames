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


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['postUpload'][] = array( '\numero2\ProperFilenames\CheckFilenames', 'renameFiles' );
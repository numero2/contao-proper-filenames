<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright 2017 numero2 - Agentur für Internetdienstleistungen
 */


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['postUpload'][] = array( '\numero2\ProperFilenames\CheckFilenames', 'renameFiles' );
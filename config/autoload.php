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
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'numero2\ProperFilenames',
));



/**
 * Register the classes
 */
ClassLoader::addClasses( array
(
    // Classes
    'numero2\ProperFilenames\CheckFilenames' => 'system/modules/proper-filenames/classes/CheckFilenames.php',
) );

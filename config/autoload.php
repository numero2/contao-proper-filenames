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
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'numero2\ProperFilenames',
));



/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Classes
    'numero2\ProperFilenames\CheckFilenames' => 'system/modules/proper-filenames/classes/CheckFilenames.php',
) );

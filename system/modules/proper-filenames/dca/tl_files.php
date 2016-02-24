<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   ProperFilenames
 * @author    Benny Born <benny.born@numero2.de>
 * @license   LGPL
 * @copyright 2015 numero2 - Agentur für Internetdienstleistungen
 */


/**
 * Namespace
 */
namespace numero2\ProperFilenames;


/* Fields */
$GLOBALS['TL_DCA']['tl_files']['fields']['name']['save_callback'][] = array('\numero2\ProperFilenames\tl_proper_filenames','rename');


/* Class */
class tl_proper_filenames extends \Backend {


    public function rename($varValue) {

        if( !$GLOBALS['TL_CONFIG']['checkFilenames'] )
            return $varValue;

        $varValue = standardize( \String::restoreBasicEntities( $varValue ) );
        return $varValue;
    }
}
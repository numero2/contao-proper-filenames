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


namespace numero2\ProperFilenames\DCAHelper;

use Contao\Backend;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use CoreBundle\DataContainer\PaletteManipulator;


class Files extends Backend {


    /**
     * Adjust the palettes
     *
     * @param Contao\DataContainer $dc
     */
    public function adjustPalettes( DataContainer $dc ) {

        if( !$dc->id ) {
            return;
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        // Remove the doNotSanitize field when editing folders
        if( !is_dir($projectDir . '/' . $dc->id) && method_exists(PaletteManipulator::class, 'removeField') ) {
            PaletteManipulator::create()
                ->removeField('doNotSanitize')
                ->applyToPalette('default', $dc->table)
            ;
        }
    }


    /**
     * check if a parent folder has set do not sanitize
     *
     * @param string $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string
     */
    public static function checkParentFolder( $varValue, $dc ) {

        $aParentFolders = [];

        if( !$varValue && $dc->id ) {

            $aParts = explode('/', $dc->id);

            if( !empty($aParts) ) {
                $path = '';
                foreach( $aParts as $folder ) {
                    $path .= $folder;
                    $aParentFolders[] = $path;
                    $path .= '/';
                }
            }
        }

        if( !empty($aParentFolders) ) {

            $doNotSanitize = Database::getInstance()->prepare("
                SELECT count(1) AS count
                FROM tl_files
                WHERE type=? AND doNotSanitize=? AND path IN ('".implode("','", $aParentFolders)."')
            ")->execute('folder', '1');

            if( $doNotSanitize->count ) {

                try {
                    if( $dc->table && $dc->field ) {
                        $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['disabled'] = true;
                    }
                } catch( \Exception $e ) {
                }

                return '1';
            }
        }

        return $varValue;
    }
}

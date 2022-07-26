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


namespace numero2\ProperFilenamesBundle\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\System;
use Doctrine\DBAL\Connection;
use numero2\ProperFilenamesBundle\Util\FilenamesUtil;


class FilesListener {


    /**
     * Adjust the palettes
     *
     * @param Contao\DataContainer $dc
     *
     * @Callback(table="tl_files", target="config.onload")
     */
    public function adjustPalettes( DataContainer $dc ): void {

        PaletteManipulator::create()
            ->addField('doNotSanitize','syncExclude',PaletteManipulator::POSITION_AFTER)
            ->applyToPalette('default', 'tl_files');

        if( !$dc->id ) {
            return;
        }

        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        // Remove the doNotSanitize field when editing folders
        if( !is_dir($projectDir . '/' . $dc->id) && method_exists(PaletteManipulator::class, 'removeField') ) {
            PaletteManipulator::create()
                ->removeField('doNotSanitize')
                ->applyToPalette('default', $dc->table);
        }
    }


    /**
     * Check if a parent folder has set do not sanitize
     *
     * @param string $varValue
     * @param Contao\DataContainer $dc
     *
     * @return string|null
     *
     * @Callback(table="tl_files", target="fields.doNotSanitize.load")
     */
    public static function checkParentFolder( $varValue, $dc ): ?string {

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
            /** @var \Doctrine\DBAL\Connection $db */
            $db = System::getContainer()->get('database_connection');

            $doNotSanitize = (int) $db->fetchOne("
                SELECT count(1) AS count
                FROM tl_files
                WHERE type='folder' AND doNotSanitize='1' AND path IN (?)
            ", [$aParentFolders], [Connection::PARAM_STR_ARRAY]);

            if( $doNotSanitize > 0 ) {

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


    /**
     * Sanitizes the given file- or foldername
     *
     * @param string $strName
     * @param Contao\DataContainer|array $dc
     *
     * @return string
     *
     * @Callback(table="tl_files", target="fields.name.save")
     */
    public function sanitizeFileOrFolderName( $strName, $dc=null ): string {

        return FilenamesUtil::sanitizeFileOrFolderName($strName, $dc);
    }
}

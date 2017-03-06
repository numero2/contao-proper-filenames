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
 * Namespace
 */
namespace numero2\ProperFilenames;


class CheckFilenames extends \Frontend {


    /**
     * Renames the given files
     *
     * @param array $arrFiles
     *
     * @return none
     */
    public function renameFiles( $arrFiles ) {

        if( !\Config::get('checkFilenames') )
            return null;

        $this->Import( 'Files' );
        $this->Import( 'FilesModel' );

        if( !empty($arrFiles) ) {

            foreach( $arrFiles as $file ) {

                // rename physical file
                $info = pathinfo( $file );
                $newFile = self::sanitizeFileOrFolderName($info['filename']);
                $newFile = $info['dirname'] . '/' . $newFile . '.' . strtolower( $info['extension'] );

                // create a temp file because the \Files class can't handle proper renaming on windows
                $this->Files->rename( $file, $newFile.'.tmp' );
                $this->Files->rename( $newFile.'.tmp', $newFile );

                // rename file in database
                $objFile = \FilesModel::findByPath($file);
                $objFile->path = $newFile;
                $objFile->hash = md5_file(TL_ROOT . '/' . $newFile);
                $objFile->save();
            }
        }
    }


    /**
     * Sanitizes the given file- or foldername
     *
     * @param  string $strName
     *
     * @return string
     */
    public static function sanitizeFileOrFolderName( $strName ) {

        if( !\Config::get('checkFilenames') )
            return $strName;

        $newName = $strName;

        // cut name to length
        if( !\Config::get('doNotTrimFilenames') ) {
            $newName = substr( $newName, 0, 32 );
        }

        // remove forbidden characters
        $newName = standardize( \String::restoreBasicEntities($newName) );

        // remove 'id-' from the beginning
        if( substr($newName,0,3) === "id-" ) {
            $newName = substr($newName,3);
        }

        // replace double underscores
        $newName = self::replaceUnderscores($newName);

        return $newName;
    }


    /**
     * Replaces doubled underscores in the given filename
     *
     * @param string $strFile
     *
     * @return string
     */
    protected static function replaceUnderscores( $strFile ) {

        $newFilename = str_replace( "__", "_", $strFile );

        if( $newFilename != $strFile ) {
            $newFilename = $this->replaceUnderscores( $newFilename );
        }

        return $newFilename;
    }
}
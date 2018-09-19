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

                $info = pathinfo( $file );

                $oldFileName = $info['filename'] . '.' . strtolower( $info['extension'] );
                $newFileName = self::sanitizeFileOrFolderName($info['filename']) . '.' . strtolower( $info['extension'] );

                // rename physical file
                if( $oldFileName !== $newFileName ) {

                    $newFile = $info['dirname'] . '/' . $newFileName;

                    // create a temp file because the \Files class can't handle proper renaming on windows
                    $this->Files->rename( $file, $newFile.'.tmp' );
                    $this->Files->rename( $newFile.'.tmp', $newFile );

                    // rename file in database
                    $objFile = \FilesModel::findByPath($file);
                    $objFile->path = $newFile;
                    $objFile->hash = md5_file(TL_ROOT . '/' . $newFile);
                    $objFile->name = $newFileName;

                    if( $objFile->save() ) {

                        \Message::addInfo(sprintf(
                            $GLOBALS['TL_LANG']['MSC']['proper_filenames_renamed']
                        ,   $oldFileName
                        ,   $newFileName
                        ));
                    }
                }
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

        // convert to lowercase
        $newName = strtolower($newName);

        // remove forbidden characters
        $newName = preg_replace("/[\s]+/", '-', $newName);
        $newName = preg_replace("/[^a-z0-9-_]+/", '', $newName);
        $newName = standardize( \StringUtil::restoreBasicEntities($newName) );

        // remove 'id-' from the beginning
        if( substr($newName,0,3) === "id-" ) {
            $newName = substr($newName,3);
        }

        // replace double underscores
        $newName = self::replaceUnderscores($newName);

        // cut name to length
        if( !\Config::get('doNotTrimFilenames') ) {
            $newName = substr( $newName, 0, 32 );
        }

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
            $newFilename = self::replaceUnderscores( $newFilename );
        }

        return $newFilename;
    }
}

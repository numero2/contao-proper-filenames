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


class CheckFilenames extends \Frontend {


    protected $search = '';


    /**
     * Renames the given files
     *
     * @param array $arrFiles
     *
     * @return none
     */
    public function renameFiles( $arrFiles ) {

        if( !$GLOBALS['TL_CONFIG']['checkFilenames'] )
            return null;

        $this->Import( 'Files' );
        $this->Import( 'FilesModel' );

        if( !empty($arrFiles) ) {

            foreach( $arrFiles as $file ) {

                // rename physical file
                $newFile = $this->replaceForbiddenCharacters( $file );

                // because the \Files renaming function is doing
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
     * Replaces all "forbidden" characters in the given filename
     *
     * @param string $strFile
     *
     * @return string
     */
    protected function replaceForbiddenCharacters( $strFile ) {

        $info = pathinfo( $strFile );

        $newFilename = substr( $info['filename'], 0, 32 );
        $newFilename = standardize( \String::restoreBasicEntities( $newFilename ) );
        $newFilename = $this->replaceUnderscores( $newFilename );

        return $info['dirname'] . '/' . $newFilename . '.' . strtolower( $info['extension'] );
    }


    /**
     * Replaces doubled underscores in the given filename
     *
     * @param string $strFile
     *
     * @return string
     */
    protected function replaceUnderscores( $strFile ) {

        $newFilename = str_replace( "__", "_", $strFile );

        if( $newFilename != $strFile ) {
            $newFilename = $this->replaceUnderscores( $newFilename );
        }

        return $newFilename;
    }
}
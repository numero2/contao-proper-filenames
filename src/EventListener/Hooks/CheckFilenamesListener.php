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


namespace numero2\ProperFilenamesBundle\EventListener\Hooks;

use Contao\Config;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Files as CoreFiles;
use Contao\FilesModel;
use Contao\Form;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Contao\Widget;
use numero2\ProperFilenamesBundle\Util\FilenamesUtil;
use Symfony\Component\HttpFoundation\RequestStack;


class CheckFilenamesListener {


    private $requestStack;
    private $scopeMatcher;


    public function __construct( RequestStack $requestStack, ScopeMatcher $scopeMatcher ) {

        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }


    /**
     * Renames uploaded files (backend)
     *
     * @param array $arrFiles
     *
     * @return void
     *
     * @Hook("postUpload")
     */
    public function renameFilesBackend( array $arrFiles ): void {

        $aRenamed = [];

        if( !Config::get('checkFilenames') ) {
            return;
        }

        if( !empty($arrFiles) ) {

            $oFiles = null;
            $oFiles = CoreFiles::getInstance();

            foreach( $arrFiles as $file ) {

                $info = pathinfo($file);

                $oldFileName = $info['filename'] . '.' . strtolower($info['extension']);
                $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                // rename physical file
                if( $oldFileName !== $newFileName ) {

                    $newFile = $info['dirname'] . '/' . $newFileName;

                    $aRenamed[$file] = $newFile;

                    // create a temp file because the \Files class can't handle proper renaming on windows
                    $oFiles->rename($file, $newFile.'.tmp');
                    $oFiles->rename($newFile.'.tmp', $newFile);

                    $rootDir = System::getContainer()->getParameter('kernel.project_dir');

                    // rename file in database
                    $objFile = FilesModel::findByPath($file);
                    $objFile->path = $newFile;
                    $objFile->hash = md5_file($rootDir . '/' . $newFile);
                    $objFile->name = $newFileName;

                    if( $objFile->save() && $this->scopeMatcher->isBackendRequest($this->requestStack->getCurrentRequest()) ) {

                        Message::addInfo(sprintf(
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
     * Renames an uploaded file (frontend)
     *
     * @param Contao\Widget $objWidget
     * @param string $formId
     * @param array $arrData
     * @param Contao\Form $objForm
     *
     * @return Contao\Widget
     *
     * @Hook("loadFormField")
     */
    public function renameFileUpload( Widget $objWidget, string $formId, array $formData, Form $form ): Widget {

        if( Input::post('FORM_SUBMIT') == $formId ) {

            if( $objWidget->storeFile && !empty($_FILES[$objWidget->name]) && $_FILES[$objWidget->name]['error'] === 0 && !$objWidget->doNotSanitize ) {

                $info = pathinfo($_FILES[$objWidget->name]['name']);
                $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                $_FILES[$objWidget->name]['name'] = $newFileName;
            }
        }

        return $objWidget;
    }
}

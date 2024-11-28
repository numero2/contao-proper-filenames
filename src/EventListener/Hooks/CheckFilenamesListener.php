<?php

/**
 * Proper Filenames Bundle for Contao Open Source CMS
 *
 * @author    Benny Born <benny.born@numero2.de>
 * @author    Michael Bösherz <michael.boesherz@numero2.de>
 * @license   LGPL
 * @copyright Copyright (c) 2024, numero2 - Agentur für digitales Marketing GbR
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
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;


class CheckFilenamesListener {


    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var Contao\CoreBundle\Routing\ScopeMatcher;
     */
    private $scopeMatcher;

    /**
     * @var Symfony\Contracts\Translation\TranslatorInterface
     */
    private TranslatorInterface $translator;


    public function __construct( RequestStack $requestStack, RouterInterface $router, ScopeMatcher $scopeMatcher, TranslatorInterface $translator ) {

        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->scopeMatcher = $scopeMatcher;
        $this->translator = $translator;
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
    public function renameFilesBackend( array &$arrFiles ): void {

        $aRenamed = [];

        if( !Config::get('checkFilenames') ) {
            return;
        }

        if( !empty($arrFiles) ) {

            $oFiles = null;
            $oFiles = CoreFiles::getInstance();

            foreach( $arrFiles as $i => $file ) {

                $info = pathinfo($file);

                $oldFileName = $info['filename'] . '.' . strtolower($info['extension']);
                $newFileName = FilenamesUtil::sanitizeFileOrFolderName($info['filename'], $info) . '.' . strtolower($info['extension']);

                // rename physical file
                if( $oldFileName !== $newFileName ) {

                    $newFile = $info['dirname'] . '/' . $newFileName;

                    $aRenamed[$file] = $newFile;

                    $oFiles->rename($file, $newFile);

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

                        // write back new filename for use in further hooks
                        $files[$i] = $newFilePath;
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


    /**
     * Checks if the renaming of files is activated but missing settings
     *
     * @Hook("getSystemMessages")
     */
    public function checkMissingSettings(): string {

        if( !Config::get('checkFilenames') || Config::get('checkFilenames') && Config::get('filenameValidCharacters') ) {
            return '';
        }

        $msg = sprintf(
            $this->translator->trans('ERR.proper_filenames_not_configured', [], 'contao_default')
        ,   $this->router->generate('contao_backend')
        );
        return '<p class="tl_error">'.$msg.'</p>';
    }
}

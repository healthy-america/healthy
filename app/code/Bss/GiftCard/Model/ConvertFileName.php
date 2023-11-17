<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Model;

use Exception;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Phrase;

class ConvertFileName
{
    /**
     * @var File
     */
    protected $fileDriver;

    /**
     * @var Dir
     */
    protected $directory;

    /**
     * @var FullModuleList
     */
    protected $moduleList;

    /**
     * @param File $fileDriver
     * @param Dir $directory
     * @param FullModuleList $fullModuleList
     */
    public function __construct(
        File           $fileDriver,
        Dir            $directory,
        FullModuleList $fullModuleList
    ) {
        $this->fileDriver = $fileDriver;
        $this->directory = $directory;
        $this->moduleList = $fullModuleList;
    }

    /**
     * Convert File Follow Version
     *
     * @return Phrase|string
     * @throws FileSystemException
     */
    public function convertFileFollowVersion()
    {
        $message = "";
        if ($this->moduleList->has('Klarna_Core')) {
            $message = $this->convertFileGiftCard("Giftcard", 'Klarna_Core');
        } elseif ($this->moduleList->has('Klarna_Base')) {
            $message = $this->convertFileGiftCard("GiftcardKlarnaBase", 'Klarna_Base');
        }
        return $message;
    }

    /**
     * Convert file txt and php
     *
     * @param string $fileName
     * @param string $moduleName
     * @return Phrase
     * @throws FileSystemException
     */
    public function convertFileGiftCard($fileName, $moduleName)
    {
        $pathPHP = "/Model/Checkout/Orderline/" . $fileName . ".php";
        $pathTXT = "/Model/Checkout/Orderline/" . $fileName . ".txt";
        $directoryPath = $this->directory->getDir('Bss_GiftCard');
        if ($this->moduleList->has($moduleName)) {
            $message = $this->convertFile($directoryPath, $pathTXT, $pathPHP);
        } else {
            $message = $this->convertFile($directoryPath, $pathPHP, $pathTXT);
        }
        return $message;
    }

    /**
     * Convert file
     *
     * @param string $directoryPath
     * @param string $pathOld
     * @param string $pathNew
     * @return Phrase
     * @throws FileSystemException
     */
    public function convertFile($directoryPath, $pathOld, $pathNew)
    {
        $message = "";
        if ($this->fileDriver->isExists($directoryPath . $pathOld)) {
            try {
                $this->fileDriver->rename($directoryPath . $pathOld, $directoryPath . $pathNew);
                $message = __("Convert file Giftcard successfully!");
            } catch (Exception $e) {
                $message = __("Permission denied.");
            }
        } elseif (!$this->fileDriver->isExists($directoryPath . $pathNew)) {
            $message = __("Can't find file Giftcard.");
        }
        return $message;
    }
}

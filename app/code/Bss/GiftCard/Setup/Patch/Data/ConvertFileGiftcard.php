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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Setup\Patch\Data;

use Bss\GiftCard\Model\ConvertFileName;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConvertFileGiftcard implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduledataSetup;

    /**
     * @var ConvertFileName
     */
    protected $convertFile;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ConvertFileName $convertFile
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ConvertFileName          $convertFile
    ) {
        $this->moduledataSetup = $moduleDataSetup;
        $this->convertFile = $convertFile;
    }

    /**
     * Apply
     *
     * @return ConvertFileGiftcard|void
     * @throws FileSystemException
     */
    public function apply()
    {
        $this->moduledataSetup->getConnection()->startSetup();
        $message=$this->convertFile->convertFileFollowVersion();
        $this->moduledataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}

<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class CityDropDownPatch implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private \Magento\Framework\Setup\ModuleDataSetupInterface $_moduleDataSetup;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private \Magento\Framework\Filesystem\DirectoryList $_directoryList;

    /**
     * @var \Aventi\CityDropDown\Model\Insert\InsertCities
     */
    private \Aventi\CityDropDown\Model\Insert\InsertCities $_insertCities;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     * @param \Aventi\CityDropDown\Model\Insert\InsertCities $insertCities
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Aventi\CityDropDown\Model\Insert\InsertCities $insertCities
    ) {
        $this->_directoryList = $directoryList;
        $this->_moduleDataSetup = $moduleDataSetup;
        $this->_insertCities = $insertCities;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->_moduleDataSetup->getConnection()->startSetup();
        $folder = $this->_directoryList->getPath('app') . '/' . 'code' . '/' . 'Aventi' . '/' . 'CityDropDown' . '/' . 'Setup' . '/' . 'data.csv';
        $this->_insertCities->insertCities($folder);
        $this->_moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        /**
         * This is dependency to another patch. Dependency should be applied first
         * One patch can have few dependencies
         * Patches do not have versions, so if in old approach with Install/Ugrade data scripts you used
         * versions, right now you need to point from patch with higher version to patch with lower version
         * But please, note, that some of your patches can be independent and can be installed in any sequence
         * So use dependencies only if this important for you
         */
        return [
        ];
    }

    public function revert()
    {
        $this->_moduleDataSetup->getConnection()->startSetup();
        //Here should go code that will revert all operations from `apply` method
        //Please note, that some operations, like removing data from column, that is in role of foreign key reference
        //is dangerous, because it can trigger ON DELETE statement
        $this->_moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        /**
         * This internal Magento method, that means that some patches with time can change their names,
         * but changing name should not affect installation process, that's why if we will change name of the patch
         * we will add alias here
         */
        return [];
    }
}

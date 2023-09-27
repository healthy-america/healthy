<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CreateStorePatch implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private \Magento\Store\Model\StoreFactory $storeFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    private \Magento\Store\Model\GroupFactory $groupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\GroupFactory $groupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $storeGroup = $this->groupFactory->create()->load('main_website_store', 'code');

        if (!$storeGroup->getId()) {
            // Create a new store group
            $storeGroup = $this->groupFactory->create();
            $storeGroup->setName('Main Website Store');
            $storeGroup->setCode('main_website_store');
            $storeGroup->setRootCategoryId(2); // Default Category
            $storeGroup->setWebsiteId(1); // base website
            $storeGroup->save();
        }

        $healthySportsStoreView = $this->storeFactory->create()->load('healthy_sports', 'code');
        $this->createStoreView($healthySportsStoreView, 'Healthy Sports View', 'healthy_sports', 1, $storeGroup->getGroupId());

        $nutrivitaStoreView = $this->storeFactory->create()->load('nutrivita', 'code');
        $this->createStoreView($nutrivitaStoreView, 'Nutrivita View', 'nutrivita', 1, $storeGroup->getGroupId());


        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @param $storeView
     * @param $name
     * @param $code
     * @param $websiteId
     * @param $groupId
     * @return void
     * @throws \Exception
     */
    public function createStoreView($storeView, $name, $code, $websiteId, $groupId): void
    {
        if (!$storeView->getId()) {
            // Create a new store
            $storeView = $this->storeFactory->create();
            $storeView->setName($name);
            $storeView->setCode($code);
            $storeView->setWebsiteId($websiteId);
            $storeView->setGroupId($groupId);
            $storeView->setIsActive(1);
            $storeView->save();
        }
    }

    /**
     * @return void
     */
    public function revert()
    {
        // TODO: Implement revert() method.
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [

        ];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}

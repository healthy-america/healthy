<?php

declare(strict_types=1);

namespace Aventi\SAP\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Store\Model\WebsiteFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CreateWebsitesPatch implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var WebsiteFactory
     */
    private WebsiteFactory $websiteFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WebsiteFactory $websiteFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->websiteFactory = $websiteFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        // Create your websites here
        $this->createWebsite('healthy_sports', 'Healthy Sports Website');
        $this->createWebsite('nutrivita', 'Nutrivita Website');

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    private function createWebsite($code, $name)
    {
        $this->websiteFactory->create()->setData([
            'code' => $code,
            'name' => $name,
        ])->save();
    }

    public function revert()
    {
        // TODO: Implement revert() method.
    }
}

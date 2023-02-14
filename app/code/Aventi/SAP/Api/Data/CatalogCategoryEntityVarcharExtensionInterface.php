<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Api\Data;

use Magento\Framework\Api\ExtensionAttributesInterface;

interface CatalogCategoryEntityVarcharExtensionInterface extends ExtensionAttributesInterface
{
    /**
     * @return int[]|null
     */
    public function getWebsiteIds();

    /**
     * @param int[] $websiteIds
     * @return $this
     */
    public function setWebsiteIds($websiteIds);
}

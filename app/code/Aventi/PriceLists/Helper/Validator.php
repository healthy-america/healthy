<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Helper;

use Aventi\PriceLists\Model\PriceListData;
use Magento\Customer\Model\SessionFactory as Session;

class Validator
{
    /**
     * @var PriceListData
     */
    private PriceListData $priceListData;

    /**
     * @var Session
     */
    private Session $customerSession;

    /**
     * @param PriceListData $priceListData
     * @param Session $customerSession
     */
    public function __construct(
        PriceListData $priceListData,
        Session $customerSession
    ) {
        $this->priceListData = $priceListData;
        $this->customerSession = $customerSession;
    }

    /**
     * @return bool
     */
    public function catalogIsVisible(): bool
    {
        $result = true;
        /** Only redirect if enabled in the config */
        if ($this->priceListData->getGeneralConfig('categories_logged_in')
            && $this->priceListData->getGeneralConfig('enable')
            && !$this->customerSession->create()->isLoggedIn()
        ) {
            $result = false;
        }
        return $result;
    }
}

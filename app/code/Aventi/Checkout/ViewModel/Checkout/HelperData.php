<?php
/**
 * Copyright © 2023 Aventi, SAS. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\ViewModel\Checkout;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Msrp\Helper\Data as MsrpHelperData;
use Magento\Tax\Helper\Data as TaxHelperData;

class HelperData implements ArgumentInterface
{
    /**
     * @param MsrpHelperData $msrpHelperData
     * @param TaxHelperData $taxHelperData
     */
    public function __construct(
        private MsrpHelperData $msrpHelperData,
        private TaxHelperData $taxHelperData
    ) {}

    /**
     * @return MsrpHelperData
     */
    public function getMsrpHelperData() {
        return $this->msrpHelperData;
    }

    /**
     * @return TaxHelperData
     */
    public function getTaxHelperData() {
        return $this->taxHelperData;
    }
}

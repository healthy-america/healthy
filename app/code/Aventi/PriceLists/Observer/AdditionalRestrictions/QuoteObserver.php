<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Observer\AdditionalRestrictions;

use Aventi\PriceLists\Helper\Validator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class QuoteObserver implements ObserverInterface
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @param Validator $validator
     */
    public function __construct(
        Validator $validator
    ) {
        $this->validator = $validator;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->validator->catalogIsVisible()) {
            throw new LocalizedException(
                __('You can not add products to cart.')
            );
        }
    }
}

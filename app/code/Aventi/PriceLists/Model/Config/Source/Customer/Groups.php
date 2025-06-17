<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model\Config\Source\Customer;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Customer groups source
 */
class Groups implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Customer groups option
     *
     * @var mixed[]
     */
    private $options;

    /**
     * Customer group collection factory
     *
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * Initialize source
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve customer groups as options
     *
     * @return mixed[]
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $groups = $this->collectionFactory->create();
            $this->options = $groups->toOptionArray();
        }
        return $this->options;
    }
}

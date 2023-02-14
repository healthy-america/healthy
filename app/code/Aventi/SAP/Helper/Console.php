<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Magento\Framework\Exception\InputException;
use Symfony\Component\Console\Output\OutputInterface;

class Console
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
    ) {
        $this->objectManager = $objectmanager;
    }

    /**
     * @param $option
     * @param $output OutputInterface
     * @param null $option2
     * @throws InputException
     * @throws \Bcn\Component\Json\Exception\ReadingError
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($option, OutputInterface $output, $option2 = null)
    {
        $class = null;
        $data = [];

        switch ($option) {
            case 'order':
                $class = \Aventi\SAP\Model\Integration\Order::class;
                $data = $option2 === 'new' ? ['pending', 'syncing', 'processing'] : ['error'];
                break;
            case 'product':
                $class = \Aventi\SAP\Model\Integration\Product::class;
                $data = ['fast' => false];
                break;
            case 'price':
                $class = \Aventi\SAP\Model\Integration\Price::class;
                $data = ['fast' => false];
                break;
            case 'stock':
                $class = \Aventi\SAP\Model\Integration\Stock::class;
                $data = ['fast' => false];
                break;
            case 'brand':
                $class = \Aventi\SAP\Model\Integration\Brand::class;
                $data = ['fast' => false];
                break;
            case 'category':
                $class = \Aventi\SAP\Model\Integration\Category::class;
                $data = ['fast' => false];
                break;
            default:
                throw new InputException(__("Option not defined."));
        }

        $object = $this->objectManager->create($class);

        $object->setOutput($output);
        //$object->process($data);
        $object->test($data);
    }
}

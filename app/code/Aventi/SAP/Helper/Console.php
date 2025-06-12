<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Bcn\Component\Json\Exception\ReadingError;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console
{
    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    public function __construct(
        ObjectManagerInterface $objectmanager,
    ) {
        $this->objectManager = $objectmanager;
    }

    /**
     * @param $option
     * @param $output OutputInterface
     * @param null $option2
     * @throws InputException
     * @throws ReadingError
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
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
                $data = ['fast' => !empty($option2)];
                break;
            case 'price':
                $class = \Aventi\SAP\Model\Integration\Price::class;
                $data = ['fast' => !empty($option2)];
                break;
            case 'stock':
                $class = \Aventi\SAP\Model\Integration\Stock::class;
                $data = ['fast' => !empty($option2)];
                break;
            case 'brand':
                $class = \Aventi\SAP\Model\Integration\Brand::class;
                $data = ['fast' => !empty($option2)];
                break;
            case 'customer':
                $class = \Aventi\SAP\Model\Integration\Customer::class;
                $data = ['fast' => !empty($option2)];
                break;
            case 'category':
                $class = \Aventi\SAP\Model\Integration\Category::class;
                $data = ['fast' => !empty($option2)];
                break;
            default:
                throw new InputException(__("Option not defined."));
        }

        $object = $this->objectManager->create($class);

        $object->setOutput($output);
        $object->process($data);
//        $object->test($data);
    }
}

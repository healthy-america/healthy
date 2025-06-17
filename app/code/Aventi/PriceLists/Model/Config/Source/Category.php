<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */

namespace Aventi\PriceLists\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Serialize\SerializerInterface;
use \Psr\Log\LoggerInterface;

/**
 * Class Products
 * @package Aventi\PriceLists\Model\Config\Source
 */
class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    private $_categoryCollection;
    protected $serializer;
    protected $cache;
    protected $dataObjectFactory;
    private LoggerInterface $logger;

    public function __construct(
        CollectionFactory $categoryCollection,
        DataObjectFactory $dataObjectFactory,
        CacheInterface $cache,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->_categoryCollection = $categoryCollection;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $data = $this->cache->load('product_categories');

        if ($data == null) {
            $options = [];
            $collection = $this->_categoryCollection->create()->addAttributeToSelect('*');
            $collection->setPageSize(50)
                ->setCurPage(1);

            foreach ($collection as $category) {
                $options[$category->getId()] = [
                    'value' => $category->getId(),
                    'label' => $category->getName(),
                    'is_active' => $category->getIsActive(),
                    'path' => $category->getSku(),
                    'optgroup' => false
                ];
            }

            $data = $this->serializer->serialize($options);
            $this->cache->save($data, 'product_categories', [], 3600);
            $data = $this->cache->load('product_categories');
        }

        return $this->serializer->unserialize($data) ?? [];
    }
}

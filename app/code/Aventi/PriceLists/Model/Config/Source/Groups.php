<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model\Config\Source;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Groups
 * @package Aventi\PriceLists\Model\Config\Source
 */
class Groups implements \Magento\Framework\Data\OptionSourceInterface
{
    private $_groupCollection;

    /**
     * @var array
     */
    protected $groupTree;
    protected $serializer;
    protected $cache;
    protected $dataObjectFactory;

    /**
     * Groups constructor.
     * @param CollectionFactory $groupCollection
     * @param DataObjectFactory $dataObjectFactory
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        CollectionFactory $groupCollection,
        DataObjectFactory $dataObjectFactory,
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
        $this->_groupCollection = $groupCollection;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getGroupTree();
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     */
    protected function getGroupTree()
    {
        $data = $this->cache->load('group_collection');
        if ($data == null) {
            $collection = $this->_groupCollection->create();
            //$collection->addNameToSelect();
            $collection->addFieldToSelect('*');
            $collection->setPageSize(20)
                ->setCurPage(1);
            foreach ($collection as $group) {
                $groupId = $group->getId();
                if (!isset($this->groupTree[$groupId])) {
                    $this->groupTree[$groupId] = [
                        'value' => $groupId,
                        'label' => $group->getCustomerGroupCode(),
                        'is_active' => true,
                        'path' => $groupId,
                        'optgroup' => false
                    ];
                }
            }


            $data = $this->serializer->serialize($this->groupTree);
            $this->cache->save($data, 'group_collection', [], 3600);
            $data = $this->cache->load('group_collection');
        }

        return $this->serializer->unserialize($data) ?? [];
    }
}

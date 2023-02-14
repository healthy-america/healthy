<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Plugin\Magento\Customer\Controller\Adminhtml\Group;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;

class Save
{
    /**
     * @var FilterBuilder
     */
    protected FilterBuilder $_filterBuilder;
    /**
     * @var GroupFactory
     */
    protected GroupFactory $_groupFactory;
    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $_groupRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @param FilterBuilder $filterBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupFactory $groupFactory
     */
    public function __construct(
        FilterBuilder $filterBuilder,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GroupFactory $groupFactory
    ) {
        $this->_filterBuilder = $filterBuilder;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_groupFactory = $groupFactory;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Adminhtml\Group\Save $subject,
                                                          $result
    ) {
        $request = $subject->getRequest();

        $sapId = $request->getParam('sap');
        $code = $request->getParam('code');

        if (empty($code)) {
            $code = 'NOT LOGGED IN';
        }

        $_filter = [ $this->_filterBuilder->setField('customer_group_code')->setConditionType('eq')->setValue($code)->create() ];
        $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->addFilters($_filter)->create())->getItems();
        $customerGroup = array_shift($customerGroups);

        if($customerGroup){
            $group = $this->_groupFactory->create();
            $group->load($customerGroup->getId());
            $group->setCode($customerGroup->getCode());
            $group->setData('sap', $sapId);
            $group->save();
        }

        return $result;
    }
}

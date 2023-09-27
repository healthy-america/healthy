<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Controller\Adminhtml;

abstract class City extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Aventi_CityDropDown::Aventi';

    /**
     * @var \Magento\Framework\Registry
     */
    protected \Magento\Framework\Registry $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage(\Magento\Backend\Model\View\Result\Page $resultPage): \Magento\Backend\Model\View\Result\Page
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Aventi'), __('Aventi'))
            ->addBreadcrumb(__('City'), __('City'));
        return $resultPage;
    }
}


<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Block\Adminhtml\PriceListCategories\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CancelButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        $targetName = 'avpricelists_pricelist_form.areas.categories.categories.categories_update_modal';
        return [
            'label' => __('Cancel'),
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => $targetName,
                                'actionName' => 'closeModal'
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 20
        ];
    }
}

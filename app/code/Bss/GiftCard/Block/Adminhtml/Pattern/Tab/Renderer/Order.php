<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Block\Adminhtml\Pattern\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

/**
 * Class order
 *
 * Bss\GiftCard\Block\Adminhtml\Pattern\Tab\Renderer
 */
class Order extends AbstractRenderer
{
    /**
     * Render
     *
     * @param   \Magento\Framework\DataObject $item
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $item)
    {
        $url = $this->getUrl(
            'sales/order/view',
            ['order_id' => $item->getOrderId()]
        );
        $html = '<span>';
        $html .= '<a href="' . $url . '"">';
        $html .= $item->getIncrementId();
        $html .= '</a>';
        $html .= '</span>';
        return $html;
    }
}

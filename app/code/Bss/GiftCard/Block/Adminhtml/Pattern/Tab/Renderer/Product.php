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

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class product
 *
 * Bss\GiftCard\Block\Adminhtml\Pattern\Tab\Renderer
 */
class Product extends AbstractRenderer
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * Render
     *
     * @param \Magento\Framework\DataObject $item
     * @return null|string
     */
    public function render(\Magento\Framework\DataObject $item)
    {
        $productId = $item->getProductId();
        try {
            $product = $this->productRepository->getById($productId);
            $url = $this->getUrl(
                'catalog/product/edit',
                ['id' => $productId]
            );
            $html = '<span>';
            $html .= '<a href="' . $url . '"">';
            $html .= $this->escapeHtml($product->getSku());
            $html .= '</a>';
            $html .= '</span>';
            return $html;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return null;
        }
    }
}

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
namespace Bss\GiftCard\Block\Wishlist\Configure;

use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;
use Magento\Framework\View\Element\Template;
use Magento\Wishlist\Model\Item as WishlistItem;
use Magento\Wishlist\Model\ItemFactory as WishlistItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item as WishlistItemResource;

class Updater extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Bss_GiftCard::wishlist/configure.phtml';

    /**
     * @var WishlistItemFactory
     */
    protected $wishlistItemFactory;

    /**
     * @var WishlistItemResource
     */
    protected $wishlistItemResource;

    /**
     * @var JsonSerialize
     */
    protected $jsonSerialize;

    /**
     * Updater constructor.
     * @param Template\Context $context
     * @param WishlistItemFactory $wishlistItemFactory
     * @param WishlistItemResource $wishlistItemResource
     * @param JsonSerialize $jsonSerialize
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        WishlistItemFactory $wishlistItemFactory,
        WishlistItemResource $wishlistItemResource,
        JsonSerialize $jsonSerialize,
        array $data = []
    ) {
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->wishlistItemResource = $wishlistItemResource;
        $this->jsonSerialize = $jsonSerialize;
        parent::__construct($context, $data);
    }

    /**
     * Get update data
     *
     * @return bool|false|string
     */
    public function getUpdaterData()
    {
        $wishlistItemId = $this->getRequest()->getParam('id');
        /** @var WishlistItem $wishlistItem */
        $wishlistItem = $this->wishlistItemFactory->create();
        $wishlistItem->loadWithOptions($wishlistItemId, 'info_buyRequest');
        $infoRequest = $wishlistItem->getOptions()[0]->getValue();
        $infoRequestArr = $this->jsonSerialize->unserialize($infoRequest);

        // Replace qty on add to wishlist by the real qty
        if (isset($infoRequestArr['qty'])) {
            $realQty = $this->correctQty($wishlistItem->getData('qty'));
            $infoRequestArr['qty'] = $realQty;
        }

        unset($infoRequestArr['form_key']);
        unset($infoRequestArr['uenc']);
        unset($infoRequestArr['product_action']);
        unset($infoRequestArr['action']);
        unset($infoRequestArr['product']);
        return $this->jsonSerialize->serialize(['itemOptions' => $infoRequestArr]);
    }

    /**
     * Correct qty data type
     *
     * @param string|int|float $qty
     * @return float|int
     */
    protected function correctQty($qty)
    {
        if ((int) $qty == $qty) {
            return (int) $qty;
        } else {
            return (float) $qty;
        }
    }
}

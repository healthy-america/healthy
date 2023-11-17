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
namespace Bss\GiftCard\Plugin\Quote;

use Bss\GiftCard\Helper\Data;
use Magento\Quote\Api\Data\TotalSegmentExtensionFactory;

/**
 * Class grand total details
 * Bss\GiftCard\Plugin\Quote
 */
class GrandTotalDetails
{
    /**
     * @var \Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory
     */
    private $detailsFactory;

    /**
     * @var TotalSegmentExtensionFactory
     */
    private $totalSegmentExtensionFactory;

    /**
     * @var string
     */
    private $code = 'bss_giftcard';

    /**
     * @var Data
     */
    private $giftCardHelper;

    /**
     * GrandTotalDetails constructor.
     * @param \Bss\GiftCard\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory
     * @param TotalSegmentExtensionFactory $totalSegmentExtensionFactory
     * @param Data $giftCardHelper
     */
    public function __construct(
        \Bss\GiftCard\Api\Data\GrandTotalDetailsInterfaceFactory $detailsFactory,
        TotalSegmentExtensionFactory $totalSegmentExtensionFactory,
        Data $giftCardHelper
    ) {
        $this->detailsFactory = $detailsFactory;
        $this->totalSegmentExtensionFactory = $totalSegmentExtensionFactory;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * Process
     *
     * @param \Magento\Quote\Model\Cart\TotalsConverter $subject
     * @param \Magento\Quote\Api\Data\TotalSegmentInterface[] $totalSegments
     * @param \Magento\Quote\Model\Quote\Address\Total[] $addressTotals
     * @return \Magento\Quote\Api\Data\TotalSegmentInterface[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        \Magento\Quote\Model\Cart\TotalsConverter $subject,
        array $totalSegments,
        array $addressTotals = []
    ) {
        if (!array_key_exists($this->code, $addressTotals)) {
            return $totalSegments;
        }

        $giftCardData = $addressTotals[$this->code]->getData();
        if (!array_key_exists('gift_card', $giftCardData)) {
            return $totalSegments;
        }

        $finalData = [];
        $fullInfo = $giftCardData['gift_card'];
        foreach ($fullInfo as $info) {
            $giftCardDetails = $this->detailsFactory->create([]);
            $giftCardDetails->setAmount($info['giftcard_amount']);
            $giftCardDetails->setTitle(__('Gift Card (%1)', $this->giftCardHelper->hideCode($info['giftcard_code'])));
            $finalData[] = $giftCardDetails;
        }

        $attributes = $totalSegments[$this->code]->getExtensionAttributes();
        if ($attributes === null) {
            $attributes = $this->totalSegmentExtensionFactory->create();
        }

        $attributes->setBssGiftcardDetails($finalData);
        $totalSegments[$this->code]->setExtensionAttributes($attributes);
        return $totalSegments;
    }
}

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

namespace Bss\GiftCard\Observer;

use Bss\GiftCard\Helper\Catalog\Product\Configuration;
use Bss\GiftCard\Helper\Data as GiftCardData;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\PatternFactory;
use Bss\GiftCard\Model\Product\Type\GiftCard;
use Bss\GiftCard\Model\Template\Image\Config;
use Bss\GiftCard\Model\Template\ImageFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AutoGenerateCode implements ObserverInterface
{
    /**
     * @var \Bss\GiftCard\Helper\Catalog\Product\Configuration
     */
    private $configurationHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PatternFactory
     */
    private $giftCardPattern;

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var ImageFactory
     */
    private $imageModelFactory;

    /**
     * @var GiftCardData
     */
    private $giftCardData;

    /**
     * @var Config
     */
    private $imageConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * AutoGenerateCode constructor.
     * @param PatternFactory $giftCardPattern
     * @param Configuration $configurationHelper
     * @param ProductRepositoryInterface $productRepository
     * @param CodeFactory $codeFactory
     * @param Registry $registry
     * @param DateTimeFactory $dateFactory
     * @param ImageFactory $imageModelFactory
     * @param GiftCardData $giftCardData
     * @param Config $imageConfig
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PatternFactory             $giftCardPattern,
        Configuration              $configurationHelper,
        ProductRepositoryInterface $productRepository,
        CodeFactory                $codeFactory,
        Registry                   $registry,
        DateTimeFactory            $dateFactory,
        ImageFactory               $imageModelFactory,
        GiftCardData               $giftCardData,
        Config                     $imageConfig,
        TimezoneInterface          $localeDate,
        StoreManagerInterface      $storeManager
    ) {
        $this->giftCardPattern = $giftCardPattern;
        $this->configurationHelper = $configurationHelper;
        $this->productRepository = $productRepository;
        $this->codeFactory = $codeFactory;
        $this->registry = $registry;
        $this->dateFactory = $dateFactory;
        $this->imageModelFactory = $imageModelFactory;
        $this->giftCardData = $giftCardData;
        $this->imageConfig = $imageConfig;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $invoice = $observer->getEvent()->getInvoice();
        $data = [];
        $storeId = $invoice->getStoreId();
        $store = $this->storeManager->getStore($storeId);
        $patterns = [];
        $datas = [];
        $varses = [];
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->getProductType() === GiftCard::BSS_GIFT_CARD) {
                $options = $orderItem->getProductOptions();
                $infoBuyRequest = $options['info_buyRequest'];
                $this->convertDate($infoBuyRequest);
                $amount = $this->configurationHelper->renderAmount($infoBuyRequest);
                $product = $this->productRepository->getById($orderItem->getProductId());
                $data['amount'] = $amount;
                $patternId = $product->getBssGiftCardCodePattern();
                $codeModel = $this->codeFactory->create();
                $pattern = $this->loadPattern($patternId);
                $qty = $item->getQty();
                $expiry = (float)$product->getBssGiftCardExpires();
                $expiry_date = null;
                if ($expiry && $expiry > 0) {
                    $expiry = '+' . $expiry . ' day';
                    $gmtDate = $this->dateFactory->create()->gmtDate('Y-m-d');
                    $timeSecond = strtotime($expiry, strtotime($gmtDate));
                    $expiry = $this->dateFactory->create()->gmtDate('Y-m-d', $timeSecond);
                    $expiry_date = $expiry;
                }
                if ($qty > 0) {
                    $data['qty'] = $qty;
                    $data['expiry'] = $expiry;
                    $data['order_id'] = $invoice->getOrderId();
                    $data['product_id'] = $item->getProductId();
                    $data['website_id'] = $store->getWebsiteId();
                    $data['store_id'] = $storeId;
                    if (isset($infoBuyRequest['bss_giftcard_delivery_date'])) {
                        $data['delivery_date'] = $infoBuyRequest['bss_giftcard_delivery_date'];
                    }
                    $selectIamge = isset($infoBuyRequest['bss_giftcard_selected_image']);
                    $senderName = isset($infoBuyRequest['bss_giftcard_sender_name']);
                    $recipientName = isset($infoBuyRequest['bss_giftcard_recipient_name']);
                    $image = $selectIamge ? $this->loadImage($infoBuyRequest['bss_giftcard_selected_image']) : null;
                    $vars = [
                        'senderName' => $senderName ? $infoBuyRequest['bss_giftcard_sender_name'] : null,
                        'recipientName' => $recipientName ? $infoBuyRequest['bss_giftcard_recipient_name'] : null,
                        'value' => $this->giftCardData->convertPrice($amount),
                        'img_url' => $image ? $this->imageConfig->getTmpMediaUrl($image->getValue()) : null,
                        'img_alt' => $image ? $image->getLabel() : null,
                        'expires' => $expiry_date
                    ];
                    if (isset($infoBuyRequest['bss_giftcard_message_email'])) {
                        $vars['message'] = $infoBuyRequest['bss_giftcard_message_email'];
                    }
                    if (isset($infoBuyRequest['bss_giftcard_delivery_date'])) {
                        $vars['delivery'] = $infoBuyRequest['bss_giftcard_delivery_date'];
                    }
                    $vars = $this->setDataValue($infoBuyRequest, $vars);
                    $patterns[] = $pattern;
                    $datas[] = $data;
                    $varses[] = $vars;
                    if (!$data['order_id']) {
                        $order->setPatterns($patterns);
                        $order->setCustomDatas($datas);
                        $order->setVarses($varses);
                    }
                    if (!$codeModel->generateCodes($pattern, $data, $vars)) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We cannot register invoice. Please check pattern code')
                        );
                    }
                }
            }
        }
    }

    /**
     * Set value
     *
     * @param array $infoBuyRequest
     * @param array $vars
     *
     * @return mixed
     */
    private function setDataValue($infoBuyRequest, $vars)
    {
        $keys = ['bss_giftcard_sender_name', 'bss_giftcard_sender_email', 'bss_giftcard_recipient_name',
            'bss_giftcard_recipient_email', 'bss_giftcard_selected_image', 'bss_giftcard_template'];

        foreach ($keys as $key) {
            if (isset($infoBuyRequest[$key])) {
                $vars[$key] = $infoBuyRequest[$key];
            }
        }
        return $vars;
    }

    /**
     * Load pattern
     *
     * @param int $patternId
     *
     * @return mixed
     */
    private function loadPattern($patternId)
    {
        return $this->giftCardPattern->create()->load($patternId);
    }

    /**
     * Load Image
     *
     * @param int $imageId
     *
     * @return mixed
     */
    private function loadImage($imageId)
    {
        return $this->imageModelFactory->create()->load($imageId);
    }

    /**
     * Convert date
     *
     * @param array $infoBuyRequest
     */
    private function convertDate(&$infoBuyRequest)
    {
        if (isset($infoBuyRequest['bss_giftcard_delivery_date'])
            && $infoBuyRequest['bss_giftcard_delivery_date']
            && isset($infoBuyRequest['bss_giftcard_timezone'])
            && $infoBuyRequest['bss_giftcard_timezone']
        ) {
            $datetime = date_create(
                $infoBuyRequest['bss_giftcard_delivery_date'],
                timezone_open($infoBuyRequest['bss_giftcard_timezone'])
            );

            $sendAt = $this->localeDate->date($datetime);
            $infoBuyRequest['bss_giftcard_delivery_date'] = $sendAt;
        }
    }
}

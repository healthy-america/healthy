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

namespace Bss\GiftCard\Controller\Customer;

use Bss\GiftCard\Helper\Data;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class gift card details
 *
 * Bss\GiftCard\Controller\Customer
 */
class GiftCardDetails extends Action\Action
{
    /**
     * @var Data
     */
    private $giftCardData;

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param EncoderInterface $jsonEncoder
     * @param CodeFactory $codeFactory
     * @param Data $giftCardData
     * @param Escaper $escaper
     * @param Validator $formKeyValidator
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Action\Context $context,
        EncoderInterface $jsonEncoder,
        CodeFactory $codeFactory,
        Data $giftCardData,
        Escaper $escaper,
        Validator $formKeyValidator,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->giftCardData = $giftCardData;
        $this->codeFactory = $codeFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->escaper = $escaper;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Execute
     *
     * @return mixed
     */
    public function execute()
    {
        $result = ['success' => false];
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $code = $this->getRequest()->getParam('bss_gc_code');

            $giftCard = $this->codeFactory->create()->loadByCode($code);
            if ($giftCard->getId()) {
                $result['success'] = true;
                $result['content'] = [
                    'code' => $this->escaper->escapeHtml($code),
                    'value' => $this->giftCardData->convertPrice($giftCard->getValue()),
                    'origin_value' => $this->giftCardData->convertPrice($giftCard->getOriginValue()),
                    'status' => $giftCard->getStatusLabel(),
                    'created_at' => $this->giftCardData->formatDateTime($giftCard->getCreatedTime()),
                    'updated_at' => $this->giftCardData->formatDateTime($giftCard->getUpdatedTime())
                ];
                if ($giftCard->getExpiryDay()) {
                    $result['content']['expire_date'] = $this->giftCardData->formatDateTime($giftCard->getExpiryDay());
                }
            } else {
                $result['message'] = __('The gift card code "%1" is not valid.', $this->escaper->escapeHtml($code));
            }
        }
        $response = $this->resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }
}

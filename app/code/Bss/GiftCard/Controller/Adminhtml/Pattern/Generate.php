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

namespace Bss\GiftCard\Controller\Adminhtml\Pattern;

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;

/**
 * Class generate
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern
 */
class Generate extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $patternId = (int) $this->getRequest()->getParam('id');
        $generateQty = (int) $this->getRequest()->getParam('qty');
        $expiry = $this->getRequest()->getParam('expiry');
        $amount = (float) $this->getRequest()->getParam('amount');
        $result = [
            'status' => false,
            'message' => __('Please check again.')
        ];
        if ($patternId && $generateQty && $amount) {
            try {
                $codeModel = $this->codeFactory->create();
                $pattern = $this->giftCardPattern->create()->load($patternId);
                if (!$pattern->validateQtyCode($generateQty)) {
                    $result['message'] = __('There are too few for generate.');
                } else {
                    $data = [
                        'id' => $patternId,
                        'qty' => $generateQty,
                        'expiry' => $expiry,
                        'amount' => $amount
                    ];
                    if ($codeModel->generateCodes($pattern, $data)) {
                        $result = [
                            'status' => true,
                            'totalQty' => (int)$pattern->getPatternCodeQty() + $generateQty,
                            'totalQtyUnused' => (int)$pattern->getPatternCodeUnused() + $generateQty,
                            'message' => __($generateQty . ' coupon(s) have been generated.', $generateQty)
                        ];
                    }
                }
            } catch (\Exception $e) {
                $result = [
                    'message' => $e->getCode()
                ];
            }
        }
        $response = $this->resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }
}

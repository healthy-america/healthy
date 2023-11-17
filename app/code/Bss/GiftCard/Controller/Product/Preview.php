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

namespace Bss\GiftCard\Controller\Product;

use Bss\GiftCard\Helper\Catalog\Product\Configuration;
use Bss\GiftCard\Model\Email;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class preview
 *
 * Bss\GiftCard\Controller\Product
 */
class Preview extends Action
{
    /**
     * @var Email
     */
    private $emailModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configuration
     */
    private $configurationHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * Construct.
     *
     * @param Context $context
     * @param Email $emailModel
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Configuration $configurationHelper
     */
    public function __construct(
        Context $context,
        Email $emailModel,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Configuration $configurationHelper
    ) {
        parent::__construct(
            $context
        );
        $this->emailModel = $emailModel;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->response = $context->getResponse();
        $this->configurationHelper = $configurationHelper;
    }

    /**
     * Execute
     *
     * @return string
     */
    public function execute()
    {
        $param = $this->getRequest()->getParam('formData');
        $data = $this->convertData($param);
        $result = [];
        try {
            $store = $this->storeManager->getStore();
            $content = $this->emailModel->previewEmail($data, $store);
            $result['success'] = true;
            $result['content'] = $content;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result['success'] = false;
            $result['content'] = __('You can\'t preview email now.');
        }
        $resultJson = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    /**
     * Convert data
     *
     * @param   array $param
     * @return  array
     */
    private function convertData($param)
    {
        $data = array_reduce($param, function ($result, $item) {
            $result[$item['name']] = $item['value'];
            return $result;
        }, []);
        $amount = $this->configurationHelper->renderAmount($data);
        $data[Configuration::GIFTCARD_AMOUNT] = 0;
        if ($amount) {
            $data[Configuration::GIFTCARD_AMOUNT] = $amount;
        }
        return $data;
    }
}

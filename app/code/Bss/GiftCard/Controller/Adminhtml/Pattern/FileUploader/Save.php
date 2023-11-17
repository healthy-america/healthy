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

namespace Bss\GiftCard\Controller\Adminhtml\Pattern\FileUploader;

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;

/**
 * Class save
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern\FileUploader
 */
class Save extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $name = $this->getRequest()->getParam('param_name');
        $patternId = $this->getRequest()->getParam('id');
        $uploader = $this->fileUploaderFactory->create(['fileId' => $name]);
        $uploader->setAllowedExtensions(['csv']);
        $file = $uploader->validateFile();
        $importCodestRawData = $this->csvProcessor->getData($file['tmp_name']);
        $message = '';
        if ($importCodestRawData[0][0] != 'code' &&
            $importCodestRawData[0][1] != 'value' &&
            $importCodestRawData[0][2] != "expiry date"
        ) {
            $result = [
                'status' => false,
                'message' => __('The file\'s format is not correct. Please download sample csv file and try again.')
            ];
        } else {
            try {
                array_shift($importCodestRawData);
                $code = $this->codeFactory->create();
                if (!empty($importCodestRawData)) {
                    $data = $code->importCodes($importCodestRawData, $patternId);
                    if ($data['error']) {
                        $message .= __(
                            '%1 code(s) bring errors because of duplicate data or
                            empty row or Code is incorrect format. Please check again.',
                            $data['error']
                        );
                    } else {
                        $message = __('%1 code(s) have been successfully imported.', $data['success']);
                    }
                } else {
                    $message = __('Empty row. Please check again.');
                }
                $result = [
                    'status' => true,
                    'message' => $message,
                    'new_items' => $data['success'] ?? []
                ];
            } catch (\Exception $e) {
                $result = [
                    'status' => false,
                    'message' => $e->getMessage()
                ];
            }
        }
        $response = $this->resultJsonFactory->create();
        $response->setData($result);
        return $response;
    }
}

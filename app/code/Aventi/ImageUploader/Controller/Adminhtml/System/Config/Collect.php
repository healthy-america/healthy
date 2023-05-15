<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aventi\ImageUploader\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;

class Collect extends Action
{
    protected $resultJsonFactory;

    /**
     * @var \Aventi\ImageUploader\Model\Image\Process
     */
    protected $image;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Aventi\ImageUploader\Model\Image\Process $image
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Aventi\ImageUploader\Model\Image\Process $image
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->image = $image;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $res = null;
        try {
            $res = $this->image->update();
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        return $result->setData(['success' => true, 'result' => $res]);
    }
}

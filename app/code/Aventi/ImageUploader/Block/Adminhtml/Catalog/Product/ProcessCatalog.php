<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */

namespace Aventi\ImageUploader\Block\Adminhtml\Catalog\Product;

use Aventi\ImageUploader\Model\Catalog\Product\ProcessMedia;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Psr\Log\LoggerInterface;

class ProcessCatalog extends Template
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;
    /**
     * @var ProcessMedia $_processMedia
     */
    protected $_processMedia;

    /**
     * @param Context $context
     * @param array $data
     * @param LoggerInterface $logger
     * @param ProcessMedia $_processMedia
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ProcessMedia $_processMedia,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->logger = $logger;
        $this->_processMedia = $_processMedia;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processAllProducts()
    {
        return $this->_processMedia->processAllProducts();
    }
}

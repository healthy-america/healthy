<?php

namespace Aventi\ImageUploader\Block\Adminhtml\Form;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ProcessCatalogButton implements ButtonProviderInterface
{

    /** @var UrlInterface */
    protected $urlInterface;

    public function __construct(
        UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    public function getButtonData()
    {
        return [
            'label' => __('Process Catalog'),
            'on_click' => 'deleteConfirm(\'' . __(
                'When this process is started, the entire current image catalog will be processed to the format used by Aventi Solutions. Do you want to continue?'
            ) . '\', \'' . $this->getBtnUrl() . '\')',
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    public function getBtnUrl()
    {
        return $this->urlInterface->getUrl('*/*/processCatalog');
    }
}

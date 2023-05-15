<?php

namespace Aventi\ImageUploader\Ui\Component\Columns;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{

    /** Url path */
    const ROW_EDIT_URL = 'imageuploader/images/delete';

    /** @var UrlInterface */
    protected $_urlBuilder;

    /**
     * @var string
     */
    private $_editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::ROW_EDIT_URL
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                // here we can also use the data from $item to configure some parameters of an action URL
                $item[$this->getData('name')] = [
                    /*'edit' => [
                        'href' => '/edit',
                        'label' => __('Edit')
                    ],*/
                    'remove' => [
                        'href' => $this->_urlBuilder->getUrl(
                            $this->_editUrl,
                            ['id' => $item['image_id']]
                        ),
                        'label' => __('Remove'),
                        'confirm' => [
                            'title' => __('Delete %1 Pos %2', $item['sku'], $item['pos_img']),
                            'message' => __('Are you sure you want to delete the picture for the sku %1?', $item['sku'])
                        ]
                    ],
                ];
            }
        }

        return $dataSource;
    }
}

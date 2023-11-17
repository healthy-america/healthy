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

namespace Bss\GiftCard\Ui\Component\Listing\Columns\Template;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Bss\GiftCard\Model\Template\ImageFactory;
use Bss\GiftCard\Model\Template\Image\Config;

/**
 * Class images
 *
 * Bss\GiftCard\Ui\Component\Listing\Columns\Template
 */
class Images extends Column
{
    /**
     * @var ImageFactory
     */
    private $imageModel;

    /**
     * @var Config
     */
    private $imageConfig;

    /**
     * @param ContextInterface $context
     * @param ImageFactory $imageModel
     * @param Config $imageConfig
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        ImageFactory $imageModel,
        Config $imageConfig,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->imageModel = $imageModel;
        $this->imageConfig = $imageConfig;
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $templateId = $item['template_id'];
                if (isset($templateId)) {
                    $imagesModel = $this->imageModel->create();
                    $images = $imagesModel->loadByTemplate($templateId);
                    if (!empty($images)) {
                        $item['images'] = $images;
                    }
                }
            }
        }

        return $dataSource;
    }
}

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

namespace Bss\GiftCard\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Bss\GiftCard\Model\TemplateFactory;

/**
 * Class template
 *
 * Bss\GiftCard\Model\Attribute\Source
 */
class Template extends AbstractSource
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @param TemplateFactory $templateFactory
     */
    public function __construct(
        TemplateFactory $templateFactory
    ) {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        $templateCollection = $this->templateFactory->create()->getCollection();
        $templateCollection->filterVisiable();

        if (null === $this->_options) {
            $this->_options = [];
            foreach ($templateCollection as $template) {
                $this->_options[] = [
                    'label' => $template->getName(),
                    'value' => $template->getTemplateId()
                ];
            }
        }
        return $this->_options;
    }
}

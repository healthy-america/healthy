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

namespace Bss\GiftCard\Block\Adminhtml\Template\Helper\Form;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Data\Form;
use Bss\GiftCard\Block\Adminhtml\Template\Helper\Form\Image\Content;

/**
 * Class image
 *
 * Bss\GiftCard\Block\Adminhtml\Template\Helper\Form
 */
class Image extends AbstractBlock
{
    /**
     * Gallery field name suffix
     *
     * @var string
     */
    protected $fieldNameSuffix = 'bss_giftcard_template';

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $form;

    /**
     * Image html id
     *
     * @var string
     */
    private $htmlId = 'bss_giftcard_image';

    /**
     * @var string
     */
    private $formName = 'bssgiftcard_template_form';

    /**
     * Gallery name
     *
     * @var string
     */
    protected $name = 'bssGiftCard';

    /**
     * @param Context $context
     * @param Form $form
     * @param array $data
     */
    public function __construct(
        Context $context,
        Form $form,
        array $data = []
    ) {
        $this->form = $form;
        parent::__construct($context, $data);
    }

    /**
     * To html
     *
     * @return string
     */
    public function toHtml()
    {
        return $this->getElementHtml();
    }

    /**
     * Get file name suffix
     *
     * @return string
     */
    public function getFieldNameSuffix()
    {
        return $this->fieldNameSuffix;
    }

    /**
     * Get element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getContentHtml();
        return $html;
    }

    /**
     * Get html id
     *
     * @return string
     */
    private function getHtmlId()
    {
        return $this->htmlId;
    }

    /**
     * Prepares content block
     *
     * @return string
     */
    public function getContentHtml()
    {
        /* @var $content \Bss\GiftCard\Block\Adminhtml\Template\Helper\Form\Image\Content */
        $content = $this->getLayout()
            ->createBlock(Content::class, 'bssgiftcard_template_image_content')
            ->setId($this->getHtmlId() . '_content')->setElement($this)
            ->setFormName($this->formName);
        $imageJs = $content->getJsObjectName();
        $content->getUploader()->getConfig()->setMediaGallery($imageJs);
        return $content->toHtml();
    }

    /**
     * Retrieve attribute field name
     *
     * @param   string $name
     * @return  string
     */
    public function getFieldName($name)
    {
        if ($suffix = $this->getFieldNameSuffix()) {
            $name = $this->form->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}

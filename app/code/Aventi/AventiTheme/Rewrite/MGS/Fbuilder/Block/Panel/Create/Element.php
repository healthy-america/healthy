<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\AventiTheme\Rewrite\MGS\Fbuilder\Block\Panel\Create;

class Element extends \MGS\Fbuilder\Block\Panel\Create\Element
{
    protected $_params = [];

    /**
     *
     * @return void
     */
    protected function _construct()
    {
        if (!$this->getTemplate()) {
            $this->_params = $this->getRequest()->getParams();
            if (isset($this->_params['type'])) {
                if ($this->_params['type'] == 'owl_banner') {
                    $this->setTemplate('Aventi_AventiTheme::panel/create/element/'.$this->_params['type'].'.phtml');
                } else {
                    $this->setTemplate('MGS_Fbuilder::panel/create/element/'.$this->_params['type'].'.phtml');
                }
            } else {
                if ($this->_params['cms']=='block') {
                    $this->setTemplate('MGS_Fbuilder::panel/edit/block.phtml');
                } else {
                    $this->setTemplate('MGS_Fbuilder::panel/edit/page.phtml');
                }
            }
        }
    }

    public function getParams()
    {
        return $this->_params;
    }

    public function getPanelUploadSrc($type, $file)
    {
        return $this->getPanelUploadFolder($type).$file;
    }

    public function getPanelUploadFolder($type)
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'wysiwyg/'.$type.'/';
    }


}


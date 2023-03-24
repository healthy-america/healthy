<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\AventiTheme\Rewrite\MGS\Fbuilder\Block\Widget;

class OwlCarousel extends \MGS\Fbuilder\Block\Widget\OwlCarousel
{

    protected $_template = 'Aventi_AventiTheme::widget/owl_slider.phtml';

    protected function _toHtml()
    {
        $this->setModuleName($this->extractModuleName('MGS\Fbuilder\Block\Widget\OwlCarousel'));
        return parent::_toHtml();
    }

    public function getIsCategory()
    {

        if (!is_null($this->getData('is_category'))) {
            if ($this->getData('is_category') != "" && $this->getData('is_category') == 1) {
                return 'true';
            }
        }

        return 'false';
    }
}

<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */


namespace Aventi\Core\Block\Adminhtml;

use Aventi\Core\Model\ModuleListProcessor;
use Magento\Backend\Block\Template;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Extensions extends Field
{

    protected $_template = 'Aventi_Core::modules.phtml';

    /**
     * @var ModuleListProcessor
     */
    private $moduleListProcessor;


    public function __construct(
        Template\Context $context,
        ModuleListProcessor $moduleListProcessor,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleListProcessor = $moduleListProcessor;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->moduleListProcessor->getModules();
    }

}

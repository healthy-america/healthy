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

namespace Bss\GiftCard\Ui\Component\Pattern\Form\Element;

use Magento\Ui\Component\Form\Element\Input;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Registry;

/**
 * Class pattern
 * Bss\GiftCard\Ui\Component\Pattern\Form\Element
 */
class Pattern extends Input
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param Registry $registry
     * @param UiComponentInterface $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Registry $registry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $components,
            $data
        );
        $this->registry = $registry;
    }
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        $pattern = $this->registry->registry('pattern');
        if (is_array($config) && $pattern) {
            $config['disabled'] = true;
        }
        $this->setData('config', (array)$config);

        parent::prepare();
    }
}

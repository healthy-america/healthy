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
use Bss\GiftCard\Model\PatternFactory;

/**
 * Class pattern
 *
 * Bss\GiftCard\Model\Attribute\Source
 */
class Pattern extends AbstractSource
{
    /**
     * @var PatternFactory
     */
    private $patternFactory;

    /**
     * @param PatternFactory $patternFactory
     */
    public function __construct(
        PatternFactory $patternFactory
    ) {
        $this->patternFactory = $patternFactory;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        $patternCollection = $this->patternFactory->create()->getCollection();
        $patternCollection->filterVisiable();

        if (null === $this->_options) {
            $this->_options = [];
            foreach ($patternCollection as $pattern) {
                $this->_options[] = [
                    'label' => $pattern->getName(),
                    'value' => $pattern->getPatternId()
                ];
            }
        }
        return $this->_options;
    }
}

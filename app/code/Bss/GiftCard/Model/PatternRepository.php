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

namespace Bss\GiftCard\Model;

use Bss\GiftCard\Api\PatternRepositoryInterface;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class pattern repository
 *
 * Bss\GiftCard\Model
 */
class PatternRepository implements PatternRepositoryInterface
{
    /**
     * @var CodeFactory
     */
    private $codeModel;

    /**
     * @var PatternFactory
     */
    private $patternFactory;

    /**
     * PatternRepository constructor.
     * @param CodeFactory $codeModel
     * @param PatternFactory $patternFactory
     */
    public function __construct(
        CodeFactory $codeModel,
        PatternFactory $patternFactory
    ) {
        $this->codeModel = $codeModel;
        $this->patternFactory = $patternFactory;
    }

    /**
     * Get gift card pattern by id
     *
     * @param int $patternId
     * @param bool $getCodes
     * @return array
     */
    public function getPatternById($patternId, $getCodes = false)
    {
        $pattern = $this->patternFactory->create();
        if (!$patternId) {
            throw new NoSuchEntityException(__('Pattern with id "%1" does not exist.', $patternId));
        }
        $patternData = $pattern->load($patternId)->getData();
        if ($getCodes) {
            $patternData['codes'] = $this->codeModel->create()->getByPattern($patternId);
        }
        return ['pattern_data' => $patternData];
    }
}

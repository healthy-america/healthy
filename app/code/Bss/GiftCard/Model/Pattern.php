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

use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\Product\Type\GiftCard as GiftCardType;
use Bss\GiftCard\Model\ResourceModel\Pattern as PatternResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class pattern
 *
 * Bss\GiftCard\Model
 */
class Pattern extends AbstractModel
{
    /**
     * @var CodeFactory
     */
    private $codeModelFactory;

    /**
     * @var CollectionFactory
     */
    private $productCollection;

    /**
     * Pattern constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CodeFactory $codeModelFactory
     * @param CollectionFactory $productCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CodeFactory $codeModelFactory,
        CollectionFactory $productCollection
    ) {
        $this->codeModelFactory = $codeModelFactory;
        $this->productCollection = $productCollection;
        parent::__construct(
            $context,
            $registry
        );
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(PatternResourceModel::class);
    }

    /**
     * Insert pattern
     *
     * @param array $data
     * @return mixed
     */
    public function insertPattern($data)
    {
        $maxQty = 1;
        $codeModel = $this->codeModelFactory->create();
        $digitCode = $codeModel->dynamicChar(
            $data['pattern'],
            \Bss\GiftCard\Model\Pattern\Code::DIGIT_CODE
        );
        $letterCode = $codeModel->dynamicChar(
            $data['pattern'],
            \Bss\GiftCard\Model\Pattern\Code::LETTER_CODE
        );
        if ($digitCode) {
            $maxQty *= pow(10, $digitCode);
        }
        if ($letterCode) {
            $maxQty *= pow(26, $letterCode);
        }
        $pattern = [
            'name' => $data['name'],
            'pattern' => $data['pattern'],
            'pattern_code_qty_max' => $maxQty * 0.8
        ];
        return $this->getResource()->insertPatternGeneral($pattern, $data['pattern_id']);
    }

    /**
     * Validate pattern
     *
     * @param string $codeName
     * @return bool
     */
    public function validatePattern($codeName)
    {
        if ($this->getResource()->validatePatternCode($codeName)) {
            return false;
        }
        $count = 0;
        $codeModel = $this->codeModelFactory->create();
        $count += $codeModel->dynamicChar(
            $codeName,
            \Bss\GiftCard\Model\Pattern\Code::DIGIT_CODE
        );
        $count += $codeModel->dynamicChar(
            $codeName,
            \Bss\GiftCard\Model\Pattern\Code::LETTER_CODE
        );
        if ($count == 0 || $count > 6) {
            return false;
        }
        return true;
    }

    /**
     * Validate qty code
     *
     * @param int $generateQty
     * @return bool
     */
    public function validateQtyCode($generateQty = 1)
    {
        if ($this->getPatternCodeQtyMax() >= $generateQty) {
            return true;
        }
        return false;
    }

    /**
     * Check if there any product used this pattern
     *
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        $productCount = $this->productCollection->create()
            ->addAttributeToFilter(GiftCardType::BSS_GIFT_CARD_CODE_PATTERN, $this->getId())
            ->count();
        if ($productCount > 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('There are some products using this pattern. Please delete them first.')
            );
        }
        return parent::beforeDelete();
    }
}

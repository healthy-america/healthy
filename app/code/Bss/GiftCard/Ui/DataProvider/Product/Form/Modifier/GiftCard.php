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

namespace Bss\GiftCard\Ui\DataProvider\Product\Form\Modifier;

use Bss\GiftCard\Helper\Data as GiftCardData;
use Bss\GiftCard\Model\Product\Type\GiftCard as GiftCardType;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Directory\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

/**
 * Class gift card
 * Bss\GiftCard\Ui\DataProvider\Product\Form\Modifier
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftCard extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    private $groupCode;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var StoreManagerInterface
     * @since 101.0.0
     */
    private $storeManager;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var GiftCardData
     */
    private $giftCardHelper;

    /**
     * GiftCard constructor.
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param StoreManagerInterface $storeManager
     * @param Data $directoryHelper
     * @param GiftCardData $giftCardHelper
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        StoreManagerInterface $storeManager,
        Data $directoryHelper,
        GiftCardData $giftCardHelper
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->storeManager = $storeManager;
        $this->directoryHelper = $directoryHelper;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * Modify data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if ($expire = $this->giftCardHelper->getConfigSetting('expire_day')) {
            foreach ($data as $id => $value) {
                if (!$id) {
                    $value['product']['bss_gift_card_expires'] = $expire;
                    $data[$id] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->groupCode = GiftCardType::BSS_GIFT_CARD_GROUP_CODE . '.container_';
        $product = $this->locator->getProduct();
        if ($product->getTypeId() != GiftCardType::BSS_GIFT_CARD) {
            return $this->meta;
        }

        $elementPathAmounts = $this->arrayManager->findPath(
            GiftCardType::BSS_GIFT_CARD_AMOUNTS,
            $this->meta,
            null,
            'children'
        );
        $elementPathDynamicPrice = $this->arrayManager->findPath(
            GiftCardType::BSS_GIFT_CARD_DYNAMIC_PRICE,
            $this->meta,
            null,
            'children'
        );
        $elementPathPercentageType = $this->arrayManager->findPath(
            GiftCardType::BSS_GIFT_CARD_PERCENTAGE_TYPE,
            $this->meta,
            null,
            'children'
        );
        $elementPathPercentageValue = $this->arrayManager->findPath(
            GiftCardType::BSS_GIFT_CARD_PERCENTAGE_VALUE,
            $this->meta,
            null,
            'children'
        );

        if ($elementPathAmounts) {
            $this->modifyMetaPathAmounts($elementPathAmounts);
        }

        if ($elementPathDynamicPrice) {
            $this->modifyMetaPathDynamicPrice($elementPathDynamicPrice);
        }

        if ($elementPathPercentageType) {
            $this->modifyMetaPercentageType($elementPathPercentageType);
        }

        if ($elementPathPercentageValue) {
            $this->modifyMetaPercentageValue($elementPathPercentageValue);
        }

        return $this->meta;
    }

    /**
     * Modify meta percentage value
     *
     * @param string|mixed $elementPath
     */
    private function modifyMetaPercentageValue($elementPath)
    {
        $addbeforeConfig = [
            'addbefore' => '%',
            'validation' => [
                'validate-number' => true,
                'validate-digits' => true,
                'less-than-equals-to' => 100
            ],
            'additionalClasses' => 'admin__field-small'
        ];
        $this->meta = $this->arrayManager->merge(
            $elementPath . static::META_CONFIG_PATH,
            $this->meta,
            $addbeforeConfig
        );
    }

    /**
     * Modify meta percentage type
     *
     * @param mixed $elementPath
     */
    private function modifyMetaPercentageType($elementPath)
    {
        $targetPercentageValue = $this->groupCode;
        $targetPercentageValue .= GiftCardType::BSS_GIFT_CARD_PERCENTAGE_VALUE;
        $targetPercentageValue .=  '.';
        $targetPercentageValue .= GiftCardType::BSS_GIFT_CARD_PERCENTAGE_VALUE;

        $switcherConfig = [
            'switcherConfig' => [
                'enabled' => true,
                'rules' => [
                    [
                        'value' => '0',
                        'actions' => [
                            [
                                'target' => 'product_form.product_form.' . $targetPercentageValue,
                                'callback' => 'disable'
                            ]
                        ]
                    ],
                    [
                        'value' => '1',
                        'actions' => [
                            [
                                'target' => 'product_form.product_form.' . $targetPercentageValue,
                                'callback' => 'enable'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->meta = $this->arrayManager->merge(
            $elementPath . static::META_CONFIG_PATH,
            $this->meta,
            $switcherConfig
        );
    }

    /**
     * Modify meta path dynamic price
     *
     * @param string|mixed $elementPath
     */
    private function modifyMetaPathDynamicPrice($elementPath)
    {
        $targetMinAmount = $this->groupCode;
        $targetMinAmount .= GiftCardType::BSS_GIFT_CARD_OPEN_MIN_AMOUNT;
        $targetMinAmount .=  '.';
        $targetMinAmount .= GiftCardType::BSS_GIFT_CARD_OPEN_MIN_AMOUNT;

        $targetMaxAmount = $this->groupCode;
        $targetMaxAmount .= GiftCardType::BSS_GIFT_CARD_OPEN_MAX_AMOUNT;
        $targetMaxAmount .=  '.';
        $targetMaxAmount .= GiftCardType::BSS_GIFT_CARD_OPEN_MAX_AMOUNT;

        $targetPercentageType = $this->groupCode;
        $targetPercentageType .= GiftCardType::BSS_GIFT_CARD_PERCENTAGE_TYPE;
        $targetPercentageType .=  '.';
        $targetPercentageType .= GiftCardType::BSS_GIFT_CARD_PERCENTAGE_TYPE;

        $targetPercentageValue = $this->groupCode;
        $targetPercentageValue .= GiftCardType::BSS_GIFT_CARD_PERCENTAGE_VALUE;
        $targetPercentageValue .=  '.';
        $targetPercentageValue .= GiftCardType::BSS_GIFT_CARD_PERCENTAGE_VALUE;

        $switcherConfig = [
            'switcherConfig' => [
                'enabled' => true,
                'rules' => [
                    [
                        'value' => '0',
                        'actions' => [
                            [
                                'target' => 'product_form.product_form.' . $targetMinAmount,
                                'callback' => 'hide'
                            ],
                            [
                                'target' => 'product_form.product_form.' . $targetMaxAmount,
                                'callback' => 'hide'
                            ],
                            [
                                'target' => 'product_form.product_form.' . $targetPercentageType,
                                'callback' => 'hide'
                            ],
                            [
                                'target' => 'product_form.product_form.' . $targetPercentageValue,
                                'callback' => 'hide'
                            ]
                        ]
                    ],
                    [
                        'value' => '1',
                        'actions' => [
                            [
                                'target' => 'product_form.product_form.' . $targetMinAmount,
                                'callback' => 'show'
                            ],
                            [
                                'target' => 'product_form.product_form.' . $targetMaxAmount,
                                'callback' => 'show'
                            ],
                            [
                                'target' => 'product_form.product_form.' . $targetPercentageType,
                                'callback' => 'show'
                            ],
                            [
                                'target' => 'product_form.product_form.' . $targetPercentageValue,
                                'callback' => 'show'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->meta = $this->arrayManager->merge(
            $elementPath . static::META_CONFIG_PATH,
            $this->meta,
            $switcherConfig
        );
    }

    /**
     * Modify meta path amounts
     *
     * @param mixed $elementPath
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function modifyMetaPathAmounts($elementPath)
    {
        $containerPath = $this->arrayManager->slicePath($elementPath, 0, -2);
        $fieldsetPath = $this->arrayManager->slicePath($elementPath, 0, -4);
        $this->meta = $this->arrayManager->replace(
            $elementPath,
            $this->meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Amounts'),
                            'componentType' => DynamicRows::NAME,
                            'component' => 'bss/giftcard_dynamic-rows',
                            'itemTemplate' => 'record',
                            'dataScope' => '',
                            'dndConfig' => [
                                'enabled' => false
                            ],
                            'sortOrder' => $this->arrayManager->get(
                                $containerPath . '/arguments/data/config/sortOrder',
                                $this->meta
                            ),
                        ],
                    ],
                ],
                'children' => [
                    'record' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'componentType' => Container::NAME,
                                    'isTemplate' => true,
                                    'is_collection' => true,
                                    'component' => 'Magento_Ui/js/dynamic-rows/record',
                                    'dataScope' => '',
                                ],
                            ],
                        ],
                        'children' => [
                            'website_id' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataType' => Text::NAME,
                                            'formElement' => Select::NAME,
                                            'componentType' => Field::NAME,
                                            'dataScope' => 'website_id',
                                            'label' => __('Website'),
                                            'options' => $this->getWebsites(),
                                            'value' => $this->getDefaultWebsite(),
                                            'visible' => $this->isMultiWebsites(),
                                            'sortOrder' => 10,
                                        ],
                                    ],
                                ],
                            ],
                            'value' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => Field::NAME,
                                            'formElement' => Input::NAME,
                                            'dataType' => Price::NAME,
                                            'label' => __('Value'),
                                            'enableLabel' => true,
                                            'dataScope' => 'value',
                                            'sortOrder' => 20,
                                            'validation' => [
                                                'required-entry' => true,
                                                'validate-zero-or-greater' => true,
                                                'validate-number' => true,
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                            'price' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => Field::NAME,
                                            'formElement' => Input::NAME,
                                            'dataType' => Price::NAME,
                                            'label' => __('Price'),
                                            'enableLabel' => true,
                                            'dataScope' => 'price',
                                            'sortOrder' => 30,
                                            'validation' => [
                                                'required-entry' => true,
                                                'validate-zero-or-greater' => true,
                                                'validate-number' => true,
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                            'actionDelete' => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'componentType' => ActionDelete::NAME,
                                            'label' => '',
                                            'sortOrder' => 40,
                                        ],
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->meta = $this->arrayManager->set(
            $fieldsetPath . '/children/' . GiftCardType::BSS_GIFT_CARD_AMOUNTS,
            $this->meta,
            $this->arrayManager->get($elementPath, $this->meta)
        );
        $this->meta = $this->arrayManager->remove($containerPath, $this->meta);
    }

    /**
     * Get websites list
     *
     * @return array
     */
    private function getWebsites()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $product = $this->locator->getProduct();
        $websitesList = $this->storeManager->getWebsites();
        $productWebsiteIds = $product->getWebsiteIds();

        foreach ($websitesList as $website) {
            /** @var \Magento\Store\Model\Website $website */
            if (!in_array($website->getId(), $productWebsiteIds)) {
                continue;
            }
            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        }

        return $websites;
    }

    /**
     * Get default website
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getDefaultWebsite()
    {
        $storeId = $this->locator->getProduct()->getStoreId();
        return $this->storeManager->getStore($storeId)->getWebsiteId();
    }

    /**
     * Show website column and switcher for group price table
     *
     * @return bool
     */
    private function isMultiWebsites()
    {
        return !$this->storeManager->isSingleStoreMode();
    }
}

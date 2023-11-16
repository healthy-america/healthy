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
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Setup\Patch\Data;

use Bss\GiftCard\Model\Attribute\Source\Backend\Amounts;
use Bss\GiftCard\Model\Attribute\Source\Backend\Template as TemplateBackend;
use Bss\GiftCard\Model\Attribute\Source\Pattern;
use Bss\GiftCard\Model\Attribute\Source\Price as PriceAttribute;
use Bss\GiftCard\Model\Attribute\Source\Template;
use Bss\GiftCard\Model\Attribute\Source\Type;
use Bss\GiftCard\Model\Product\Type\GiftCard;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Class install data
 * Bss\GiftCard\Setup\Patch\Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallAttributeData implements DataPatchInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param ModuleDataSetupInterface $setup
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        ModuleDataSetupInterface $setup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->setup=$setup;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Apply
     *
     * @return InstallData|void
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $groupName = GiftCard::BSS_GIFT_CARD_GROUP;
        $fieldList = [
            'weight',
            'tax_class_id'
        ];

        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array(GiftCard::BSS_GIFT_CARD, $applyTo)) {
                $applyTo[] = GiftCard::BSS_GIFT_CARD;
                $eavSetup->updateAttribute(
                    Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        $entityTypeId = $eavSetup->getEntityTypeId('catalog_product');
        $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityTypeId);

        foreach ($attributeSetIds as $attributeSetId) {
            $eavSetup->addAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                $groupName,
                99
            );
        }

        $this->addAttribute($eavSetup, $groupName);
    }

    /**
     * Add att
     *
     * @param mixed $eavSetup
     * @param string $groupName
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addAttribute($eavSetup, $groupName)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD,
            [
                'group' => $groupName,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 110,
                'label' => 'Type',
                'input' => 'select',
                'class' => '',
                'source' => Type::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_AMOUNTS,
            [
                'group' => $groupName,
                'type' => 'static',
                'backend' => Amounts::class,
                'frontend' => '',
                'sort_order' => 120,
                'label' => 'Amounts',
                'input' => 'text',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_DYNAMIC_PRICE,
            [
                'group' => $groupName,
                'type' => 'int',
                'frontend' => '',
                'sort_order' => 130,
                'label' => 'Dynamic Price',
                'input' => 'boolean',
                'class' => '',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_OPEN_MIN_AMOUNT,
            [
                'group' => $groupName,
                'type' => 'decimal',
                'backend' => Price::class,
                'frontend' => '',
                'sort_order' => 140,
                'label' => 'Min Value',
                'input' => 'price',
                'frontend_class' => 'validate-greater-than-zero',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_OPEN_MAX_AMOUNT,
            [
                'group' => $groupName,
                'type' => 'decimal',
                'backend' => Price::class,
                'frontend' => '',
                'sort_order' => 150,
                'label' => 'Max Value',
                'input' => 'price',
                'frontend_class' => 'validate-greater-than-zero',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_PERCENTAGE_TYPE,
            [
                'group' => $groupName,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 160,
                'label' => 'Percentage Price',
                'input' => 'select',
                'class' => '',
                'source' => PriceAttribute::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_PERCENTAGE_VALUE,
            [
                'group' => $groupName,
                'type' => 'int',
                'backend' => Price::class,
                'frontend' => '',
                'sort_order' => 170,
                'label' => 'Value',
                'input' => 'text',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 100,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_TEMPLATE,
            [
                'group' => $groupName,
                'type' => 'text',
                'frontend' => '',
                'sort_order' => 180,
                'label' => 'Template',
                'input' => 'multiselect',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'source' => Template::class,
                'backend' => TemplateBackend::class,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_CODE_PATTERN,
            [
                'group' => $groupName,
                'type' => 'int',
                'frontend' => '',
                'sort_order' => 190,
                'label' => 'Gift Code Pattern',
                'input' => 'select',
                'class' => '',
                'source' => Pattern::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_MESSAGE,
            [
                'group' => $groupName,
                'type' => 'int',
                'frontend' => '',
                'sort_order' => 200,
                'label' => 'Message',
                'input' => 'boolean',
                'class' => '',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            GiftCard::BSS_GIFT_CARD_EXPIRES,
            [
                'group' => $groupName,
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'sort_order' => 210,
                'label' => 'Expires After (days)',
                'input' => 'text',
                'frontend_class' => 'validate-zero-or-greater validate-number',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => GiftCard::BSS_GIFT_CARD
            ]
        );
    }
}

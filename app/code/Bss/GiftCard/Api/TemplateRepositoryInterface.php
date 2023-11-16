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

namespace Bss\GiftCard\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface TemplateRepositoryInterface
 *
 * Bss\GiftCard\Api
 */
interface TemplateRepositoryInterface
{
    /**
     * Get template by id
     *
     * @param int $templateId
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTemplateById($templateId);

    /**
     * Get list templates
     *
     * @param SearchCriteriaInterface $criteria
     *
     * @return Data\TemplateSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);
}

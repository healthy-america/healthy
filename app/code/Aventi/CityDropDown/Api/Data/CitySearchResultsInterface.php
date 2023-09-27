<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Api\Data;

interface CitySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get City list.
     * @return \Aventi\CityDropDown\Api\Data\CityInterface[]
     */
    public function getItems(): array;

    /**
     * Set name list.
     * @param \Aventi\CityDropDown\Api\Data\CityInterface[] $items
     * @return $this
     */
    public function setItems(array $items): CitySearchResultsInterface;
}


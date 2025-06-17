<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const DESCRIPTION = 'description';
    const NAME = 'name';
    const ENTITY_ID = 'entity_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityID
     * @return PriceListInterface
     */
    public function setEntityId($entityID);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return PriceListInterface
     */
    public function setName($name);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return PriceListInterface
     */
    public function setDescription($description);
}

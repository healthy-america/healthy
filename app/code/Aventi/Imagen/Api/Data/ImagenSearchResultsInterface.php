<?php declare(strict_types=1);


namespace Aventi\Imagen\Api\Data;


interface ImagenSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Imagen list.
     * @return \Aventi\Imagen\Api\Data\ImagenInterface[]
     */
    public function getItems();

    /**
     * Set image list.
     * @param \Aventi\Imagen\Api\Data\ImagenInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}


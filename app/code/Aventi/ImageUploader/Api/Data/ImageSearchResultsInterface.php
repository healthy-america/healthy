<?php declare(strict_types=1);


namespace Aventi\ImageUploader\Api\Data;


interface ImageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Imagen list.
     * @return \Aventi\ImageUploader\Api\Data\ImageInterface[]
     */
    public function getItems();

    /**
     * Set image list.
     * @param \Aventi\ImageUploader\Api\Data\ImageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}


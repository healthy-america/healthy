<?php declare(strict_types=1);


namespace Aventi\ImageUploader\Api;

use Magento\Framework\Api\SearchCriteriaInterface;


interface ImageRepositoryInterface
{

    /**
     * Save Imagen
     * @param \Aventi\ImageUploader\Api\Data\ImageInterface $imagen
     * @return \Aventi\ImageUploader\Api\Data\ImageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Aventi\ImageUploader\Api\Data\ImageInterface $imagen
    );

    /**
     * Retrieve Imagen
     * @param string $imagenId
     * @return \Aventi\ImageUploader\Api\Data\ImageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($imagenId);

    /**
     * Retrieve Imagen matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aventi\ImageUploader\Api\Data\ImageSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Imagen
     * @param \Aventi\ImageUploader\Api\Data\ImageInterface $imagen
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Aventi\ImageUploader\Api\Data\ImageInterface $imagen
    );

    /**
     * Delete Imagen by ID
     * @param string $imagenId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($imagenId);
}


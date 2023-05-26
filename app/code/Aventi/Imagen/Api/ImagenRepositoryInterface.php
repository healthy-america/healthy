<?php declare(strict_types=1);


namespace Aventi\Imagen\Api;


interface ImagenRepositoryInterface
{

    /**
     * Save Imagen
     * @param \Aventi\Imagen\Api\Data\ImagenInterface $imagen
     * @return \Aventi\Imagen\Api\Data\ImagenInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        Data\ImagenInterface $imagen
    );

    /**
     * Retrieve Imagen
     * @param string $imagenId
     * @return \Aventi\Imagen\Api\Data\ImagenInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($imagenId);

    /**
     * Retrieve Imagen matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aventi\Imagen\Api\Data\ImagenSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Imagen
     * @param \Aventi\Imagen\Api\Data\ImagenInterface $imagen
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        Data\ImagenInterface $imagen
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


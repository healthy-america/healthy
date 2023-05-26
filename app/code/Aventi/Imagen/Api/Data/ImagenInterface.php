<?php declare(strict_types=1);


namespace Aventi\Imagen\Api\Data;


interface ImagenInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const IMAGE = 'image';
    const IMAGEN_ID = 'imagen_id';

    /**
     * Get imagen_id
     * @return string|null
     */
    public function getImagenId();

    /**
     * Set imagen_id
     * @param string $imagenId
     * @return \Aventi\Imagen\Api\Data\ImagenInterface
     */
    public function setImagenId($imagenId);

    /**
     * Get image
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     * @param string $image
     * @return \Aventi\Imagen\Api\Data\ImagenInterface
     */
    public function setImage($image);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Aventi\Imagen\Api\Data\ImagenExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Aventi\Imagen\Api\Data\ImagenExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aventi\Imagen\Api\Data\ImagenExtensionInterface $extensionAttributes
    );
}


<?php declare(strict_types=1);


namespace Aventi\Imagen\Model\Data;

use Aventi\Imagen\Api\Data\ImagenInterface;


class Imagen extends \Magento\Framework\Api\AbstractExtensibleObject implements ImagenInterface
{

    /**
     * Get imagen_id
     * @return string|null
     */
    public function getImagenId()
    {
        return $this->_get(self::IMAGEN_ID);
    }

    /**
     * Set imagen_id
     * @param string $imagenId
     * @return \Aventi\Imagen\Api\Data\ImagenInterface
     */
    public function setImagenId($imagenId)
    {
        return $this->setData(self::IMAGEN_ID, $imagenId);
    }

    /**
     * Get image
     * @return string|null
     */
    public function getImage()
    {
        return $this->_get(self::IMAGE);
    }

    /**
     * Set image
     * @param string $image
     * @return \Aventi\Imagen\Api\Data\ImagenInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Aventi\Imagen\Api\Data\ImagenExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Aventi\Imagen\Api\Data\ImagenExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aventi\Imagen\Api\Data\ImagenExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}


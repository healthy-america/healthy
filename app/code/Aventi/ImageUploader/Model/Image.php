<?php

namespace Aventi\ImageUploader\Model;

use Aventi\ImageUploader\Api\Data\ImageInterface;
use Aventi\ImageUploader\Model\ResourceModel\Image as ResourceModelImage;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Image extends AbstractModel implements ImageInterface, IdentityInterface
{
    const CACHE_TAG = 'aventi_images';

    public function getIdentities()
    {
        return [
            self::CACHE_TAG . '_' . $this->getId(),
        ];
    }

    protected function _construct()
    {
        $this->_init(ResourceModelImage::class);
    }

    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    public function setPath($value)
    {
        return $this->setData(self::PATH, $value);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function setSku($value)
    {
        return $this->setData(self::SKU, $value);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function setPosition($value)
    {
        return $this->setData(self::POSITION, $value);
    }

    public function getDetails()
    {
        return $this->getData(self::DETAILS);
    }

    public function setDetails($value)
    {
        return $this->setData(self::DETAILS, $value);
    }
}

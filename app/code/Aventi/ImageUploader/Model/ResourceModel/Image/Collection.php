<?php

namespace Aventi\ImageUploader\Model\ResourceModel\Image;

use Aventi\ImageUploader\Model\Image;
use Aventi\ImageUploader\Model\ResourceModel\Image as ResourceModelImage;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Image::class, ResourceModelImage::class);
    }
}

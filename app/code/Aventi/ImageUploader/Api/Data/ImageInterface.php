<?php

namespace Aventi\ImageUploader\Api\Data;

interface ImageInterface {
    const ID = 'image_id';
    const PATH = 'path';
    const SKU = 'sku';
    const POSITION = 'pos_img';
    const DETAILS = 'details';

    public function getPath ();

    public function setPath ($value);
}

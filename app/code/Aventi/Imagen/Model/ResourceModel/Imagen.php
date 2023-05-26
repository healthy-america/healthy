<?php declare(strict_types=1);


namespace Aventi\Imagen\Model\ResourceModel;


class Imagen extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('aventi_imagen_imagen', 'imagen_id');
    }
}


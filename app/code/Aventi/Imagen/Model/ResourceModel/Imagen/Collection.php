<?php declare(strict_types=1);


namespace Aventi\Imagen\Model\ResourceModel\Imagen;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'imagen_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aventi\Imagen\Model\Imagen::class,
            \Aventi\Imagen\Model\ResourceModel\Imagen::class
        );
    }
}


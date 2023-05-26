<?php declare(strict_types=1);

namespace Aventi\Imagen\Model;

use Aventi\Imagen\Api\Data\ImagenInterface;
use Aventi\Imagen\Api\Data\ImagenInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Imagen extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var ImagenInterfaceFactory
     */
    protected $imagenDataFactory;
    /**
     * @var DataObjectHelper
     */

    protected $dataObjectHelper;

    protected $_eventPrefix = 'aventi_imagen_imagen';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ImagenInterfaceFactory $imagenDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Aventi\Imagen\Model\ResourceModel\Imagen $resource
     * @param \Aventi\Imagen\Model\ResourceModel\Imagen\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ImagenInterfaceFactory $imagenDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\Imagen $resource,
        ResourceModel\Imagen\Collection $resourceCollection,
        array $data = []
    ) {
        $this->imagenDataFactory = $imagenDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve imagen model with imagen data
     * @return ImagenInterface
     */
    public function getDataModel()
    {
        $imagenData = $this->getData();

        $imagenDataObject = $this->imagenDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $imagenDataObject,
            $imagenData,
            ImagenInterface::class
        );

        return $imagenDataObject;
    }
}

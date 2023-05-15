<?php

namespace Aventi\Imagen\Model;

use Aventi\Imagen\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessImage
{
    /**
     * @var \Aventi\Imagen\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Aventi\Imagen\Model\MediaGalery
     */
    private $mediaGallery;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output = null;

    /**
     * @var RenameImages
     */
    private $renameImages;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * @param Data $helperData
     * @param ProductRepositoryInterface $productRepository
     * @param MediaGalery $mediaGallery
     * @param RenameImages $renameImages
     * @param Csv $csv
     */
    public function __construct(
        \Aventi\Imagen\Helper\Data $helperData,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Aventi\Imagen\Model\MediaGalery $mediaGallery,
        \Aventi\Imagen\Model\RenameImages $renameImages,
        \Aventi\Imagen\Model\Csv $csv
    ) {
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
        $this->mediaGallery = $mediaGallery;
        $this->renameImages = $renameImages;
        $this->csv = $csv;
    }

    /**
     * @return null
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @return void
     */
    public function process(): array
    {
        $resume = [
            'total' => 0,
            'completed' => 0 ,
            'noFound' => 0 ,
            'NoProcessing' => 0
        ];

        try {
            $this->renameImages->process();
        } catch (\Exception $e) {
            // error
        }

        $pathImages = $this->helperData->getPathImage();

        $this->writeIn(__('Path origin the data ') . $pathImages);

        if (empty($pathImages) || !$this->existsFolder($pathImages)) {
            $this->writeIn(__('Path does not exist and there are insufficient permissions to create it.'));
            return [];
        }

        $nameFiles = $this->getNameFiles($pathImages);

        $total =  count($nameFiles);
        $resume['total'] = $total;
        $this->writeIn(__('Total images: ') . $total);

        $csvName = 'info_image_' . date('Y-m-d') . '.csv';
        $this->csv->create($csvName, ['Sku', 'Image', 'Index', 'Process', 'Message']);

        for ($i = 0; $i < $total; $i++) {
            $this->writeIn('Process image: ' . ($i + 1));
            $nameFile = $nameFiles[$i];
            $pathImage = $pathImages . $nameFile;

            $dataValidate = $this->validateImage($pathImage, $nameFile);

            if (!$dataValidate['isValid']) {
                $message = 'Image invalid: ' . $nameFile . ' Error: ' . $dataValidate['message'];
                $this->csv->addRow($csvName, ['', $nameFile, 0, 0, $message]);
                $this->writeIn($message);
                continue;
            }

            $sku = $dataValidate['sku'];
            $index = $dataValidate['index'];

            try {
                $product = $this->productRepository->get($sku);

                $type = ($index == 1 || $index == '1') ? ['image', 'small_image', 'thumbnail'] : [];
                $save = $this->mediaGallery->processMediaGalleryEntry(
                    $product,
                    $pathImage,
                    $index,
                    $product->getId() . '_' . $index,
                    $type
                );
                if ($index == 1 || $index == '1') {
                    $this->mediaGallery->setTypes($sku, $type);
                }
                if ($save) {
                    $resume['completed'] += 1;
                    $isConmplete = 1;
                } else {
                    $resume['NoProcessing'] += 1;
                    $isConmplete = 0;
                }
                $this->csv->addRow($csvName, [$sku, $nameFile, $index, $isConmplete, '']);
            } catch (\Exception $e) {
                $this->writeIn("Error: " . $e->getMessage());
                $resume['NoProcessing'] += 1;
                $this->csv->addRow($csvName, [$sku, $nameFile, $index, 0, $e->getMessage()]);
            }
        }

        $this->resumen(array_values($resume));
        return $resume;
    }

    /**
     * @param string $path
     * @param string $nameFile
     * @return void
     */
    private function validateImage(string $path, string $nameFile): array
    {
        $result = [
            'message' => '',
            'sku' => '',
            'index' => 1,
            'isValid' => true
        ];

        $sizeMb = 2;
        $sizeMb = ($sizeMb*1024000);

        $pathInfo = pathinfo($path);

        if (!preg_match("/[\w]_[0-9][.]/", $nameFile)) {
            $result['isValid']  =  false;
            $result['message']  =  "La imagen: " . $nameFile . ", No tiene el formato requerido en el modulo 'SKU_1.jpg'";
            return $result;
        }

        if (!in_array($pathInfo['extension'], ['jpg', 'png', 'gif'])) {
            $result['isValid']  =  false;
            $result['message']  =  "Error: extension";
            return $result;
        }

        $sizeImg = $this->mediaGallery->getFileSize($path);

        $this->writeIn($nameFile . '     ' . $sizeImg);
        if ($sizeImg > $sizeMb) {
            $result['isValid']  =  false;
            $result['message']  =  "La imagen: " . $nameFile . ", Supera el tamaño permitido, Permitido: " . ($sizeMb/1024000) . " Mb, Tamaño que viene: " . round(($sizeImg/1024000), 2) . " Mb ";
            return $result;
        }

        $index = explode("_", $nameFile);
        $index = $this->deleteEmptyPositions($index);
        $sku = $index[0];
        $index = explode(".", $index[1]);
        $index = intval($index[0]);
        if ($index < 1) {
            $result['isValid']  =  false;
            $result['message']  =  "La imagen: " . $nameFile . ", No tiene el formato requerido despues del Guion bajo '_' 'SKU_1.jpg'";
            return $result;
        }
        $result['sku'] = $sku;
        $result['index'] = $index;

        return $result;
    }

    /**
     * @param string $path
     * @return array
     */
    private function getNameFiles(string $path): array
    {
        $names = [];

        $objects = $this->_files("key", $path, "files");

        foreach ($objects["objects"] as $obj) {
            $names[] = $obj["key"];
        }

        return $names;
    }

    /**
     * @param string $key
     * @param string $dir
     * @param string $reading
     * @return array
     */
    private function _files(string $key, string $dir, string $reading = "all") : array
    {
        $count = 0;
        $objects = [];
        if (is_dir($dir)) {
            $objs = array_diff(scandir($dir), [ '.', '..' ]);
            $readDir = true;
            asort($objs);
            foreach ($objs as $ley => $obj) {
                $object = $dir . DIRECTORY_SEPARATOR . $obj;
                if ("all" == $reading) {
                    $readDir = true;
                } elseif ("files" == $reading) {
                    $readDir = !is_dir($object);
                } elseif ("directories" == $reading) {
                    $readDir = is_dir($object);
                }
                if ($readDir &&  file_exists($object)) {
                    $objects[$ley][$key] = $obj;
                    $count++;
                }
            }
        }
        return [ "count" => $count, "objects" => $objects ];
    }

    /**
     * Create folder if not exists
     *
     * @param string $path
     * @return bool
     */
    private function existsFolder(string $path): bool
    {
        $result = true;
        try {
            if (!is_dir($path)) {
                mkdir($path);
            }
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * print data
     *
     * @author Carlos Hernan Aguilar Hurado <caguilar@aventi.co>
     * @date 28/04/20
     * @param $message
     */
    public function writeIn($message)
    {
        $_output = $this->getOutput();
        if ($_output) {
            $_output->writeln($message);
        }
    }

    /**
     * @param array $array
     * @return array
     */
    private function deleteEmptyPositions(array $array): array
    {
        $new = [];
        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i] && trim($array[$i]) != "" && strlen($array[$i]) > 0) {
                $new[] = $array[$i];
            }
        }
        return $new;
    }

    /**
     * Print the resume
     *
     * @author Carlos Hernan Aguilar Hurado <caguilar@aventi.co>
     * @date 28/04/20
     * @param array $data
     */
    private function resumen($data = [])
    {
        $_output = $this->getOutput();
        if ($_output) {
            $table = new Table($_output);
            $table
                ->setHeaders(['Total', 'Complete', 'Product no found', 'No processing'])
                ->setRows([$data]);
            $table->render();
        }
    }
}

<?php

namespace Aventi\Imagen\Model;

use SplFileObject;

class Csv
{
    /**
     * @param $name
     * @param $heads
     * @return void
     */
    public function create($name, $heads)
    {
        try {
            $path = $this->getPathMedia() . $name;
            $file = new SplFileObject($path, 'a');
            $file->fputcsv($heads);
            $file = null;
        } catch (\Exception $e) {
            //
        }
    }

    /**
     * @param $name
     * @param $row
     * @return void
     */
    public function addRow($name, $row)
    {
        try {
            $path = $this->getPathMedia() . $name;
            $file = new SplFileObject($path, 'a');
            $file->fputcsv($row);
            $file = null;
        } catch (\Exception $e) {
            //
        }
    }

    /**
     * @return string
     */
    public function getPathMedia(): string
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $fileSystem = $objectManager->create(\Magento\Framework\Filesystem::class);
        return $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
    }
}

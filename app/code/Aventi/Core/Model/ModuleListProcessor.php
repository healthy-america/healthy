<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */

declare(strict_types=1);

namespace Aventi\Core\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Driver\File;

class ModuleListProcessor
{
    protected $moduleDataStorage = [];
    /**
     * @var ModuleListInterface
     */
    private $moduleList;
    /**
     * @var Reader
     */
    private $moduleReader;
    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ModuleListInterface $moduleList,
        File $filesystem,
        Reader $moduleReader,
        Serializer $serializer
    ) {
        $this->moduleList = $moduleList;
        $this->filesystem = $filesystem;
        $this->moduleReader = $moduleReader;
        $this->serializer = $serializer;
    }

    /**
     * @return array
     */
    public function getModules()
    {
        $modules = $this->moduleList->getNames();


        foreach ($modules as $moduleName) {
            if ($moduleName === 'Aventi_Base'|| strpos($moduleName, 'Aventi_') === false) {
                continue;
            }

            try {
                if (!is_array($module = $this->getModuleInfo($moduleName))) {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }
            $this->modules[] = $module;
            /*if (empty($module['hasUpdate'])) {
                $this->modules['lastVersion'][] = $module;
            } else {
                $this->modules['hasUpdate'][] = $module;
            }*/
        }

        return $this->modules;
    }

    /**
     * @param string $moduleCode
     * @return array|mixed|string
     */
    protected function getModuleInfo($moduleCode)
    {


        try {
            $dir = $this->moduleReader->getModuleDir('', $moduleCode);
            $file = $dir . '/composer.json';
            $string = $this->filesystem->fileGetContents($file);
            $this->moduleDataStorage[$moduleCode] = $this->serializer->unserialize($string);
        } catch (FileSystemException $e) {
            $this->moduleDataStorage[$moduleCode] = [];
        }

        $module =  $this->moduleDataStorage[$moduleCode];

        if (!is_array($module) || !isset($module['version']) || !isset($module['description'])
        ) {
            return '';
        }

        $module['description'] = $this->replaceMagentoText($module['description']);



        return $module;
    }

    /**
     * @param string $moduleName
     *
     * @return string
     */
    protected function replaceMagentoText(string $moduleName): string
    {
        return str_replace(['for Magento 2', 'by Amasty'], '', $moduleName);
    }
}

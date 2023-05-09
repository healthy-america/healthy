<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model;

use Aventi\SAP\Helper\Attribute;
use Aventi\SAP\Logger\Logger;
use Bcn\Component\Json\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Integration
{
    private array $arrayOption = [];

    /**
     * @var Attribute
     */
    protected Attribute $attributeDate;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var OutputInterface|null
     */
    private ?OutputInterface $output = null;

    /**
     * @var DriverInterface
     */
    protected DriverInterface $driver;

    /**
     * @var false|resource
     */
    private $file;

    /**
     * @var Filesystem
     */
    private Filesystem $fileSystem;

    /**
     * @param Attribute $attributeDate
     * @param Logger $logger
     * @param DriverInterface $driver
     * @param Filesystem $filesystem
     */
    public function __construct(
        Attribute $attributeDate,
        Logger $logger,
        DriverInterface $driver,
        Filesystem $filesystem
    ) {
        $this->attributeDate = $attributeDate;
        $this->logger = $logger;
        $this->driver = $driver;
        $this->fileSystem = $filesystem;
    }

    /**
     * @return OutputInterface|null
     */
    private function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Opens a resource file and returns its instance.
     * @param $filePath
     * @return Reader
     * @throws FileSystemException
     */
    protected function getJsonReader($filePath)
    {
        try {
            if ($this->driver->isExists($filePath)) {
                $this->file = $this->driver->fileOpen($filePath, 'r');
                return new Reader($this->file);
            }
        } catch (FileSystemException $e) {
            throw new FileSystemException(__('An error has occurred: ' . $e->getMessage()));
        }
        return false;
    }

    /**
     * Close a resource file and deletes it.
     * @param $file
     * @throws FileSystemException
     */
    protected function closeFile($file)
    {
        try {
            $this->driver->fileClose($this->file);
            $this->driver->deleteFile($file);
        } catch (FileSystemException $e) {
            throw new FileSystemException(__('An error has occurred: ' . $e->getMessage()));
        }
    }

    /**
     * Creates a ProgressBar instance and returns it.
     * @param $total
     * @return null|ProgressBar
     */
    protected function startProgressBar($total): ?ProgressBar
    {
        $out = $this->getOutput();
        if ($out) {
            $progressBar = new ProgressBar($out, $total);
            $progressBar->start();
            return $progressBar;
        }
        return null;
    }

    /**
     * @param $progressBar
     */
    protected function advanceProgressBar($progressBar)
    {
        if ($this->getOutput()) {
            $progressBar->advance();
        }
    }

    /**
     * Finish the progressBar.
     * @param $progressBar
     * @param $start
     * @param $rows
     */
    protected function finishProgressBar($progressBar, $start, $rows)
    {
        $out = $this->getOutput();
        if ($out) {
            $progressBar->finish();
            $out->writeln(sprintf("\nInteraction %s", ($start / $rows)));
        }
    }

    /**
     * Print Table with synchro results.
     * @param $response
     */
    public function printTable($response)
    {
        $out = $this->getOutput();
        if ($out) {
            $out->writeln("\n");
            $table = new Table($out);
            $table->setRows([
                ['Data New', $response['new']],
                ['Data Updated', $response['updated']],
                ['Data Check', $response['check']]
            ]);
            $table->render();
        }
    }

    public function printOrderTable($response)
    {
        $out = $this->getOutput();
        if ($out) {
            $out->writeln("\n");
            $table = new Table($out);
            $table->setHeaders($response['headers']);
            $table->setRows([$response['rows']]);
            $table->render();
        }
    }

    /**
     * Get or create the option by attributes and returns id.
     *
     * @param string $label
     * @param string $attributeCode
     * @return false|int|mixed
     */
    public function getOptionId(string $label = '', string $attributeCode = '')
    {
        try {
            if (!empty($label)) {
                $brand = str_replace(' ', '', $label);
                $optionId = 0;
                if (!array_key_exists($brand, $this->arrayOption)) {
                    $optionId = $this->attributeDate->getOptionId($attributeCode, $label);
                    if (!$optionId) {
                        $optionId = $this->attributeDate->createOrGetId($attributeCode, $label);
                    }
                    $this->arrayOption[$brand] = $optionId;
                } else {
                    $optionId = $this->arrayOption[$brand];
                }
                return $optionId;
            }
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    /**
     * Returns path file.
     *
     * @param $jsonData
     * @param $typeUri
     * @return false|string
     */
    protected function getJsonPath($jsonData, $typeUri)
    {
        $fileDir = $this->fileSystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $fileJson = $fileDir . sprintf('sap_%s_%s.json', $typeUri, date('YmdHis'));
        try {
            if (!$this->driver->isExists($fileJson)) {
                $this->driver->filePutContents($fileJson, $jsonData);
                return $fileJson;
            }
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    /**
     * @param $number
     * @return string
     */
    public function formatDecimalNumber($number)
    {
        return number_format($number, 6, '.', '');
    }

    /**
     * Main procedure
     *
     * @param array|null $data
     * @return void
     */
    abstract public function process(array $data = null): void;
}

<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */

namespace Aventi\ImageUploader\Cron;

use Aventi\ImageUploader\Model\Catalog\Product\ProcessMedia;
use Bcn\Component\Json\Exception\ReadingError;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class ProcessCatalog
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProcessMedia
     */
    private $processMedia;

    /**
     * Constructor
     * @param LoggerInterface $logger
     * @param ProcessMedia $processMedia
     */
    public function __construct(
        LoggerInterface $logger,
        ProcessMedia $processMedia
    ) {
        $this->logger = $logger;
        $this->processMedia = $processMedia;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws ReadingError
     * @throws FileSystemException
     */
    public function execute()
    {
        $this->logger->info("Process Product Images cron was executed.");
        $this->processMedia->processAllProducts();
    }
}

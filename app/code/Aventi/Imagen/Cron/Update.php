<?php declare(strict_types=1);

namespace Aventi\Imagen\Cron;

class Update
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Aventi\Imagen\Model\Process
     */
    private $process;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aventi\Imagen\Model\Process $process
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Aventi\Imagen\Model\Process $process
    ) {
        $this->logger = $logger;
        $this->process = $process;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->logger->addInfo("Cronjob Update is executed.");
        $this->process->process();
    }
}

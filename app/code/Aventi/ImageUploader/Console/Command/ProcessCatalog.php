<?php
namespace Aventi\ImageUploader\Console\Command;

use Aventi\ImageUploader\Model\Catalog\Product\ProcessMedia;
use Bcn\Component\Json\Exception\ReadingError;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessCatalog extends Command
{

    /**
     * @var ProcessMedia
     */
    private $processMedia;

    /**
     * @var State
     */
    private $state;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param ProcessMedia $processMedia
     * @param State $state
     * @param DateTime $dateTime
     */
    public function __construct(
        ProcessMedia $processMedia,
        State            $state,
        DateTime         $dateTime
    ) {
        parent::__construct();
        $this->processMedia = $processMedia;
        $this->state = $state;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws InputException
     * @throws FileSystemException
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(Area::AREA_CRONTAB);
        $this->processMedia->setOutput($output);
        $rst = $this->processMedia->processAllProducts();

        if ($output) {
            $table = new Table($output);
            $table->setRows([
                ['Total products procesed', count($rst)]
            ]);
            $table->render();
            $output->writeln("\nNow your catalog is procesed");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("aventi:images:processCatalog");
        $this->setDescription("rename all product images to aventi standard for Magento");
        parent::configure();
    }
}

<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Console\Command;

use Bcn\Component\Json\Exception\ReadingError;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Sincronice extends Command
{

    const NAME_ARGUMENT = "type";
    const DATA_OPTION = "data";
    const DATA_TYPE = "type";

    /**
     * @var \Aventi\SAP\Helper\Console
     */
    private $console;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    public function __construct(
        \Aventi\SAP\Helper\Console $console,
        \Magento\Framework\App\State $state,
        string $name = null
    ) {
        parent::__construct($name);
        $this->console = $console;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_CRONTAB);
        $option = $input->getOption(self::DATA_OPTION);
        $option2 = $input->getOption(self::DATA_TYPE);
        $msg = "";
        if ($option2) {
            $msg = " | Type: " . $option2;
        }
        $output->writeln("Data to synchronize: " . $option . $msg);

        $this->console->execute($option, $output, $option2);
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("aventi:sap:sincronice");
        $this->setDescription("Synchronize data from SAP to Magento. Option determines the data to sincronice.");
        $this->setDefinition([
            //new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Type"),
            new InputOption(
                self::DATA_OPTION,
                "-d",
                InputOption::VALUE_REQUIRED,
                "The data to synchronize; Options allowed are: 'brand', 'category', 'order', 'product', 'price' or 'stock'."
            ),
            new InputOption(
                self::DATA_TYPE,
                "-o",
                InputOption::VALUE_OPTIONAL,
                "Order type process to sincronice; Options allowed are: 'new', 'error'."
            )
        ]);
        parent::configure();
    }
}

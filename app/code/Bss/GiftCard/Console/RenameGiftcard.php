<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Console;

use Bss\GiftCard\Model\ConvertFileName;
use Magento\Framework\Exception\FileSystemException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RenameGiftcard extends Command
{
    /**
     * @var ConvertFileName
     */
    protected $convertFileGiftcard;

    /**
     * @param ConvertFileName $convertGiftcard
     */
    public function __construct(
        ConvertFileName $convertGiftcard
    ) {
        parent::__construct();
        $this->convertFileGiftcard = $convertGiftcard;
    }

    /**
     * Configure function
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bss_g_c:convert');
        $this->setDescription('Convert file Giftcard');
        parent::configure();
    }

    /**
     * Execute function
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->convertFileGiftcard->convertFileFollowVersion();
        if (!empty($message)) {
            $output->writeln($message);
        }
    }
}

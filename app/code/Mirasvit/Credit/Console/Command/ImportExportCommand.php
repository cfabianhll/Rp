<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Console\Command;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Credit\Model\BalanceFactory;
use Mirasvit\Credit\Model\ResourceModel\Balance\CollectionFactory as BalanceCollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportExportCommand extends Command
{
    /**
     * @var BalanceCollectionFactory
     */
    private $balanceCollectionFactory;

    /**
     * @var BalanceFactory
     */
    private $balanceFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var State
     */
    private $state;

    /**
     * ImportExportCommand constructor.
     * @param BalanceCollectionFactory $balanceCollectionFactory
     * @param BalanceFactory $balanceFactory
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param State $state
     */
    public function __construct(
        BalanceCollectionFactory $balanceCollectionFactory,
        BalanceFactory $balanceFactory,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        State $state
    ) {
        $this->balanceCollectionFactory = $balanceCollectionFactory;
        $this->balanceFactory           = $balanceFactory;
        $this->customerFactory          = $customerFactory;
        $this->storeManager             = $storeManager;
        $this->state                    = $state;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:credit:import-export')
            ->setDescription('Import / Export Store Credit Balances');

        $this->addOption('export', null, InputOption::VALUE_NONE, 'Export balances');
        $this->addOption('import', null, InputOption::VALUE_NONE, 'Import balances');
        $this->addOption('file', null, InputOption::VALUE_REQUIRED, 'Path to file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        $file = $input->getOption('file');

        if ($input->getOption('export')) {
            $file = $file ? $file : 'export.csv';

            $collection = $this->balanceCollectionFactory->create();

            $data = [];

            /** @var \Mirasvit\Credit\Model\Balance $balance */
            foreach ($collection as $balance) {
                if (!$balance->getCustomer()) {
                    continue;
                }

                $data[] = [
                    $balance->getCustomer()->getEmail(),
                    $balance->getAmount(),
                    $balance->getCurrencyCode(),
                ];
            }

            $csv = new Csv(new File());
            $csv->appendData($file, $data);

            $output->writeln("Balances were exported to file '$file'.");
        } elseif ($input->getOption('import')) {
            $storeId = $this->storeManager->getDefaultStoreView()->getId();

            $csv  = new Csv(new File());
            $rows = $csv->getData($file);

            foreach ($rows as $idx => $row) {
                if (count($row) < 2) {
                    $output->writeln("<error>Skip row #$idx</error>");
                    continue;
                }

                $email    = $row[0];
                $amount   = floatval($row[1]);
                $currency = isset($row[2]) ? $row[2] : $this->storeManager->getStore()->getBaseCurrencyCode();

                $customer = $this->customerFactory->create();
                $customer->setWebsiteId($storeId)
                    ->loadByEmail($email);

                if (!$customer->getId()) {
                    $output->writeln("<error>Skip row #$idx. Can't find customer with email '$email'.</error>");
                    continue;
                }

                $balance = $this->balanceFactory->create()->loadByCustomer($customer, $currency);

                $balance->setAmount(0);
                $balance->addTransaction($amount, $amount);
            }

        } else {
            $help = new HelpCommand();
            $help->setCommand($this);

            return $help->run($input, $output);
        }
    }
}
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
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\OptimizeImage\Console\Command;

use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\WebpService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebpCommand extends Command
{
    private $fileListSynchronizationService;

    private $webpService;

    private $fileRepository;

    public function __construct(
        FileListSynchronizationService $fileListSynchronizationService,
        WebpService $webpService,
        FileRepository $fileRepository
    ) {
        $this->fileListSynchronizationService = $fileListSynchronizationService;
        $this->webpService                    = $webpService;
        $this->fileRepository                 = $fileRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:optimize-image:webp')
            ->setDescription('Create a copy of images in .webp format');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fileListSynchronizationService->synchronize(10000);

        $collection = $this->fileRepository->getCollection();

        $bar = new ProgressBar($output, $collection->getSize());

        foreach ($collection as $file) {
            try {
                $this->webpService->process($file);
                $this->fileRepository->save($file);
            } catch (\Exception $e) {
                $output->writeln('');
                $output->writeln($e->getMessage());
            }

            $bar->advance();
        }

        $bar->clear();
    }
}

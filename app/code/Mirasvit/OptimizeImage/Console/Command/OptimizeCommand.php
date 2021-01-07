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
use Mirasvit\OptimizeImage\Service\FileListBatchService;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\FormatService;
use Mirasvit\OptimizeImage\Service\OptimizeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    private $fileListSynchronizationService;

    private $fileRepository;

    private $fileListBatchService;

    private $optimizeService;

    private $formatService;

    public function __construct(
        FileListSynchronizationService $fileListSynchronizationService,
        FileRepository $fileRepository,
        FileListBatchService $fileListBatchService,
        OptimizeService $optimizeService,
        FormatService $formatService
    ) {
        $this->fileListSynchronizationService = $fileListSynchronizationService;
        $this->fileRepository                 = $fileRepository;
        $this->fileListBatchService           = $fileListBatchService;
        $this->optimizeService                = $optimizeService;
        $this->formatService                  = $formatService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:optimize-image:optimize')
            ->setDescription('Run images optimization process');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->fileListSynchronizationService->synchronize(10000);

        $size = $this->fileListBatchService->getSize();

        $bar = new ProgressBar($output, $size);
        $bar->setFormat('%current%(%updated%)/%max% [%bar%] %percent%% <info>%message%</info> %etc%');

        $bar->setMessage('', 'message');
        $bar->setMessage(0, 'updated');
        $bar->setMessage('', 'etc');

        $originalSize = 0;
        $actualSize   = 0;
        $updatedFiles = 0;

        while ($batch = $this->fileListBatchService->getBatch()) {
            foreach ($batch as $file) {
                try {
                    $this->optimizeService->optimize($file);

                    $originalSize += $file->getOriginalSize();
                    $actualSize   += $file->getActualSize();

                    if ($file->getOriginalSize() != $file->getActualSize()) {
                        $updatedFiles++;
                    }

                    $this->fileRepository->save($file);
                } catch (\Exception $e) {
                    $this->fileRepository->delete($file);

                    $output->writeln('');
                    $output->writeln($e->getMessage());
                }

                $bar->advance();

                $message = [
                    sprintf('Saved: %s', $this->formatService->formatBytes($originalSize - $actualSize)),
                    sprintf('Processed: %s', $this->formatService->formatBytes($originalSize)),
                ];
                $bar->setMessage(implode(' ', $message), 'message');
                $bar->setMessage($updatedFiles, 'updated');

                $savedSize = $file->getOriginalSize() - $file->getActualSize();
                $bar->setMessage($file->getBasename() . ' ' . $this->formatService->formatBytes($savedSize), 'etc');
            }
        }

        $bar->clear();

        $output->writeln(sprintf("Original size: %s", $this->formatService->formatBytes($originalSize)));
        $output->writeln(sprintf("Actual size: %s", $this->formatService->formatBytes($actualSize)));
        $output->writeln(sprintf("Saved size: %s", $this->formatService->formatBytes($originalSize - $actualSize)));
    }
}

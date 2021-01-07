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



namespace Mirasvit\OptimizeImage\Cron;

use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\FileListBatchService;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\OptimizeService;

class OptimizeCron
{
    private $fileRepository;

    private $fileListBatchService;

    private $fileListSynchronizationService;

    private $optimizeService;

    public function __construct(
        FileRepository $fileRepository,
        FileListBatchService $fileListBatchService,
        FileListSynchronizationService $fileListSynchronizationService,
        OptimizeService $optimizeService
    ) {
        $this->fileRepository                 = $fileRepository;
        $this->fileListBatchService           = $fileListBatchService;
        $this->fileListSynchronizationService = $fileListSynchronizationService;
        $this->optimizeService                = $optimizeService;
    }

    public function execute()
    {
        $this->fileListSynchronizationService->synchronize(1000);

        $batch = $this->fileListBatchService->getBatch();

        if ($batch) {
            foreach ($batch as $file) {
                try {
                    $this->optimizeService->optimize($file);

                    $this->fileRepository->save($file);
                } catch (\Exception $e) {
                    $this->fileRepository->delete($file);
                }
            }
        }
    }
}

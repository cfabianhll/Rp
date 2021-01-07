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

use Mirasvit\OptimizeImage\Model\Config;
use Mirasvit\OptimizeImage\Repository\FileRepository;
use Mirasvit\OptimizeImage\Service\FileListBatchService;
use Mirasvit\OptimizeImage\Service\FileListSynchronizationService;
use Mirasvit\OptimizeImage\Service\WebpService;

class WebpCron
{
    private $fileRepository;

    private $fileListSynchronizationService;

    private $webpService;
    
    private $config;

    public function __construct(
        FileRepository $fileRepository,
        FileListSynchronizationService $fileListSynchronizationService,
        WebpService $webpService,
        Config $config
    ) {
        $this->fileRepository                 = $fileRepository;
        $this->fileListSynchronizationService = $fileListSynchronizationService;
        $this->webpService                    = $webpService;
        $this->config                         = $config;
    }

    public function execute()
    {
        if (!$this->config->isWebpEnabled()) {
            return;
        }

        $this->fileListSynchronizationService->synchronize(1000);
        $collection = $this->fileRepository->getCollection()
            ->setPageSize(1000)
            ->setOrder('rand()');

        foreach ($collection as $file) {
            try {
                $this->webpService->process($file);
                $this->fileRepository->save($file);
            } catch (\Exception $e) {
            }
        }
    }
}

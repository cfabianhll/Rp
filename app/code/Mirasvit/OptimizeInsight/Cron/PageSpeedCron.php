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



namespace Mirasvit\OptimizeInsight\Cron;

use Mirasvit\OptimizeInsight\Api\Data\ScoreInterface;
use Mirasvit\OptimizeInsight\Model\Config;
use Mirasvit\OptimizeInsight\Repository\ScoreRepository;
use Mirasvit\OptimizeInsight\Service\PageSpeedService;
use Psr\Log\LoggerInterface;

class PageSpeedCron
{
    private $pageSpeedService;

    private $scoreRepository;

    private $config;

    private $logger;

    public function __construct(
        PageSpeedService $pageSpeedService,
        ScoreRepository $scoreRepository,
        LoggerInterface $logger,
        Config $config
    ) {
        $this->pageSpeedService = $pageSpeedService;
        $this->scoreRepository  = $scoreRepository;
        $this->config           = $config;
        $this->logger           = $logger;
    }

    public function execute()
    {
        foreach ([PageSpeedService::STRATEGY_DESKTOP, PageSpeedService::STRATEGY_MOBILE] as $strategy) {
            foreach ($this->config->getMonitoredURLs() as $url) {
                try {
                    $value = $this->pageSpeedService->getScore($url, $strategy);

                    if ($value === false) {
                        continue;
                    }

                    $code = $strategy == PageSpeedService::STRATEGY_DESKTOP
                        ? ScoreInterface::PAGESPEED_PERFORMANCE_DESKTOP
                        : ScoreInterface::PAGESPEED_PERFORMANCE_MOBILE;

                    $model = $this->scoreRepository->create();
                    $model->setCode($code)
                        ->setUrl($url)
                        ->setValue($value);

                    $this->scoreRepository->save($model);
                } catch (\Exception $e) {
                    $this->logger->error($e);
                }
            }
        }
    }
}

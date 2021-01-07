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



namespace Mirasvit\OptimizeInsight\Console\Command;

use Mirasvit\OptimizeInsight\Api\Data\ScoreInterface;
use Mirasvit\OptimizeInsight\Repository\ScoreRepository;
use Mirasvit\OptimizeInsight\Service\PageSpeedService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PageSpeedCommand extends Command
{
    const URL = 'url';

    private $pageSpeedService;

    private $scoreRepository;

    public function __construct(
        PageSpeedService $pageSpeedService,
        ScoreRepository $scoreRepository
    ) {
        $this->pageSpeedService = $pageSpeedService;
        $this->scoreRepository  = $scoreRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputArgument(
                self::URL,
                InputArgument::REQUIRED,
                'Page URL'
            ),
        ];

        $this->setName('mirasvit:optimize-insight:pagespeed')
            ->setDescription('Run Google PageSpeed Test')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument(self::URL);

        foreach ([PageSpeedService::STRATEGY_DESKTOP, PageSpeedService::STRATEGY_MOBILE] as $strategy) {
            $output->write(sprintf('Running test for "%s"...', $strategy));

            $value = $this->pageSpeedService->getScore($url, $strategy);

            $score = $this->scoreRepository->create();
            $score->setCode($strategy == PageSpeedService::STRATEGY_DESKTOP
                ? ScoreInterface::PAGESPEED_PERFORMANCE_DESKTOP
                : ScoreInterface::PAGESPEED_PERFORMANCE_MOBILE)
                ->setUrl($url)
                ->setValue($value);

            $this->scoreRepository->save($score);

            $output->writeln(sprintf("Score: <options=bold,underscore>%s</>", $value));
        }
    }
}

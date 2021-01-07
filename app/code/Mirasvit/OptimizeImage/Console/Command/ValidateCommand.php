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

use Magento\Framework\Shell;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ValidateCommand extends Command
{
    private $shell;

    public function __construct(
        Shell $shell
    ) {
        $this->shell = $shell;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:optimize-image:validate')
            ->setDescription('Validate software required for image optimization');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pool = [
            [
                'name' => 'optipng',
                'cmd'  => 'optipng --version',
            ],
            [
                'name' => 'gifsicle',
                'cmd'  => 'gifsicle --version',
            ],
            [
                'name' => 'jpegoptim',
                'cmd'  => 'jpegoptim --version',
            ],
            [
                'name' => 'cwebp',
                'cmd'  => 'cwebp -h',
            ],
            [
                'name' => 'ImageMagick',
                'cmd'  => 'convert -version',
            ],
        ];

        foreach ($pool as $item) {
            $name = $item['name'];

            $output->write(sprintf('Check `%s`...', $name));
            try {
                $this->shell->execute($item['cmd']);
                $output->writeln(sprintf('<info>The `%s` is installed.</info>', $name));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>The `%s` is not installed.</error>', $name));
            }
        }
    }
}

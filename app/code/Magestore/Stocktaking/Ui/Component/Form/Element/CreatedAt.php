<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Ui\Component\Form\Element;

use Magento\Ui\Component\Form\Element\DataType\Date;

/**
 * Class CreatedAt
 *
 * Used for stocktaking form
 */
class CreatedAt extends Date
{
    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare(): void
    {
        parent::prepare();

        $config = $this->getData('config');

        if (isset($config['dataScope']) && $config['dataScope'] == 'created_at') {
            /* @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate */
            $localeDate = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
            );
            $config['default']= $localeDate->date()->format('Y-m-d');
            $this->setData('config', (array)$config);
        }
    }
}

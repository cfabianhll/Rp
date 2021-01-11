<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Edit\Steps;

/**
 * Status Counting in status bar
 */
class StatusCompleted extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @inheritdoc
     */
    public function getCaption()
    {
        return __('Completed');
    }
}

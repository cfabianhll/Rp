<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Block\Adminhtml\Stocktaking\Edit\Steps;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Service\Adminhtml\Stocktaking\Edit\GetCurrentStocktakingService;

/**
 * Block for status bar on edit page
 */
class Timeline extends \Magento\Ui\Block\Component\StepsWizard
{
    /**
     * Wizard step template
     *
     * @var string
     */
    protected $_template = 'Magestore_Stocktaking::form/timeline.phtml';

    /**
     * @var null|\Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     */
    private $steps;

    /**
     * @var GetCurrentStocktakingService
     */
    protected $getCurrentStocktakingService;

    /**
     * Timeline constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param GetCurrentStocktakingService $getCurrentStocktakingService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        GetCurrentStocktakingService $getCurrentStocktakingService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getCurrentStocktakingService = $getCurrentStocktakingService;
    }

    /**
     * Wizard step template
     *
     * @var string
     */
    public function getCurrentStep()
    {
        $stage = $this->getCurrentStage();
        switch ($stage) {
            case StocktakingInterface::STATUS_COUNTING:
                $step = 'variation-steps-wizard_counting';
                break;
            case StocktakingInterface::STATUS_VERIFYING:
                $step = 'variation-steps-wizard_verifying';
                break;
            case StocktakingInterface::STATUS_COMPLETED:
                $step = 'variation-steps-wizard_completed';
                break;
            default:
                $step = 'variation-steps-wizard_preparing';
                break;
        }
        return $step;
    }

    /**
     * Get steps
     *
     * @return \Magento\Ui\Block\Component\StepsWizard\StepInterface[]
     */
    public function getSteps()
    {
        if ($this->steps === null) {
            foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $step) {
                if ($step instanceof \Magento\Ui\Block\Component\StepsWizard\StepInterface) {
                    $this->steps[$step->getComponentName()] = $step;
                }
            }
        }
        return $this->steps;
    }

    /**
     * Get current page
     *
     * @return string
     */
    public function getCurrentStage()
    {
        $status = '';
        $stockTaking = $this->getCurrentStocktakingService->getCurrentStocktaking();
        if ($stockTaking && $stockTaking->getId()) {
            $status = $stockTaking->getStatus();
        }
        return $status;
    }
}

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
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model\UI\Rule\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class EventColumn extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                $events = [
                    \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_RMA_CREATED =>
                        'New RMA has been created',
                    \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_RMA_UPDATED =>
                        'RMA has been changed',
                    \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_NEW_CUSTOMER_REPLY =>
                        'New reply from customer',
                    \Mirasvit\Rma\Api\Config\RuleConfigInterface::RULE_EVENT_NEW_STAFF_REPLY =>
                        'New reply from staff',
                ];

                $item[$name] = $events[$item[$name]];
            }
        }

        return $dataSource;
    }
}
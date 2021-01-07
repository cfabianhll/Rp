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



namespace Mirasvit\Rma\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;

class Menu extends AbstractMenu
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        Context $context
    ) {
        $this->visibleAt(['rma']);

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'resource' => 'Mirasvit_Rma::rma_rma',
            'title'    => __('RMA'),
            'url'      => $this->urlBuilder->getUrl('rma/rma'),
        ]);

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_Rma::rma_dictionary_status',
            'title'    => __('Statuses'),
            'url'      => $this->urlBuilder->getUrl('rma/status'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_dictionary_reason',
            'title'    => __('Reasons'),
            'url'      => $this->urlBuilder->getUrl('rma/reason'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_dictionary_condition',
            'title'    => __('Conditions'),
            'url'      => $this->urlBuilder->getUrl('rma/condition'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_dictionary_resolution',
            'title'    => __('Resolutions'),
            'url'      => $this->urlBuilder->getUrl('rma/resolution'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_dictionary_field',
            'title'    => __('Custom Fields'),
            'url'      => $this->urlBuilder->getUrl('rma/field'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_dictionary_template',
            'title'    => __('Quick Responses'),
            'url'      => $this->urlBuilder->getUrl('rma/template'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_rule',
            'title'    => __('Workflow Rules'),
            'url'      => $this->urlBuilder->getUrl('rma/rule'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_return_addresses',
            'title'    => __('Return Addresses'),
            'url'      => $this->urlBuilder->getUrl('rma/address'),
        ]);

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_Rma::rma_report',
            'title'    => __('Report by Status'),
            'url'      => $this->urlBuilder->getUrl('rma/report/view'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_report',
            'title'    => __('Report by Reasons'),
            'url'      => $this->urlBuilder->getUrl('rma/report/reason'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_report',
            'title'    => __('Report by Product'),
            'url'      => $this->urlBuilder->getUrl('rma/report/product'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_report',
            'title'    => __('Report by Offline Product'),
            'url'      => $this->urlBuilder->getUrl('rma/report/offlineProduct'),
        ])->addItem([
            'resource' => 'Mirasvit_Rma::rma_report',
            'title'    => __('Report by Attribute'),
            'url'      => $this->urlBuilder->getUrl('rma/report/attribute'),
        ]);

        $this->addSeparator();

        $this->addItem([
            'resource' => 'Mirasvit_Rma::rma_settings',
            'title'    => __('Settings'),
            'url'      => $this->urlBuilder->getUrl('adminhtml/system_config/edit/section/rma'),
        ]);
    }
}

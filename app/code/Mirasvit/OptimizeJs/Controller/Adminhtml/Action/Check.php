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



namespace Mirasvit\OptimizeJs\Controller\Adminhtml\Action;

use Magento\Backend\App\Action;
use Mirasvit\OptimizeJs\Service\BundleFileService;

class Check extends Action
{
    private $bundleFileService;

    public function __construct(
        BundleFileService $bundleFileService,
        Action\Context $context
    ) {
        $this->bundleFileService = $bundleFileService;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $bundles = $this->bundleFileService->getScopes('frontend', false, false);

        $output = [
            'success' => false,
            'message' => '',
        ];

        if (count($bundles) > 3) {
            $output['success'] = true;
            $output['message'] = (string)__('All OK!');
        } else {
            $output['message'] = (string)__('Extension requires at least a few frontend 
                visits for allocating JavaScript by pages. Please try again later.');
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->representJson(\Zend_Json_Encoder::encode($output));
    }

    public function _processUrlKeys()
    {
        return true;
    }
}

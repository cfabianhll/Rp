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



namespace Mirasvit\Rma\Helper\Message;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Option extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;
    /**
     * @var \Mirasvit\Core\Helper\ParseVariables
     */
    private $parser;
    /**
     * @var \Mirasvit\Rma\Api\Repository\QuickResponseRepositoryInterface
     */
    private $quickResponseRepository;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Rma\Model\QuickResponseFactory
     */
    private $responseFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Magento\Store\Model\Information
     */
    private $storeInfo;

    /**
     * Option constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\Core\Helper\ParseVariables $parser
     * @param \Mirasvit\Rma\Api\Repository\QuickResponseRepositoryInterface $quickResponseRepository
     * @param \Mirasvit\Rma\Model\QuickResponseFactory $responseFactory
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Magento\Store\Model\Information $storeInfo
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Mirasvit\Core\Helper\ParseVariables $parser,
        \Mirasvit\Rma\Api\Repository\QuickResponseRepositoryInterface $quickResponseRepository,
        \Mirasvit\Rma\Model\QuickResponseFactory $responseFactory,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Store\Model\Information $storeInfo,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->registry                = $registry;
        $this->parser                  = $parser;
        $this->quickResponseRepository = $quickResponseRepository;
        $this->responseFactory         = $responseFactory;
        $this->rmaManagement           = $rmaManagement;
        $this->storeInfo               = $storeInfo;
        $this->context                 = $context;

        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\QuickResponseInterface[]
     */
    public function getOptionsList()
    {
        $items = [
            $this->responseFactory->create()->setId(0)->setName(__('-- Please Select --'))
        ];
        $storeId = $this->getRma()->getStoreId();
        $items = array_merge($items, $this->quickResponseRepository->getListByStoreId($storeId)->getItems());
        foreach ($items as $response) {
            $response->setTemplate($this->parseTemplate($response));
        }

        return $items;
    }

    /**
     * @param \Mirasvit\Rma\Model\QuickResponse $response
     * @return string
     */
    public function parseTemplate(\Mirasvit\Rma\Model\QuickResponse $response)
    {
        $template = $response->getTemplate();
        $rma = $this->getRma();
        if ($rma) {
            $store = $this->rmaManagement->getStore($rma);
            $store->setPhone($this->scopeConfig->getValue(
                'general/store_information/phone',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $store->getId()
            ));
            $storeAddress = '';
            $ignoreFields = ['region_id', 'country_id'];
            foreach ($this->storeInfo->getStoreInformationObject($store)->getData() as $key => $row) {
                if (!empty(trim($row)) && !in_array($key, $ignoreFields)) {
                    $storeAddress .= $row."\n";
                }
            }
            $store->setAddress($storeAddress);
            $data = [
                'rma'   => $rma,
                'store' => $store,
                'user'  => $this->rmaManagement->getUser($rma),
            ];
            $template = $this->parser->parse($template, $data);
        }

        return $template;
    }
}
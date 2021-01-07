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



namespace Mirasvit\Rma\Model;

use Magento\Backend\Model\Auth;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreFactory;

/**
 * @method ResourceModel\QuickResponse\Collection getCollection()
 * @method $this load(int $id)
 * @method bool getIsMassDelete()
 * @method $this setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method $this setIsMassStatus(bool $flag)
 * @method ResourceModel\QuickResponse getResource()
 */
class QuickResponse extends AbstractModel
{
    /**
     * @var \Mirasvit\Core\Api\ParseVariablesHelperInterface
     */
    private $parseVariablesHelper;
    /**
     * @var Auth
     */
    private $auth;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var StoreFactory
     */
    private $storeFactory;
    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb
     */
    private $resourceCollection;
    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
     */
    private $resource;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Framework\Model\Context
     */
    private $context;

    /**
     * QuickResponse constructor.
     * @param StoreFactory $storeFactory
     * @param \Mirasvit\Core\Api\ParseVariablesHelperInterface $parseVariablesHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Auth $auth
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        StoreFactory $storeFactory,
        \Mirasvit\Core\Api\ParseVariablesHelperInterface $parseVariablesHelper,
        ScopeConfigInterface $scopeConfig,
        Auth $auth,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeFactory         = $storeFactory;
        $this->context              = $context;
        $this->parseVariablesHelper = $parseVariablesHelper;
        $this->scopeConfig          = $scopeConfig;
        $this->auth                 = $auth;
        $this->registry             = $registry;
        $this->resource             = $resource;
        $this->resourceCollection   = $resourceCollection;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\QuickResponse');
    }

    /**
     * To option array
     *
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    /**
     * Parse template
     *
     * @param Rma $rma
     * @return string
     */
    public function getParsedTemplate($rma)
    {
        $storeId = $rma->getStoreId();
        $storeOb = $this->storeFactory->create()->load($storeId);
        if (!$name = $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        )
        ) {
            $name = $storeOb->getName();
        }
        $store = new DataObject([
            'name'    => $name,
            'phone'   => $this->scopeConfig->getValue(
                'general/store_information/phone',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $storeId
            ),
            'address' => $this->scopeConfig->getValue(
                'general/store_information/address',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $storeId
            ),
        ]);
        $user = $this->auth->getUser();

        $result = $this->parseVariablesHelper->parse(
            $this->getTemplate(),
            [
                'rma'   => $rma,
                'store' => $store,
                'user'  => $user,
            ],
            [],
            $store->getId()
        );

        return $result;
    }
}

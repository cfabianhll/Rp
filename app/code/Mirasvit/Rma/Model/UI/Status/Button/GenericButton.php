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



namespace Mirasvit\Rma\Model\UI\Status\Button;

use Magento\Backend\Block\Widget\Context;
use Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * GenericButton constructor.
     * @param CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->context = $context;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->context->getRequest()->getParam('id');
    }

    /**
     * @return \Mirasvit\Rma\Model\Status|false
     */
    public function getModel()
    {
        return $this->collectionFactory->create()->getItemById($this->getId());
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

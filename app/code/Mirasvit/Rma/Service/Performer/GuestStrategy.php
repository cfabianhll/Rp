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


namespace Mirasvit\Rma\Service\Performer;


class GuestStrategy implements \Mirasvit\Rma\Api\Service\Performer\PerformerInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaConfigInterface
     */
    private $rmaConfig;

    /**
     * GuestStrategy constructor.
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
    ) {
        $this->rmaConfig = $rmaConfig;
    }
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * {@inheritdoc}
     */
    public function setPerfomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageAttributesBeforeAdd($message, $params = [])
    {
        if (!isset($params['isNotified'])) {
            $params['isNotified'] = 1;
        }
        if (!isset($params['isVisible'])) {
            $params['isVisible'] = 1;
        }
        $message->setIsCustomerNotified($params['isNotified']);
        $message->setIsVisibleInFrontend($params['isVisible']);
        $message->setCustomerId($this->getId())
            ->setCustomerName($this->customer->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaAttributesBeforeSave($rma)
    {
        $rma->setStatusId($this->rmaConfig->getDefaultStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->customer ? $this->customer->getId() : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->customer->getName() ?: $this->getEmail();
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        if (!$this->customer->getName()) {
            return '';
        }
        $name = $this->customer->getName();
        $names = explode(' ', $name);

        return array_shift($names);
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        if (!$this->customer->getName()) {
            return '';
        }
        $name = $this->customer->getName();
        $names = explode(' ', $name);

        return array_pop($names);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->customer->getEmail();
    }
}
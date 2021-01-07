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


class UserStrategy implements \Mirasvit\Rma\Api\Service\Performer\PerformerInterface
{
    /**
     * @var \Magento\User\Model\User
     */
    protected $user;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaService;

    /**
     * UserStrategy constructor.
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaService
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaService
    ) {
        $this->rmaService = $rmaService;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerfomer($user) 
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->user->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->user->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageAttributesBeforeAdd($message, $params = [])
    {
        $isNotify = $isVisible = true;
        if (isset($params['reply_type']) && $params['reply_type'] == 'internal') {
            $isNotify = $isVisible = false;
        }
        if (isset($params['isHistory']) && $params['isHistory']) {
            $isNotify = false;
        }
        $message->setIsVisibleInFrontend($isVisible)
            ->setIsCustomerNotified($isNotify)
            ->setUserId($this->user->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaAttributesBeforeSave($rma) 
    {
        if (!$rma->getUserId()) {
            $rma->setUserId($this->getId());
        }
        if (
            $rma->getStatusId() != $rma->getOrigData('status_id') &&
            $this->rmaService->getStatus($rma)->getCustomerMessage()
        ) {
            $rma->setIsAdminRead(true);
        }
    }

}
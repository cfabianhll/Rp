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



namespace Mirasvit\Rma\Service\Rma;

class ShippingManagement implements \Mirasvit\Rma\Api\Service\Rma\ShippingManagementInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    private $fieldManagement;
    /**
     * @var \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface
     */
    private $statusRepository;
    /**
     * @var \Mirasvit\Rma\Api\Config\ShippingConfigInterface
     */
    private $config;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;

    /**
     * ShippingManagement constructor.
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface $statusRepository
     * @param \Mirasvit\Rma\Api\Config\ShippingConfigInterface $config
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Repository\StatusRepositoryInterface $statusRepository,
        \Mirasvit\Rma\Api\Config\ShippingConfigInterface $config,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->fieldManagement  = $fieldManagement;
        $this->rmaManagement    = $rmaManagement;
        $this->rmaRepository    = $rmaRepository;
        $this->statusRepository = $statusRepository;
        $this->config           = $config;
        $this->eventManager     = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function isShowShippingBlock(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $status = $this->rmaManagement->getStatus($rma);
        return $status->getIsShowShipping();
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireShippingConfirmation(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $dontShowShippingConfirmationButton = [
            \Mirasvit\Rma\Api\Data\StatusInterface::PACKAGE_SENT,
            \Mirasvit\Rma\Api\Data\StatusInterface::REJECTED,
            \Mirasvit\Rma\Api\Data\StatusInterface::CLOSED,
        ];
        $status = $this->rmaManagement->getStatus($rma);

        if (in_array($status->getCode(), $dontShowShippingConfirmationButton)) {
            return false;
        }

        return $this->config->isRequireShippingConfirmation($rma->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingConfirmationText($storeId)
    {
        return $this->config->getShippingConfirmationText($storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function confirmShipping(\Mirasvit\Rma\Api\Data\RmaInterface $rma, $data = [])
    {
        try {
            $status = $this->statusRepository->getByCode(\Mirasvit\Rma\Model\Status::PACKAGE_SENT);
            $rma->setStatusId($status->getId());

            $fields = $this->fieldManagement->getShippingConfirmationFields();
            foreach ($fields as $field) {
                if (isset($data[$field->getCode()])) {
                    $rma->setData($field->getCode(), $data[$field->getCode()]);
                }
            }

            $this->rmaRepository->save($rma);

            $this->eventManager->dispatch('rma_update_rma_after', ['rma' => $rma]);
            $this->eventManager->dispatch('rma_update_rma_shipping_after', ['rma' => $rma]);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Can't confirm shipping"));
        }
    }
}
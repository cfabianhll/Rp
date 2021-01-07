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



namespace Mirasvit\Rma\Ui\Component;

class MassAction extends \Magento\Ui\Component\MassAction
{
    protected $adminSession;

    protected $policyInterface;

    public function __construct(
        \Magento\Framework\Authorization\PolicyInterface $policyInterface,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        array $components = [],
        array $data = []

    ) {
        $this->policyInterface = $policyInterface;
        $this->adminSession    = $adminSession;
        $this->context         = $context;

        parent::__construct($context, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();

        $config         = $this->getConfiguration();
        $allowedActions = [];

        foreach ($config['actions'] as $action) {
            if ($action['type'] == 'delete' && (!$this->isDeleteAllowed())) {
                continue;
            }

            if (($action['type'] == 'is_read' || $action['type'] == 'status_id') && (!$this->isEditAllowed())) {
                continue;
            }

            $allowedActions[] = $action;

        }

        $config['actions'] = $allowedActions;
        $this->setData('config', (array)$config);

    }

    private function isDeleteAllowed()
    {
        $roleId = $this->adminSession->getUser()->getRole()->getRoleId();

        return $this->policyInterface->isAllowed($roleId, 'Mirasvit_Rma::delete');
    }

    private function isEditAllowed()
    {
        $roleId = $this->adminSession->getUser()->getRole()->getRoleId();

        return $this->policyInterface->isAllowed($roleId, 'Mirasvit_Rma::add');
    }
}
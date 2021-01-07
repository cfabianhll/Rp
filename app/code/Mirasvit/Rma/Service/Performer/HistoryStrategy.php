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


class HistoryStrategy implements \Mirasvit\Rma\Api\Service\Performer\PerformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setPerfomer($history)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageAttributesBeforeAdd($message, $params = [])
    {
        $message->setIsVisibleInFrontend(true)
            ->setIsCustomerNotified(false);
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaAttributesBeforeSave($rma)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'History';
    }
}
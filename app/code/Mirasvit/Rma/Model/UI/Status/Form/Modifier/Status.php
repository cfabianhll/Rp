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


namespace Mirasvit\Rma\Model\UI\Status\Form\Modifier;

use Magento\Framework\Registry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Mirasvit\Rma\Api\Config\RmaConfigInterface;

class Status implements ModifierInterface
{
    /**
     * @var RmaConfigInterface
     */
    private $config;
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Status constructor.
     * @param RmaConfigInterface $config
     * @param Registry $registry
     */
    public function __construct(
        RmaConfigInterface $config,
        Registry $registry
    ) {
        $this->config   = $config;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}

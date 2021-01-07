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



namespace Mirasvit\Rma\Model\UI\Rma\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Api\Service\Status\StatusManagementInterface;
use Mirasvit\Rma\Model\ResourceModel\Status\Collection;

class StatusColumn extends Column
{
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var Collection
     */
    private $statusCollection;
    /**
     * @var StatusManagementInterface
     */
    private $statusManagement;

    /**
     * StatusColumn constructor.
     * @param Escaper $escaper
     * @param Collection $statusCollection
     * @param StatusManagementInterface $statusManagement
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Escaper $escaper,
        Collection $statusCollection,
        StatusManagementInterface $statusManagement,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->escaper          = $escaper;
        $this->statusCollection = $statusCollection;
        $this->statusManagement = $statusManagement;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var StatusInterface $status */
                $status = $this->statusCollection->getItemById($item[$this->getData('name')]);
                if (!$status) {
                    continue;
                }

                $css = "mst-rma-badge status-branch-" . $status->getColor();

                $item[$this->getData('name')] = "<span class='" . $css . "'>" .
                    $this->escaper->escapeHtml($status->getName()) .
                    "</span>";

            }
        }

        return $dataSource;
    }
}

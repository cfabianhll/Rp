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
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\OptimizeImage\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mirasvit\OptimizeImage\Service\FileStatisticService;
use Mirasvit\OptimizeImage\Service\FormatService;

class StatisticGroup extends Field
{
    private $fileStatisticService;

    private $formatService;


    public function __construct(
        FileStatisticService $fileStatisticService,
        FormatService $formatService,
        Context $context,
        array $data = []
    ) {
        $this->fileStatisticService = $fileStatisticService;
        $this->formatService        = $formatService;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setTemplate('Mirasvit_OptimizeImage::system/config/statistic.phtml');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData([
            'html_id' => $element->getHtmlId(),
        ]);

        return $this->_toHtml();
    }

    public function getTotalFiles()
    {
        return $this->fileStatisticService->getTotalFiles();
    }

    public function getProcessedFiles()
    {
        return $this->fileStatisticService->getProcessedFiles();
    }

    public function getWebpFiles()
    {
        return $this->fileStatisticService->getWebpFiles();
    }

    public function getProcessedSize()
    {
        return $this->formatService->formatBytes($this->fileStatisticService->getProcessedSize());
    }

    public function getSavedSize()
    {
        return $this->formatService->formatBytes($this->fileStatisticService->getSavedSize());
    }
}

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



namespace Mirasvit\OptimizeInsight\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\OptimizeInsight\Api\Data\ScoreInterface;
use Mirasvit\OptimizeInsight\Repository\ScoreRepository;

class InsightGroup extends Field
{
    /**
     * @var ScoreRepository
     */
    private $scoreRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ScoreRepository $scoreRepository,
        Context $context,
        array $data = []
    ) {
        $this->scoreRepository = $scoreRepository;
        $this->storeManager    = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setTemplate('Mirasvit_OptimizeInsight::system/config/insight.phtml');

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
        $url = $this->storeManager->getDefaultStoreView()->getBaseUrl();

        $elements = '';
        foreach ($element->getElements() as $el) {
            $elements .= $el->toHtml();
        }

        $this->addData([
            'html_id'  => $element->getHtmlId(),
            'elements' => $elements,
            'url'      => $url,
        ]);

        return $this->_toHtml();
    }

    /**
     * @param string $code
     *
     * @return float|int
     */
    public function getAverageScore($code)
    {
        $avg    = 0;
        $values = $this->getScoreValues($code);

        foreach ($values as $v) {
            $avg += $v;
        }

        return count($values) > 0 ? $avg / count($values) : 0;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getLastCheck($code)
    {
        $scores = $this->getScores($code);

        return $scores->getFirstItem()->getCreatedAt();
    }

    /**
     * @param string $code
     *
     * @return array
     */
    public function getScoreValues($code)
    {
        $values = [];
        foreach ($this->getScores($code) as $score) {
            $values[] = $score->getValue();
        }

        return $values;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    public function getSparklineConfiguration(array $values)
    {
        return [
            'type'    => 'line',
            'data'    => [
                'labels'   => $values,
                'datasets' => [
                    [
                        'data'            => $values,
                        'lineTension'     => .5,
                        'fill'            => true,
                        'pointRadius'     => 0,
                        'borderWidth'     => 1,
                        'backgroundColor' => 'rgba(231, 234, 249, 1)',
                        'borderColor'     => 'rgba(4, 141, 199, 1)',
                    ],
                ],
            ],
            'options' => [
                'legend' => [
                    'display' => false,
                ],
                'scales' => [
                    'xAxes' => [
                        [
                            'display' => false,
                        ],
                    ],
                    'yAxes' => [
                        [
                            'display' => true,
                            'ticks'   => [
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param float|int $value
     *
     * @return array
     */
    public function getRadialConfiguration($value)
    {
        return [
            'type' => 'radialGauge',
            'data' => [
                'datasets' => [
                    [
                        'data'            => [$value],
                        'backgroundColor' => 'rgba(126, 211, 33, 1)',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string $code
     *
     * @return ScoreInterface[]|\Mirasvit\OptimizeInsight\Model\ResourceModel\Score\Collection
     */
    private function getScores($code)
    {
        $collection = $this->scoreRepository->getCollection();
        $collection->addFieldToFilter(ScoreInterface::CODE, $code)
            ->setOrder(ScoreInterface::CREATED_AT, 'desc')
            ->setPageSize((60 / 5) * 12);

        return $collection;
    }
}

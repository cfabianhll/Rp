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


namespace Mirasvit\Rma\Model\UI\Rma;

use Mirasvit\Rma\Api\Service\Field\FieldManagementInterface;
use Mirasvit\Rma\Api\Config\FieldConfigInterface as FieldConfig;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var array
     */
    protected $filterMap = [
        'default'                        => 'text',
        FieldConfig::FIELD_TYPE_TEXT     => 'text',
        FieldConfig::FIELD_TYPE_TEXTAREA => 'text',
        FieldConfig::FIELD_TYPE_CHECKBOX => 'select',
        FieldConfig::FIELD_TYPE_SELECT   => 'select',
        FieldConfig::FIELD_TYPE_DATE     => 'dateRange',
    ];
    /**
     * @var FieldManagementInterface
     */
    private $fieldManagement;

    /**
     * Columns constructor.
     * @param FieldManagementInterface $fieldManagement
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        FieldManagementInterface $fieldManagement,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);

        $this->fieldManagement = $fieldManagement;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function prepare()
    {
        parent::prepare();

        $sortOrder = 130;
        $collection = $this->fieldManagement->getGridStaffCollection();
        /** @var \Mirasvit\Rma\Model\Field $field */
        foreach ($collection as $field) {
            switch ($field->getType()) {
                case FieldConfig::FIELD_TYPE_CHECKBOX:
                    $this->addCheckboxColumn($field, $sortOrder);
                    break;
                case FieldConfig::FIELD_TYPE_DATE:
                    $this->addDateColumn($field, $sortOrder);
                    break;
                case FieldConfig::FIELD_TYPE_SELECT:
                    $this->addSelectColumn($field, $sortOrder);
                    break;
                case FieldConfig::FIELD_TYPE_TEXT:
                case FieldConfig::FIELD_TYPE_TEXTAREA:
                    $this->addTextColumn($field, $sortOrder);
                    break;
            }
            $sortOrder += 10;
        }
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getFilterType($type)
    {
        return isset($this->filterMap[$type]) ? $this->filterMap[$type] : $this->filterMap['default'];
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $field
     * @param int                       $sortOrder
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function addTextColumn($field, $sortOrder)
    {
        $arguments = [
            'data'    => [
                'config' => [
                    'label'               => __($field->getName()),
                    'visible'             => false,
                    'filter'              => $this->getFilterType($field->getType()),
                    'sortOrder'           => $sortOrder,
                ],
            ],
            'context' => $this->context,
        ];
        $column = $this->context->getUiComponentFactory()->create($field->getCode(), 'column', $arguments);

        $column->prepare();

        $this->addComponent($field->getCode(), $column);
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $field
     * @param int                       $sortOrder
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function addCheckboxColumn($field, $sortOrder)
    {
        $arguments = [
            'data'    => [
                'options' => [
                    'active' => [
                        'value' => 1,
                        'label' => __('Yes'),
                    ],
                    'inactive' => [
                        'value' => 0,
                        'label' => __('No'),
                    ],
                ],
                'config'  => [
                    'label'               => __($field->getName()),
                    'visible'             => false,
                    'component'           => 'Magento_Ui/js/grid/columns/select',
                    'editor'              => 'select',
                    'dataType'            => 'select',
                    'filter'              => $this->getFilterType($field->getType()),
                    'sortOrder'           => $sortOrder,
                ],
            ],
            'context' => $this->context,
        ];
        $column = $this->context->getUiComponentFactory()->create($field->getCode(), 'column', $arguments);

        $column->prepare();

        $this->addComponent($field->getCode(), $column);
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $field
     * @param int                       $sortOrder
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function addSelectColumn($field, $sortOrder)
    {
        $options = [];
        foreach ($field->getValues() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => __($label),
            ];
        }
        $arguments = [
            'data'    => [
                'options' => $options,
                'config'  => [
                    'label'               => __($field->getName()),
                    'visible'             => false,
                    'component'           => 'Magento_Ui/js/grid/columns/select',
                    'editor'              => 'select',
                    'dataType'            => 'select',
                    'filter'              => $this->getFilterType($field->getType()),
                    'sortOrder'           => $sortOrder,
                ],
            ],
            'context' => $this->context,
        ];
        $column = $this->context->getUiComponentFactory()->create($field->getCode(), 'column', $arguments);

        $column->prepare();

        $this->addComponent($field->getCode(), $column);
    }

    /**
     * @param \Mirasvit\Rma\Model\Field $field
     * @param int                       $sortOrder
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function addDateColumn($field, $sortOrder)
    {
        $arguments = [
            'config'  => [
                'class'               => 'Mirasvit\Rma\Model\UI\Rma\Column\DateColumn',
            ],
            'data'    => [
                'config'  => [
                    'label'               => __($field->getName()),
                    'visible'             => false,
                    'dataType'            => 'text',
                    'filter'              => $this->getFilterType($field->getType()),
                    'sortOrder'           => $sortOrder,
                ],
            ],
            'context' => $this->context,
            'components' => [],
        ];
        $column = $this->context->getUiComponentFactory()->create($field->getCode(), 'column', $arguments);

        $column->prepare();

        $this->addComponent($field->getCode(), $column);
    }
}
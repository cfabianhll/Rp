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


namespace Mirasvit\Rma\Service\Field;

/**
 *  We put here only methods directly connected with Field properties
 */
class FieldManagement implements \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;
    /**
     * @var \Mirasvit\Rma\Api\Repository\FieldRepositoryInterface
     */
    private $fieldRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * FieldManagement constructor.
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Mirasvit\Rma\Api\Repository\FieldRepositoryInterface $fieldRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rma\Api\Repository\FieldRepositoryInterface $fieldRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->escaper               = $escaper;
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->fieldRepository       = $fieldRepository;
        $this->localeDate            = $localeDate;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->assetRepo             = $assetRepo;
        $this->objectManager         = $objectManager;
    }

    /**
     * @return \Magento\Framework\Api\SortOrder
     */
    protected function getSortOrder()
    {
        return $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection(\Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableCustomerCollection()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addFilter('is_editable_customer', true)
            ->addSortOrder($this->getSortOrder())
        ;

        return $this->fieldRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleCustomerCollection($status, $isEdit)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addFilter('visible_customer_status', "%,$status,%", 'like')
            ->addSortOrder($this->getSortOrder())
        ;
        if ($isEdit) {
            $searchCriteria->addFilter('is_editable_customer', true);
        }

        return $this->fieldRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingConfirmationFields()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addFilter('is_show_in_confirm_shipping', true)
            ->addSortOrder($this->getSortOrder())
        ;

        return $this->fieldRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getStaffCollection()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
            ->addSortOrder($this->getSortOrder())
        ;

        return $this->fieldRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridStaffCollection()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', true)
        ;

        return $this->fieldRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getInputParams($field, $staff = true, $object = false)
    {
        $value = $object ? $object->getData($this->escaper->escapeHtml($field->getCode())) : '';
        switch ($field->getType()) {
            case 'checkbox':
                $value = 1;
                break;
            case 'date':
                if ($value == '0000-00-00 00:00:00') {
                    $value = time();
                }
                break;
        }
        $values = [];
        if (is_array($field->getValues())) {
            foreach ($field->getValues() as $k => $v) {
                $values[$this->escaper->escapeHtml($k)] = $this->escaper->escapeHtml($v);
            }
        } else {
            $values = $this->escaper->escapeHtml($field->getValues());
        }

        return [
            'label'        => __($this->escaper->escapeHtml($field->getName())),
            'name'         => $field->getCode(),
            'required'     => $staff ? $field->getIsRequiredStaff() : $field->getIsRequiredCustomer(),
            'value'        => $value,
            'checked'      => $object ? $object->getData($this->escaper->escapeHtml($field->getCode())) : false,
            'values'       => $values,
            'image'        => $this->assetRepo->getUrl('images/grid-cal.gif'),
            'note'         => $this->escaper->escapeHtml($field->getDescription()),
            'date_format'  => $this->localeDate->getDateFormat(\IntlDateFormatter::SHORT),
            'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getInputHtml($field)
    {
        $params = $this->getInputParams($field, false);
        unset($params['label']);
        $className = '\Magento\Framework\Data\Form\Element\\'.ucfirst(strtolower($field->getType()));
        $element = $this->objectManager->create($className);
        $element->setData($params);
        $element->setForm(new \Magento\Framework\DataObject());
        $element->setId($this->escaper->escapeHtml($field->getCode()));
        $element->setNoSpan(true);
        $element->addClass($this->escaper->escapeHtml($field->getType()));
        $element->setType($this->escaper->escapeHtml($field->getType()));
        if ($field->getIsRequiredCustomer()) {
            $element->addClass('required-entry');
        }

        //store may have wrong renderer. so we can't use ->toHtml() here;
        return $element->getDefaultHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function processPost($post, $object)
    {
        $collection = $this->getEditableCustomerCollection();
        foreach ($collection as $field) {
            if (isset($post[$field->getCode()])) {
                $value = $post[$field->getCode()];
                $object->setData($field->getCode(), $value);
            }
            if ($field->getType() == 'checkbox') {
                if (!isset($post[$field->getCode()])) {
                    $object->setData($field->getCode(), 0);
                }
            } elseif ($field->getType() == 'date') {
                $value = $object->getData($field->getCode());
                try {
                    $value = $this->localeDate->formatDate($value, \IntlDateFormatter::SHORT);
                } catch (\Exception $e) { //we have exception if input date is in incorrect format
                    $value = '';
                }
                $object->setData($field->getCode(), $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($object, $field)
    {
        $value = $object->getData($field->getCode());
        if (!$value) {
            return false;
        }
        if ($field->getType() == 'checkbox') {
            $value = $value ? __('Yes') : __('No');
        } elseif ($field->getType() == 'date') {
            try {
                $value = $this->localeDate->formatDate($value, \IntlDateFormatter::MEDIUM);
            } catch (\Exception $e) { //we have exception if input date is in incorrect format
                $value = '';
            }
        } elseif ($field->getType() == 'select') {
            $values = $field->getValues();
            $value = $values[$value];
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldByCode($code)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('code', $code)
        ;

        $data = $this->fieldRepository->getList($searchCriteria->create())->getItems();
        if (count($data)) {
            return array_shift($data);
        }
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function filterCode($code)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $code);
    }
}


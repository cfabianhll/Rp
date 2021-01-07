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



namespace Mirasvit\Rma\Model\Rule\Condition;

use Mirasvit\Rma\Api\Config\RmaConfigInterface as Config;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Rma extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    private $rmaField;
    /**
     * @var \Mirasvit\Rma\Helper\Item\Option
     */
    private $optionList;
    /**
     * @var \Mirasvit\Rma\Helper\Store
     */
    private $rmaData;
    /**
     * @var \Mirasvit\Rma\Helper\User\Html
     */
    private $userHtml;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory
     */
    private $statusCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface
     */
    private $messageManagement;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Field\CollectionFactory
     */
    private $fieldCollectionFactory;
    /**
     * @var \Magento\Rule\Model\Condition\Context
     */
    private $context;

    /**
     * Rma constructor.
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement
     * @param \Mirasvit\Rma\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $rmaField
     * @param \Mirasvit\Rma\Helper\Store $rmaData
     * @param \Mirasvit\Rma\Helper\Item\Option $optionList
     * @param \Mirasvit\Rma\Helper\User\Html $userHtml
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagementInterface $messageManagement,
        \Mirasvit\Rma\Model\ResourceModel\Field\CollectionFactory $fieldCollectionFactory,
        \Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $rmaField,
        \Mirasvit\Rma\Helper\Store $rmaData,
        \Mirasvit\Rma\Helper\Item\Option $optionList,
        \Mirasvit\Rma\Helper\User\Html $userHtml,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    ) {
        $this->rmaSearchManagement     = $rmaSearchManagement;
        $this->messageManagement       = $messageManagement;
        $this->fieldCollectionFactory  = $fieldCollectionFactory;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->rmaField                = $rmaField;
        $this->rmaData                 = $rmaData;
        $this->optionList              = $optionList;
        $this->userHtml                = $userHtml;
        $this->context                 = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = [
            'last_message'              => __('Last message body'),
            'created_at'                => __('Created At'),
            'updated_at'                => __('Updated At'),
            'store_id'                  => __('Store'),
            'old_status_id'             => __('Status (before change)'),
            'status_id'                 => __('Status'),
            'old_user_id'               => __('Owner (before change)'),
            'user_id'                   => __('Owner'),
            'last_reply_by'             => __('Last Reply By'),
            'hours_since_created_at'    => __('Hours since Created'),
            'hours_since_updated_at'    => __('Hours since Updated'),
            'hours_since_last_reply_at' => __('Hours since Last reply'),
            'items_have_reason'         => __('Items have reason'),
            'items_have_condition'      => __('Items have condition'),
            'items_have_resolution'     => __('Items have resolution'),
        ];

        $fields = $this->fieldCollectionFactory->create()
            ->setOrder('sort_order');

        foreach ($fields as $field) {
            $attributes['old_' . $field->getCode()] = __('%1 (before change)', $field->getName());
            $attributes[$field->getCode()] = $field->getName();
        }

        // asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * @return $this
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);

        return $element;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        $attrCode = $this->getAttribute();
        if (strpos($attrCode, '_id') !== false || $attrCode == 'last_reply_by' ||
            strpos($attrCode, 'items_have_') === 0
        ) {
            return 'select';
        }

        if ($field = $this->getCustomFieldByAttributeCode($attrCode)) {
            if ($field->getType() == 'select') {
                return 'select';
            }
        }

        return 'string';
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        switch ($this->getInputType()) {
            case 'string':
                return 'text';
        }

        return $this->getInputType();
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     *
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Mirasvit\Rma\Api\Data\RmaInterface $object */
        $attrCode = $this->getAttribute();
        if (strpos($attrCode, 'old_') === 0) {
            $attrCode = str_replace('old_', '', $attrCode);
            $value = $object->getOrigData($attrCode);
        } elseif ($attrCode == 'last_message') {
            $value = $this->rmaSearchManagement->getLastMessage($object)->getTextHtml();
        } elseif ($attrCode == 'last_reply_by') {
            $lastMessage = $this->rmaSearchManagement->getLastMessage($object);
            $value = $this->messageManagement->getTriggeredBy($lastMessage);
        } elseif (strpos($attrCode, 'hours_since_') === 0) {
            $attrCode = str_replace('hours_since_', '', $attrCode);
            $timestamp = $object->getData($attrCode);

            $diff = abs(
                strtotime((new \DateTime())
                    ->format(
                        \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT
                    )) - strtotime($timestamp)
            );
            $value = round($diff / 60 / 60);
        } elseif (strpos($attrCode, 'items_have_') === 0) {
            /** @var \Mirasvit\Rma\Api\Data\RmaInterface $object */
            return $this->isReasonsValid($attrCode, $object);
        } else {
            $value = $object->getData($attrCode);
        }
        if (strpos($attrCode, '_id') !== false) {
            $value = (int)$value;
            //we need this to empty value to zero and then to compare
        }

        return $this->validateAttribute($value);
    }

    /**
     * @param string                                 $attrCode
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $object
     * @return bool
     */
    protected function isReasonsValid($attrCode, $object)
    {
        $validatedValue = false;

        $value = $this->getValueParsed();
        if (strpos($attrCode, 'reason') !== false) {
            $validatedValue = $this->rmaSearchManagement->hasRmaReason($object, $value);
        } elseif (strpos($attrCode, 'condition') !== false) {
            $validatedValue = $this->rmaSearchManagement->hasRmaCondition($object, $value);
        } elseif (strpos($attrCode, 'resolution') !== false) {
            $validatedValue = $this->rmaSearchManagement->hasRmaResolution($object, $value);
        }

        if ($validatedValue && $this->getOperatorForValidate() != '==') {
            $validatedValue = false;
        }

        return (bool)$validatedValue;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareValueOptions()
    {
        // Check that both keys exist. Maybe somehow only one was set not in this routine, but externally.
        $selectReady = $this->getData('value_select_options');
        $hashedReady = $this->getData('value_option');
        if ($selectReady && $hashedReady) {
            return $this;
        }
        // Get array of select options. It will be used as source for hashed options
        $selectOptions = null;
        $addNotEmpty = true;
        $field = $this->getCustomFieldByAttributeCode($this->getAttribute());

        if ($field && $field->getType() == 'select') {
            $selectOptions = $field->getValues();
        } else {
            switch ($this->getAttribute()) {
                case 'status_id':
                case 'old_status_id':
                    $selectOptions = $this->statusCollectionFactory->create()->getOptionArray();
                    break;
                case 'user_id':
                case 'old_user_id':
                    $selectOptions = $this->userHtml->getAdminUserOptionArray();
                    break;
                case 'store_id':
                    $selectOptions = $this->rmaData->getCoreStoreOptionArray();
                    break;
                case 'last_reply_by':
                    $selectOptions = [
                        Config::CUSTOMER => __('Customer'),
                        Config::USER     => __('Staff'),
                    ];
                    $addNotEmpty = false;
                    break;
                case 'items_have_reason':
                    $selectOptions = $this->optionList->getReasonOptionArray();
                    $addNotEmpty   = false;
                    break;
                case 'items_have_resolution':
                    $selectOptions = $this->optionList->getResolutionOptionArray();
                    $addNotEmpty   = false;
                    break;
                case 'items_have_condition':
                    $selectOptions = $this->optionList->getConditionOptionArray();
                    $addNotEmpty   = false;
                    break;
                default:
                    return $this;
            }
        }
        if ($addNotEmpty) {
            $selectOptions[0] = '(not set)';
        }

        $optionsA = [];
        foreach ($selectOptions as $key => $value) {
            $optionsA[] = ['value' => $key, 'label' => $value];
        }
        $selectOptions = $optionsA;

        // Set new values only if we really got them
        if ($selectOptions !== null) {
            // Overwrite only not already existing values
            if (!$selectReady) {
                $this->setData('value_select_options', $selectOptions);
            }
            if (!$hashedReady) {
                $hashedOptions = [];
                foreach ($selectOptions as $o) {
                    if (is_array($o['value'])) {
                        continue; // We cannot use array as index
                    }
                    $hashedOptions[$o['value']] = $o['label'];
                }
                $this->setData('value_option', $hashedOptions);
            }
        }

        return $this;
    }

    /**
     * Retrieve value by option.
     *
     * @param string $option
     *
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option' . ($option !== null ? '/' . $option : ''));
    }

    /**
     * {@inheritdoc}
     */
    public function getValueSelectOptions()
    {
        $this->_prepareValueOptions();

        return $this->getData('value_select_options');
    }

    /**
     * @return string
     */
    public function getJsFormObject()
    {
        return 'rule_conditions_fieldset';
    }

    /**
     * @param string $attrCode
     *
     * @return \Mirasvit\Rma\Model\Field|null
     */
    protected function getCustomFieldByAttributeCode($attrCode)
    {
        if (strpos($attrCode, 'f_') === 0 || strpos($attrCode, 'old_f_') === 0) {
            $attrCode = str_replace('old_f_', 'f_', $attrCode);

            if ($field = $this->rmaField->getFieldByCode($attrCode)) {
                return $field;
            }
        }
    }
}

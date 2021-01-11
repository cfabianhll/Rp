<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\SupplierSuccess\Ui\DataProvider\Supplier\DataForm\Modifier;

use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Field;
use Magestore\SupplierSuccess\Model\Locator\LocatorInterface;
use Magestore\SupplierSuccess\Service\SupplierService;

/**
 * Class Information
 *
 * Data provider for Configurable panel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Information extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var SupplierService
     */
    protected $supplierService;

    /**
     * @return boolean
     */
    protected $opened = true;

    /**
     * @var
     */
    protected $loadedData;

    /**
     * Information constructor.
     *
     * @param LocatorInterface $locator
     * @param SupplierService $supplierService
     */
    public function __construct(
        LocatorInterface $locator,
        SupplierService $supplierService
    ) {
        $this->locator = $locator;
        $this->supplierService = $supplierService;
        $supplier = $this->locator->getSupplier();
        if ($supplier && $supplier->getId()) {
            $this->opened = false;
        }
    }

    /**
     * Modify data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $data = array_replace_recursive(
            $data,
            $this->getData()
        );
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $supplier = $this->locator->getSupplier();
        if ($supplier && $supplier->getId()) {
            $this->loadedData[$supplier->getId()] = $supplier->getData();
        }
        return $this->loadedData;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            $this->getSupplierInformation($meta)
        );
        return $meta;
    }

    /**
     * Get supplier information
     *
     * @param array $meta
     * @return mixed
     */
    public function getSupplierInformation($meta)
    {
        $meta['information']['arguments']['data']['config'] = [
            'label' => __('Supplier Information'),
            'collapsible' => true,
            'visible' => true,
            'opened' => $this->opened,
            'dataScope' => 'data',
            'componentType' => Form\Fieldset::NAME
        ];
        $meta['information']['children'] = $this->getSupplierInformationChildren();
        return $meta;
    }

    /**
     * Get supplier information children
     *
     * @return array
     */
    public function getSupplierInformationChildren()
    {
        $children = [
            'supplier_code' => $this->getField(
                __('Supplier Code'),
                Field::NAME,
                true,
                'text',
                'input',
                ['required-entry' => true]
            ),
            'supplier_name' => $this->getField(
                __('Supplier Name'),
                Field::NAME,
                true,
                'text',
                'input',
                ['required-entry' => true]
            ),
            'contact_name' => $this->getField(
                __('Contact Person'),
                Field::NAME,
                true,
                'text',
                'input',
                ['required-entry' => true]
            ),
            'contact_email' => $this->getField(
                __('Supplier Email'),
                Field::NAME,
                true,
                'text',
                'input',
                ['required-entry' => true, 'validate-email' => true]
            ),
            'additional_emails' => $this->getField(
                __('Cc Emails'),
                Field::NAME,
                true,
                'text',
                'input',
                ['validate-emails' => true],
                __('Comma-separated.')
            ),
            'description' => $this->getField(
                __('Description'),
                Field::NAME,
                true,
                'text',
                'textarea'
            ),
            'status' => $this->getField(
                __('Status'),
                Field::NAME,
                true,
                'text',
                'select',
                [],
                null,
                $this->getStatusOptions()
            ),
        ];
        return $children;
    }

    /**
     * Get status option
     *
     * @return array
     */
    public function getStatusOptions()
    {
        return $this->supplierService->toStatusOptionArray();
    }
}

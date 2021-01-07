<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface WarehouseInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'warehouse_id';

    const TITLE = 'title';

    const CODE = 'code';

    const STORES = 'stores';

    const COUNTRY = 'country';

    const STATE = 'state';

    const CITY = 'city';

    const ADDRESS = 'address';

    const ZIP = 'zip';

    const PHONE = 'phone';

    const EMAIL = 'email';

    const DESCRIPTION = 'description';

    const MANAGE = 'manage';

    const PRIORITY = 'priority';

    const IS_GENERAL = 'is_general';

    const ORDER_EMAIL_NOTIFICATION = 'order_email_notification';

    const LOW_STOCK_NOTIFICATION = 'low_stock_notification';

    const STOCK_ID = 'stock_id';

    const CREATE_TIME = 'create_time';

    const UPDATE_TIME = 'update_time';

    const IS_SHIPPING = 'is_shipping';

    const BACKORDERS = 'backorders';

    const SHIPPINGS = 'shippings';

    const CUSTOMER_GROUPS = 'customer_groups';

    const ITEMS = 'items';

    const REMOVE_ITEMS = 'remove_items';

    const CACHE_TAG = 'warehouse';

    const LNG = 'lng';

    const LAT = 'lat';

    /**#@-*/

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getZip();

    /**
     * @return string
     */
    public function getPhone();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return bool
     */
    public function isManage();

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @return bool
     */
    public function isGeneral();

    /**
     * @return string
     */
    public function getOrderEmailNotification();

    /**
     * @return string
     */
    public function getLowStockNotification();

    /**
     * @return int|null
     */
    public function getStockId();

    /**
     * @return string
     */
    public function getCreateTime();

    /**
     * @return string
     */
    public function getUpdateTime();

    /**
     * @return bool
     */
    public function isShipping();

    /**
     * @return WarehouseCustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * @param int $id
     *
     * @return WarehouseInterface
     */
    public function setId($id);

    /**
     * @param string $title
     *
     * @return WarehouseInterface
     */
    public function setTitle($title);

    /**
     * @param string $code
     *
     * @return WarehouseInterface
     */
    public function setCode($code);

    /**
     * @param string $country
     *
     * @return WarehouseInterface
     */
    public function setCountry($country);

    /**
     * @param string $state
     *
     * @return WarehouseInterface
     */
    public function setState($state);

    /**
     * @param string $city
     *
     * @return WarehouseInterface
     */
    public function setCity($city);

    /**
     * @param string $address
     *
     * @return WarehouseInterface
     */
    public function setAddress($address);

    /**
     * @param string $zip
     *
     * @return WarehouseInterface
     */
    public function setZip($zip);

    /**
     * @param string $phone
     *
     * @return WarehouseInterface
     */
    public function setPhone($phone);

    /**
     * @param string $email
     *
     * @return WarehouseInterface
     */
    public function setEmail($email);

    /**
     * @param string $description
     *
     * @return WarehouseInterface
     */
    public function setDescription($description);

    /**
     * @param bool $manage
     *
     * @return WarehouseInterface
     */
    public function setManage($manage);

    /**
     * @param int $priority
     *
     * @return WarehouseInterface
     */
    public function setPriority($priority);

    /**
     * @param bool $value
     *
     * @return WarehouseInterface
     */
    public function setIsGeneral($value);

    /**
     * @param string $notify
     *
     * @return WarehouseInterface
     */
    public function setOrderEmailNotification($notify);

    /**
     * @param string $notify
     *
     * @return WarehouseInterface
     */
    public function setLowStockNotification($notify);

    /**
     * @param int $id
     *
     * @return WarehouseInterface
     */
    public function setStockId($id);

    /**
     * @param string $time
     *
     * @return WarehouseInterface
     */
    public function setCreateTime($time);

    /**
     * @param string $time
     *
     * @return WarehouseInterface
     */
    public function setUpdateTime($time);

    /**
     * @param bool $bool
     *
     * @return WarehouseInterface
     */
    public function setIsShipping($bool);

    /**
     * @param WarehouseCustomerGroupInterface[]
     *
     * @return WarehouseInterface
     */
    public function setCustomerGroups($groups);

    /**
     * @return int
     */
    public function getBackorders();

    /**
     * @param int $backorders
     *
     * @return WarehouseInterface
     */
    public function setBackorders($backorders);

    /**
     * @return string
     */
    public function getLng();

    /**
     * @param string $lng
     *
     * @return WarehouseInterface
     */
    public function setLng($lng);

    /**
     * @return string
     */
    public function getLat();

    /**
     * @param string $lat
     *
     * @return WarehouseInterface
     */
    public function setLat($lat);
}

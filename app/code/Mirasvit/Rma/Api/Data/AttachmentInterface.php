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



namespace Mirasvit\Rma\Api\Data;

use Mirasvit\Rma\Api;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @method Api\Data\AttachmentSearchResultsInterface getList(SearchCriteriaInterface $searchCriteria)
 */
interface AttachmentInterface extends DataInterface
{
    const KEY_ITEM_TYPE  = 'item_type';
    const KEY_ITEM_ID    = 'item_id';
    const KEY_UID        = 'uid';
    const KEY_NAME       = 'name';
    const KEY_TYPE       = 'type';
    const KEY_SIZE       = 'size';
    const KEY_BODY       = 'body';
    const KEY_CREATED_AT = 'created_at';

    /**
     * @return string
     */
    public function getItemType();

    /**
     * @param string $itemType
     * @return $this
     */
    public function setItemType($itemType);

    /**
     * @return int
     */
    public function getItemId();

    /**
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * @return string
     */
    public function getUid();

    /**
     * @param string $uid
     * @return $this
     */
    public function setUid($uid);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);


    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getSize();

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size);

    /**
     * @return string
     */
    public function getBody();

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);


}
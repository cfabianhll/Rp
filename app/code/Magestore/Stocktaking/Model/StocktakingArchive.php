<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model;

use Magento\Framework\Model\AbstractModel;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive as StocktakingArchiveResource;

/**
 * Class StocktakingArchive
 *
 * Used for Stocktaking Archive model
 */
class StocktakingArchive extends AbstractModel implements StocktakingArchiveInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(StocktakingArchiveResource::class);
    }

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Get source code
     *
     * @return string|null
     */
    public function getSourceCode()
    {
        return $this->getData(self::SOURCE_CODE);
    }

    /**
     * Get Source Name
     *
     * @return string|null
     */
    public function getSourceName()
    {
        return $this->getData(self::SOURCE_NAME);
    }

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get stocktaking type
     *
     * @return int|null
     */
    public function getStocktakingType()
    {
        return $this->getData(self::STOCKTAKING_TYPE);
    }

    /**
     * Get created by id
     *
     * @return int|null
     */
    public function getCreatedById()
    {
        return $this->getData(self::CREATED_BY_ID);
    }

    /**
     * Get created by first name
     *
     * @return string|null
     */
    public function getCreatedByFirstName()
    {
        return $this->getData(self::CREATED_BY_FIRST_NAME);
    }

    /**
     * Get created by last name
     *
     * @return string|null
     */
    public function getCreatedByLastName()
    {
        return $this->getData(self::CREATED_BY_LAST_NAME);
    }

    /**
     * Get assign user id
     *
     * @return int|null
     */
    public function getAssignUserId()
    {
        return $this->getData(self::ASSIGN_USER_ID);
    }

    /**
     * Get assign user first name
     *
     * @return string|null
     */
    public function getAssignUserFirstName()
    {
        return $this->getData(self::ASSIGN_USER_FIRST_NAME);
    }

    /**
     * Get assign user last name
     *
     * @return string|null
     */
    public function getAssignUserLastName()
    {
        return $this->getData(self::ASSIGN_USER_LAST_NAME);
    }

    /**
     * Set id
     *
     * @param int|null $id
     * @return StocktakingArchiveInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set stocktaking code
     *
     * @param string|null $code
     * @return StocktakingArchiveInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Set source code
     *
     * @param string|null $sourceCode
     * @return StocktakingArchiveInterface
     */
    public function setSourceCode($sourceCode)
    {
        return $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * Set source name
     *
     * @param string|null $sourceName
     * @return StocktakingArchiveInterface
     */
    public function setSourceName($sourceName)
    {
        return $this->setData(self::SOURCE_NAME, $sourceName);
    }

    /**
     * Set created at
     *
     * @param string|null $createdAt
     * @return StocktakingArchiveInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set status
     *
     * @param int|null $status
     * @return StocktakingArchiveInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set description
     *
     * @param string|null $description
     * @return StocktakingArchiveInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set stocktaking type
     *
     * @param int|null $stocktakingType
     * @return StocktakingArchiveInterface
     */
    public function setStocktakingType($stocktakingType)
    {
        return $this->setData(self::STOCKTAKING_TYPE, $stocktakingType);
    }

    /**
     * Set created by id
     *
     * @param int|null $createdById
     * @return StocktakingArchiveInterface
     */
    public function setCreatedById($createdById)
    {
        return $this->setData(self::CREATED_BY_ID, $createdById);
    }

    /**
     * Set created by first name
     *
     * @param int|null $createdByFirstName
     * @return StocktakingArchiveInterface
     */
    public function setCreatedByFirstName($createdByFirstName)
    {
        return $this->setData(self::CREATED_BY_FIRST_NAME, $createdByFirstName);
    }

    /**
     * Set created by last name
     *
     * @param int|null $createdByLastName
     * @return StocktakingArchiveInterface
     */
    public function setCreatedByLastName($createdByLastName)
    {
        return $this->setData(self::CREATED_BY_LAST_NAME, $createdByLastName);
    }

    /**
     * Set assign user id
     *
     * @param int|null $assignUserId
     * @return StocktakingArchiveInterface
     */
    public function setAssignUserId($assignUserId)
    {
        return $this->setData(self::ASSIGN_USER_ID, $assignUserId);
    }

    /**
     * Set assign user first name
     *
     * @param int|null $assignUserFirstName
     * @return StocktakingArchiveInterface
     */
    public function setAssignUserFirstName($assignUserFirstName)
    {
        return $this->setData(self::ASSIGN_USER_FIRST_NAME, $assignUserFirstName);
    }

    /**
     * Set assign user last name
     *
     * @param int|null $assignUserLastName
     * @return StocktakingArchiveInterface
     */
    public function setAssignUserLastName($assignUserLastName)
    {
        return $this->setData(self::ASSIGN_USER_LAST_NAME, $assignUserLastName);
    }
}

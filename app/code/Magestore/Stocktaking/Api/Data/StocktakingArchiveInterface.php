<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Api\Data;

interface StocktakingArchiveInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CODE = 'code';
    const SOURCE_CODE = 'source_code';
    const SOURCE_NAME = 'source_name';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';
    const DESCRIPTION = 'description';
    const STOCKTAKING_TYPE = 'stocktaking_type';
    const CREATED_BY_ID = 'created_by_id';
    const CREATED_BY_FIRST_NAME = 'created_by_first_name';
    const CREATED_BY_LAST_NAME = 'created_by_last_name';
    const ASSIGN_USER_ID = 'assign_user_id';
    const ASSIGN_USER_FIRST_NAME = 'assign_user_first_name';
    const ASSIGN_USER_LAST_NAME = 'assign_user_last_name';

    const STATUS_NEW = 0;
    const STATUS_PREPARING = 1;
    const STATUS_COUNTING = 2;
    const STATUS_VERIFYING = 3;
    const STATUS_CANCELED = 4;
    const STATUS_COMPLETED = 5;

    const STOCKTAKING_TYPE_PARTIAL = 1;
    const STOCKTAKING_TYPE_FULL = 2;

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get source code
     *
     * @return string|null
     */
    public function getSourceCode();

    /**
     * Get Source Name
     *
     * @return string|null
     */
    public function getSourceName();

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get stocktaking type
     *
     * @return int|null
     */
    public function getStocktakingType();

    /**
     * Get created by id
     *
     * @return int|null
     */
    public function getCreatedById();

    /**
     * Get created by first name
     *
     * @return string|null
     */
    public function getCreatedByFirstName();

    /**
     * Get created by last name
     *
     * @return string|null
     */
    public function getCreatedByLastName();

    /**
     * Get assign user id
     *
     * @return int|null
     */
    public function getAssignUserId();

    /**
     * Get assign user first name
     *
     * @return string|null
     */
    public function getAssignUserFirstName();

    /**
     * Get assign user last name
     *
     * @return string|null
     */
    public function getAssignUserLastName();

    /**
     * Set id
     *
     * @param int|null $id
     * @return StocktakingArchiveInterface
     */
    public function setId($id);

    /**
     * Set stocktaking code
     *
     * @param string|null $code
     * @return StocktakingArchiveInterface
     */
    public function setCode($code);

    /**
     * Set source code
     *
     * @param string|null $sourceCode
     * @return StocktakingArchiveInterface
     */
    public function setSourceCode($sourceCode);

    /**
     * Set source name
     *
     * @param string|null $sourceName
     * @return StocktakingArchiveInterface
     */
    public function setSourceName($sourceName);

    /**
     * Set created at
     *
     * @param string|null $createdAt
     * @return StocktakingArchiveInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set status
     *
     * @param int|null $status
     * @return StocktakingArchiveInterface
     */
    public function setStatus($status);

    /**
     * Set description
     *
     * @param string|null $description
     * @return StocktakingArchiveInterface
     */
    public function setDescription($description);

    /**
     * Set stocktaking type
     *
     * @param int|null $stocktakingType
     * @return StocktakingArchiveInterface
     */
    public function setStocktakingType($stocktakingType);

    /**
     * Set created by id
     *
     * @param int|null $createdById
     * @return StocktakingArchiveInterface
     */
    public function setCreatedById($createdById);

    /**
     * Set created by first name
     *
     * @param int|null $createdByFirstName
     * @return StocktakingArchiveInterface
     */
    public function setCreatedByFirstName($createdByFirstName);

    /**
     * Set created by last name
     *
     * @param int|null $createdByLastName
     * @return StocktakingArchiveInterface
     */
    public function setCreatedByLastName($createdByLastName);

    /**
     * Set assign user id
     *
     * @param int|null $assignUserId
     * @return StocktakingArchiveInterface
     */
    public function setAssignUserId($assignUserId);

    /**
     * Set assign user first name
     *
     * @param int|null $assignUserFirstName
     * @return StocktakingArchiveInterface
     */
    public function setAssignUserFirstName($assignUserFirstName);

    /**
     * Set assign user last name
     *
     * @param int|null $assignUserLastName
     * @return StocktakingArchiveInterface
     */
    public function setAssignUserLastName($assignUserLastName);
}

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
 * @package   mirasvit/module-report-api
 * @version   1.0.37
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Config\Aggregator;

use Magento\Framework\App\ResourceConnection;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;

class Week implements AggregatorInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Week constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_WEEK;
    }

    /**
     * @return array|\Zend_Db_Expr
     */
    public function getExpression()
    {
        $connection = $this->resource->getConnection();

        $year       = $connection->getDateFormatSql('%1', '%Y');
        $weekOfYear = new \Zend_Db_Expr('WEEKOFYEAR(%1)');
        $firstDay   = new \Zend_Db_Expr("'Monday'");
        $contact    = $connection->getConcatSql([$year, $weekOfYear, $firstDay], ' ');

        return $connection->getConcatSql(["STR_TO_DATE($contact, '%X %V %W')", "'00:00:00'"], ' ');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Week';
    }
}

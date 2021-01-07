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



namespace Mirasvit\OptimizeInsight\Api\Data;

interface ScoreInterface
{
    const PAGESPEED_PERFORMANCE_DESKTOP = 'pagespeed_performance_desktop';
    const PAGESPEED_PERFORMANCE_MOBILE  = 'pagespeed_performance_mobile';

    const TABLE_NAME = 'mst_optimize_insight_score';

    const ID = 'score_id';

    const CODE       = 'code';
    const VALUE      = 'value';
    const URL        = 'url';
    const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCode($value);

    /**
     * @return int
     */
    public function getValue();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setUrl($value);

    /**
     * @return string
     */
    public function getCreatedAt();
}

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



namespace Mirasvit\OptimizeInsight\Service;

class PageSpeedService
{
    const STRATEGY_MOBILE  = 'mobile';
    const STRATEGY_DESKTOP = 'desktop';

    /**
     * @param string $url
     * @param string $strategy
     *
     * @return int [0...100]
     */
    public function getScore($url, $strategy)
    {
        $params = [
            'url'      => $url,
            'strategy' => $strategy,
            'locale'   => 'en_US',
        ];

        try {
            $url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?' . http_build_query($params);

            $data = file_get_contents($url);
            $data = \Zend_Json::decode($data);

            $score = $data['lighthouseResult']['categories']['performance']['score'];

            $score = floatval($score) * 100;

            return $score;
        } catch (\Exception $e) {
            return false;
        }
    }
}

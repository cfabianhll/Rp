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



namespace Mirasvit\OptimizeJs\Service;

use Mirasvit\OptimizeJs\Api\Data\BundleFileInterface;
use Mirasvit\OptimizeJs\Repository\BundleFileRepository;

class BundleFileService
{
    const DEFAULT_SCOPE_THRESHOLD = 0.80;

    /** @var BundleFileRepository  */
    private $bundleFileRepository;

    public function __construct(
        BundleFileRepository $bundleFileRepository
    ) {
        $this->bundleFileRepository = $bundleFileRepository;
    }

    /**
     * @param string $area
     * @param string|false $theme
     * @param string|false $locale
     *
     * @return array
     */
    public function getScopes($area, $theme, $locale)
    {
        $scopes = [
            'default' => [],
        ];

        try {
            // just check, that setup:upgrade was completed (deploy may run static-content and then upgrade)
            $this->bundleFileRepository->getCollection()->getSize();
        } catch (\Exception $e) {
            return $scopes;
        }

        $collection = $this->bundleFileRepository->getCollection();

        $collection->addFieldToFilter(BundleFileInterface::AREA, $area);

        if ($theme) {
            $collection->addFieldToFilter(BundleFileInterface::THEME, $theme);
        }

        if ($locale) {
            $collection->addFieldToFilter(BundleFileInterface::LOCALE, $locale);
        }

        $file2Layout = [];
        $layoutPool  = [];

        /** @var BundleFileInterface $bundleFile */
        foreach ($collection as $bundleFile) {
            $filename = $bundleFile->getFilename();
            $layout   = $bundleFile->getLayout();

            if (!isset($file2Layout[$filename])) {
                $file2Layout[$filename] = [];
            }

            $layoutPool[$layout] = $layout;
            $scopes[$layout]     = [];

            $file2Layout[$filename][] = $bundleFile->getLayout();
        }

        foreach ($file2Layout as $filename => $layouts) {
            if (count($layouts) / count($layoutPool) >= self::DEFAULT_SCOPE_THRESHOLD) {
                $scopes['default'][] = $filename;
            } else {
                foreach ($layouts as $layout) {
                    $scopes[$layout][] = $filename;
                }
            }
        }

        return $scopes;
    }
}

<?php
/**
 *  Copyright © 2018 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 */
namespace Magestore\ReportSuccess\Ui\Component;

/**
 * Class ExportButton override function prepare of core Magento
 */
class ExportButton extends \Magento\Ui\Component\ExportButton
{
    private $removeOptionXml = 'xml';
    private $removeOptionCvs = 'cvs';

    /**
     * Function prepare
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');

        if (isset($config['options'])) {
            $options = [];

            if (isset($config['options'][$this->removeOptionXml])) {
                unset($config['options'][$this->removeOptionXml]);
            }
            // option cvs removed from magento 2.3.2 covering compatible
            if (isset($config['options'][$this->removeOptionCvs])) {
                unset($config['options'][$this->removeOptionCvs]);
            }

            foreach ($config['options'] as $option) {
                $option['url'] = $this->urlBuilder->getUrl($option['url']);
                $options[] = $option;
            }

            $config['options'] = $options;
            $this->setData('config', $config);
        }

        parent::prepare();
    }
}

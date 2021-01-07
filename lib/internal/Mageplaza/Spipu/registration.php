<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Spipu
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
require_once dirname(__DIR__) . '/registration.php';

// Load Html2Pdf library
$loader = new MageplazaSplClassLoader('Spipu\Html2Pdf', realpath(__DIR__ . '/Html2Pdf/'));
$loader->register();

class Tcpdf_Autoload
{
    public static function autoload ($class) {
        $prefix = 'TCPDF';
        $baseDir = __DIR__ . '/vendor/tecnickcom/tcpdf/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = strtolower($class);
        $file = rtrim($baseDir, '/') . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
spl_autoload_register(['Tcpdf_Autoload', 'autoload']);

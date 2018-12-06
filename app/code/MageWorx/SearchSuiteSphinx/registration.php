<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'MageWorx_SearchSuiteSphinx',
    __DIR__
);

if (!class_exists('SphinxClient')) {
    @include_once 'lib' . DIRECTORY_SEPARATOR . 'Sphinx' . DIRECTORY_SEPARATOR . 'SphinxClient.php';
}
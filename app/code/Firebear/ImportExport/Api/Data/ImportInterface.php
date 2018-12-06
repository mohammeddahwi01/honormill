<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio GmbH. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Api\Data;

/**
 * Interface ImportInterface
 *
 * @package Firebear\ImportExport\Api\Data
 */
interface ImportInterface extends AbstractInterface
{
    const IMPORT_SOURCE = 'import_source';

    const MAP = 'map';

    /**
     * Get Import Source
     *
     * @return mixed
     */
    public function getImportSource();
    
    /**
     * @param $mapping
     *
     * @return serialize
     */
    public function getMapping();

    /**
     * @param string $source
     *
     * @return ImportInterface
     */
    public function setImportSource($source);
    
    /**
     * @param $mapping
     *
     * @return serialize
     */
    public function setMapping($mapping);
}

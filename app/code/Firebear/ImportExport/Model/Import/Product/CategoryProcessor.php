<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Product;


class CategoryProcessor extends \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
{
    /**
     * @param $rowData
     * @return array
     */
    public function getRowCategories($rowData, $separator)
    {
        $categoryIds = [];
        if (isset($rowData[\Firebear\ImportExport\Model\Import\Product::COL_CATEGORY])
            && $rowData[\Firebear\ImportExport\Model\Import\Product::COL_CATEGORY]) {
            if (!empty($rowData[\Firebear\ImportExport\Model\Import\Product::COL_CATEGORY])) {
                $catData = explode(
                    $separator,
                    $rowData[\Firebear\ImportExport\Model\Import\Product::COL_CATEGORY]
                );
                foreach ($catData as $cData) {
                    if ($cData == '/') {
                        continue;
                    }
                    $collection = $this->categoryColFactory->create()->addFieldToFilter('path', $cData);
                    $collectionId = $this->categoryColFactory->create()->addFieldToFilter('entity_id', $cData);
                    if ($collection->getSize()) {
                        $categoryIds[] = $collection->getFirstItem()->getId();
                    } elseif ($collectionId->getSize()) {
                        $categoryIds[] = $cData;
                    } else {
                        try {
                            $categoryIds[] = $this->upsertCategory($cData);
                        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                            $this->addFailedCategory($cData, $e);
                        }
                    }
                }
            }
        }

        return $categoryIds;
    }

    /**
     * @param string $category
     * @param \Magento\Framework\Exception\AlreadyExistsException $exception
     * @return $this
     */
    private function addFailedCategory($category, $exception)
    {
        $this->failedCategories[] =
            [
                'category' => $category,
                'exception' => $exception,
            ];
        return $this;
    }
}
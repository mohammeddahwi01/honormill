<?php
namespace MageWorx\OptionTemplates\Ui\Component\Listing\Column;

class Fastship extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('mageworx_optiontemplates_relation');
        
        $params = array();
        $url = $_SERVER['HTTP_REFERER'];
        $params = explode('/', $url);
        $groupIdKey = array_search('group_id', $params);
        $groupId = (isset($params[($groupIdKey+1)])) ? intval($params[($groupIdKey+1)]) : 0;
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['fast_ship'])) {
                continue;
            }
            
            $found = false;
            $productId = intval($item['entity_id']);
            $fastShip = intval($item['fast_ship']);
            $sql = "Select * FROM " . $tableName . " WHERE group_id=".$groupId." AND product_id=".$productId;
            $result = $connection->fetchAll($sql);

            $sql1 = "SELECT option_id FROM mageworx_optiontemplates_group_option WHERE group_id=".$groupId;
            $result1 = $connection->fetchAll($sql1);
            if(!empty($result) && count($result) && count($result1) <= 1) {
                $sql = "SELECT * FROM catalog_product_option_type_value WHERE is_stocktab=1 AND option_id=(
                    SELECT option_id FROM catalog_product_option WHERE product_id=".$productId." AND group_option_id=(
                        SELECT option_id FROM mageworx_optiontemplates_group_option WHERE group_id=".$groupId."
                    )
                )";
                $result2 = $connection->fetchAll($sql);
                if(isset($result2) && !empty($result2) && count($result2)) {
                    $found = true;
                }
            }
            if (count($result1) > 1) {
                $item['fast_ship'] = 'N/A';
            } else if($found) {
                $item['fast_ship'] = 'Yes';
            } else {
                $item['fast_ship'] = 'No';
            }
        }

        return $dataSource;
    }
}

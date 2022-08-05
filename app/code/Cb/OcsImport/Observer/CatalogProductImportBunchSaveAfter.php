<?php
namespace Cb\OcsImport\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;

class CatalogProductImportBunchSaveAfter implements ObserverInterface
{
    protected $_objectManager;
    protected $_resource;
    protected $greenlineHelper;
    protected $mpHelper;
    protected $mpProductFactory;
    protected $_date;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Cb\GreenlineApi\Helper\Data $greenlineHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        MpProductFactory $mpProductFactory,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    )
    {
        $this->_objectManager = $objectManager;
        $this->_resource = $resource;
        $this->greenlineHelper = $greenlineHelper;
        $this->mpHelper = $mpHelper;
        $this->mpProductFactory = $mpProductFactory;
        $this->indexFactory = $indexFactory;
        $this->indexCollection = $indexCollection;
        $this->_eventManager = $eventManager;
        $this->_date = $date;
    }

    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getData('bunch');
        foreach ($bunch as $rowNum => $rowData) {
            $sku = $rowData['sku'];
            
            $rowProduct = $this->greenlineHelper->getProductBySku($sku);

            if($rowProduct){
                $rowProductId = $rowProduct->getId();

                /*$connection  = $this->_resource->getConnection();
                $table = $connection->getTableName('url_rewrite');

                $query = "SELECT `request_path` FROM `".$table."` WHERE `entity_type` = 'product' AND `entity_id` = ".$productId." AND `target_path` LIKE 'catalog/product/view/id/'".$productId." LIMIT 1";
                
                $request_path = $connection->fetchOne($query);

                if($request_path != ''){
                    $url_key = explode(".html", $request_path);
                    $rowProduct->setData('url_key', $url_key);
                    $rowProduct->save();
                }*/

                $this->setProductUrlKey($rowProductId);

                $defaultSellerId = $this->greenlineHelper->getDefaultSellerId();

                $productCollection = $this->mpProductFactory->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    'mageproduct_id',
                                    $rowProductId
                                );

                if($productCollection->getSize() < 1){             
                    $mpProductModel = $this->mpProductFactory->create();
                    $mpProductModel->setMageproductId($rowProductId);
                    $mpProductModel->setSellerId($defaultSellerId);
                    $mpProductModel->setStatus($rowProduct->getStatus());
                    $mpProductModel->setAdminassign(1);
                    $mpProductModel->setIsApproved(1);
                    $mpProductModel->setCreatedAt($this->_date->gmtDate());
                    $mpProductModel->setUpdatedAt($this->_date->gmtDate());
                    $mpProductModel->save();
                }

                $rowProduct->cleanCache();
                $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $rowProduct]);
            }
        }

        $indexerCollection = $this->indexCollection->create();
        $indexids = $indexerCollection->getAllIds();
     
        foreach ($indexids as $indexid){
            $indexidarray = $this->indexFactory->create()->load($indexid);
     
            //If you want reindex all use this code.
             $indexidarray->reindexAll($indexid);
     
            //If you want to reindex one by one, use this code
             $indexidarray->reindexRow($indexid);
        }

        return $this;
    }


    public function setProductUrlKey($productId){

        $connection  = $this->_resource->getConnection();
        $table = $connection->getTableName('url_rewrite');

        $query = "SELECT `request_path` FROM `".$table."` WHERE `entity_type` = 'product' AND `entity_id` = ".$productId." AND `target_path` LIKE 'catalog/product/view/id/".$productId."' LIMIT 1";
        
        $request_path = $connection->fetchOne($query);

        if($request_path != ''){
            $urlParts = explode(".html", $request_path);
            $urlkey = $urlParts[0];

            $table = $connection->getTableName('eav_attribute');
            $table2 = $connection->getTableName('catalog_product_entity_varchar');

            $query = "SELECT `attribute_id` FROM `".$table."` WHERE `entity_type_id` = '4' AND `attribute_code` = 'url_key' LIMIT 1";

            $attribute_id = $connection->fetchOne($query);

            $data = ["value"=> $urlkey];
            $where = ['attribute_id = '.$attribute_id.' AND store_id = 0 AND entity_id ='.$productId];
            $connection->update($table2, $data, $where);
        }

        return $this;

    }

}
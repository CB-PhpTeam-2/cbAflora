<?php

namespace Cb\OcsImport\Plugin;

use Magento\Store\Model\Store;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class GeneratorUrlKey
{
    protected $_objectManager;
    protected $_storeManager;
    protected $_resource;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource
    ){
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_resource = $resource;
    }

    public function  afterGetUrlPath(\Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $subject, $result, $product, $category = null){

        $path = $product->getData('url_path');
        if ($path === null) {
            $urlkey = $result.'.html';
            $result = $this->getFinalUrlKey($product, $urlkey);
        }

        return $result;
    }


    public function  getFinalUrlKey($product, $urlkey){

        $connection  = $this->_resource->getConnection();
        $table = $connection->getTableName('url_rewrite');

        $query = "SELECT * FROM `".$table."` WHERE `entity_type` = 'product' AND `request_path` LIKE '$urlkey'";
        $collection = [];
        $collection = $connection->fetchAll($query);

        $parts = [];
        $parts = explode(".html", $urlkey);
        $baseUrl = $parts[0];
        if(sizeof($collection) > 0){
            $_parts = [];
            $_parts = explode("-", $baseUrl);
            $last = trim(end($_parts));

            $suffix = '';
            if(is_numeric($last)){
                array_pop($_parts);
                $baseUrl = implode("-", $_parts);
                $suffix = (int) $last + 1;
            }else{
                $suffix = 1;
            }            
            $final_urlkey = $baseUrl.'-'.$suffix.'.html';

            return $this->getFinalUrlKey($final_urlkey);
        }else{
            return $baseUrl;
        }

    }

}

<?php

namespace Cb\OcsImport\Model\Rewrite;

use Magento\Store\Model\Store;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductUrlPathGenerator
 */
class ProductUrlPathGenerator extends \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
{
    
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->productRepository = $productRepository;
    }

    protected function prepareProductUrlKey(Product $product)
    {
        $urlKey = (string)$product->getUrlKey();
        $urlKey = trim(strtolower($urlKey));

        $_urlKey = $urlKey ?: $product->formatUrlKey($product->getName());

        $_urlKey = $_urlKey.'.html';
        $finalUrlKey = $this->getFinalUrlKey($_urlKey, $product->getId());

        return $finalUrlKey;
    }

    protected function prepareProductDefaultUrlKey(Product $product)
    {
        $storedProduct = $this->productRepository->getById($product->getId());
        $storedUrlKey = $storedProduct->getUrlKey();
        $_storedUrlKey = $storedUrlKey ?: $product->formatUrlKey($storedProduct->getName());
        $_storedUrlKey = $_storedUrlKey.'.html';
        $finalUrlKey = $this->getFinalUrlKey($_storedUrlKey, $product->getId());

        return $finalUrlKey;
    }

    public function  getFinalUrlKey($urlkey, $productId = null){
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $connectionO = $om->create('Magento\Framework\App\ResourceConnection');
        $connection= $connectionO->getConnection();

        $table = $connection->getTableName('url_rewrite');

        $query = "SELECT * FROM `".$table."` WHERE `entity_type` = 'product' AND `request_path` LIKE '$urlkey'";
        $collection = [];
        $collection = $connection->fetchAll($query);

        $parts = [];
        $parts = explode(".html", $urlkey);
        $baseUrl = $parts[0];
        if(sizeof($collection) > 0){

            $query = "SELECT * FROM `".$table."` WHERE `entity_type` = 'product' AND `entity_id` = '".$productId."' AND `request_path` LIKE '$urlkey'";
            $_collection = [];
            $_collection = $connection->fetchAll($query);

            if(sizeof($_collection) > 0){
                return $baseUrl;
            }

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
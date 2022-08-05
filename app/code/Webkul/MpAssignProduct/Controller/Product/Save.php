<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    const DEFAULT_STORE_ID = 0;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Indexer\Model\IndexerFactory $indexFactory,
        \Magento\Indexer\Model\Indexer\CollectionFactory $indexCollection,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Cb\GreenlineApi\Helper\Data $moduleHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Gallery\Processor $imageProcessor,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $productGallery,
        MpProductFactory $mpProductFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->indexFactory = $indexFactory;
        $this->indexCollection = $indexCollection;
        $this->_assignHelper = $helper;
        $this->productCopier = $productCopier;
        $this->_moduleHelper = $moduleHelper;
        $this->mpHelper = $mpHelper;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->productFactory   = $productFactory;
        $this->imageProcessor = $imageProcessor;
        $this->productGallery = $productGallery;
        $this->mpProductFactory = $mpProductFactory;
        $this->_date = $date;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_assignHelper;
        $currentStoreId = $helper->getStoreId();
        $data = $this->getRequest()->getParams();
        $data['product_condition'] = 1;
        $data['image'] = '';
        if (!array_key_exists('product_id', $data)) {
            $this->messageManager->addError(__('Something went wrong.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        $productId = $data['product_id'];
        $newProductId = 0;
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $result = $helper->validateData($data, $productType);
        if ($result['error']) {
            $this->messageManager->addError(__($result['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        if (array_key_exists('assign_id', $data) && array_key_exists('assign_product_id', $data)) {
            $flag = 1;
            $newProductId = $data['assign_product_id'];
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        if (!$flag) {
            $sellerId = $this->mpHelper->getCustomerId();
            $shop_url = $this->_moduleHelper->getSellerUrlBySellerId($sellerId);
            
            $newProduct = $this->productCopier->copy($product);
            $sku = $newProduct->getBarcode().'-'.$shop_url;

            $newProduct->setSku($sku);
            $newProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $newProduct->save();
            $newProductId = $newProduct->getId();
            $data['assign_product_id'] = $newProductId;
            $this->removeImages($newProductId);
        }
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $attributData = [
          'status' => $status,
          'description' => $data['description']
        ];
        if ($productType != "configurable") {
            $attributData['price'] = $data['price'];
        }
        $helper->updateProductData([$newProductId], $attributData, $currentStoreId);
        $duplicateProduct = $helper->getProduct($newProductId);
        $sku = $duplicateProduct->getSku();
        if ($productType != "configurable") {
            $this->mpHelper->reIndexData();
            $this->updateStockData($sku, $data['qty'], 1);
        } else {
            $associateProducts = [];
            $updatedProducts = $this->addAssociatedProducts($newProductId, $data);
            $this->mpHelper->reIndexData();
            $data['products'] = $updatedProducts;
            foreach ($updatedProducts as $exProductId => $updatedData) {
                $associateProducts[] = $updatedData['new_product_id'];
                $this->updateStockData($updatedData['sku'], $updatedData['qty'], 1);
            }
            $duplicateProduct->setStatus($status);
            $duplicateProduct->setDescription($data['description']);
            $duplicateProduct->setAssociatedProductIds($associateProducts);
            $duplicateProduct->setCanSaveConfigurableAttributes(true);
            $duplicateProduct->save();
        }
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $this->adminStoreMediaImages($newProductId, $data, $currentStoreId);
            $helper->processProductStatus($result);

            $productCollection = $this->mpProductFactory->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    'mageproduct_id',
                                    $newProductId
                                );
            if($productCollection->getSize() < 1){
                $sellerId = $this->mpHelper->getCustomerId();              
                $mpProductModel = $this->mpProductFactory->create();
                $mpProductModel->setMageproductId($newProductId);
                $mpProductModel->setSellerId($sellerId);
                $mpProductModel->setStatus($duplicateProduct->getStatus());
                $mpProductModel->setAdminassign(1);
                $mpProductModel->setIsApproved(1);
                $mpProductModel->setCreatedAt($this->_date->gmtDate());
                $mpProductModel->setUpdatedAt($this->_date->gmtDate());
                $mpProductModel->save();
            }

            /*$productIds = [$newProductId];
            $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute', 'cataloginventory_stock', 'inventory', 'catalogsearch_fulltext', 'catalog_product_price', 'catalogrule_product', 'catalogrule_rule'];

            foreach ($indexLists as $indexList) {
               $categoryIndexer = $this->_objectManager->get('Magento\Framework\Indexer\IndexerRegistry')->get($indexList);

                if (!$categoryIndexer->isScheduled()) {
                    try {
                       $categoryIndexer->reindexList(array_unique($productIds));
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }*/
            $indexerCollection = $this->indexCollection->create();
            $indexids = $indexerCollection->getAllIds();
         
            foreach ($indexids as $indexid){
                $indexidarray = $this->indexFactory->create()->load($indexid);
         
                //If you want reindex all use this code.
                 $indexidarray->reindexAll($indexid);
         
                //If you want to reindex one by one, use this code
                 $indexidarray->reindexRow($indexid);
            }

            $duplicateProduct->cleanCache();
            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $duplicateProduct]);

            $this->messageManager->addSuccess(__('Product is saved successfully.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/productlist');
        } else {
            $this->messageManager->addError(__('There was some error while processing your request.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/add', ['id' => $data['product_id']]);
        }
    }

    public function saveAssignedProduct($data)
    {
        $helper = $this->_assignHelper;
        $currentStoreId = $helper->getStoreId();
        $data['image'] = '';
        $productId = $data['product_id'];
        $newProductId = 0;
        $product = $helper->getProduct($productId);
        $productType = $product->getTypeId();
        $result = $helper->validateData($data, $productType);
        if ($result['error']) {
            $result['error'] = $result['msg'];
        }

        if (array_key_exists('assign_id', $data) && array_key_exists('assign_product_id', $data)) {
            $flag = 1;
            $newProductId = $data['assign_product_id'];
        } else {
            $flag = 0;
            $data['del'] = 0;
        }
        if (!$flag) {

            $sellerId = $this->mpHelper->getCustomerId();
            $shop_url= $this->_moduleHelper->getSellerUrlBySellerId($sellerId);

            $newProduct = $this->productCopier->copy($product);
            
            $sku = $newProduct->getBarcode().'-'.$shop_url;
            $newProduct->setSku($sku);
            $newProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $newProduct->save();
            $newProductId = $newProduct->getId();
            $data['assign_product_id'] = $newProductId;
            $this->removeImages($newProductId);
        }
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $attributData = [
          'status' => $status,
          'description' => $data['description']
        ];
        if ($productType != "configurable") {
            $attributData['price'] = $data['price'];
        }
        $helper->updateProductData([$newProductId], $attributData, $currentStoreId);
        $duplicateProduct = $helper->getProduct($newProductId);
        $sku = $duplicateProduct->getSku();
        if ($productType != "configurable") {
            $this->mpHelper->reIndexData();
            $this->updateStockData($sku, $data['qty'], 1);
        } else {
            $associateProducts = [];
            $updatedProducts = $this->addAssociatedProducts($newProductId, $data);
            $this->mpHelper->reIndexData();
            $data['products'] = $updatedProducts;
            foreach ($updatedProducts as $exProductId => $updatedData) {
                $associateProducts[] = $updatedData['new_product_id'];
                $this->updateStockData($updatedData['sku'], $updatedData['qty'], 1);
            }
            $duplicateProduct->setStatus($status);
            $duplicateProduct->setDescription($data['description']);
            $duplicateProduct->setAssociatedProductIds($associateProducts);
            $duplicateProduct->setCanSaveConfigurableAttributes(true);
            $duplicateProduct->save();
        }
        $result = $helper->processAssignProduct($data, $productType, $flag);
        if ($result['assign_id'] > 0) {
            $this->adminStoreMediaImages($newProductId, $data, $currentStoreId);
            $helper->processProductStatus($result);

            $productCollection = $this->mpProductFactory->create()
                                ->getCollection()
                                ->addFieldToFilter(
                                    'mageproduct_id',
                                    $newProductId
                                );
            if($productCollection->getSize() < 1){
                $sellerId = $this->mpHelper->getCustomerId();              
                $mpProductModel = $this->mpProductFactory->create();
                $mpProductModel->setMageproductId($newProductId);
                $mpProductModel->setSellerId($sellerId);
                $mpProductModel->setStatus($duplicateProduct->getStatus());
                $mpProductModel->setAdminassign(1);
                $mpProductModel->setIsApproved(1);
                $mpProductModel->setCreatedAt($this->_date->gmtDate());
                $mpProductModel->setUpdatedAt($this->_date->gmtDate());
                $mpProductModel->save();
            }

            $productIds = [$newProductId];
            $indexLists = ['catalog_category_product', 'catalog_product_category', 'catalog_product_attribute', 'cataloginventory_stock', 'inventory', 'catalogsearch_fulltext', 'catalog_product_price', 'catalogrule_product', 'catalogrule_rule'];

            foreach ($indexLists as $indexList) {
                $categoryIndexer = $this->_objectManager->get('Magento\Framework\Indexer\IndexerRegistry')->get($indexList);

                if (!$categoryIndexer->isScheduled()) {
                    try {
                       $categoryIndexer->reindexList(array_unique($productIds));
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }

            $duplicateProduct->cleanCache();
            $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $duplicateProduct]);

        } else {
            $result['error'] = 1;
        }
        return $result;
    }

    /**
     * Update Stock Data of Product
     *
     * @param integer $productId
     * @param integer $qty
     * @param integer $isInStock
     */
    public function updateStockData($sku, $qty = 0, $isInStock = 0)
    {
        try {
            $socpeConfiguration = $this->stockConfiguration;
            $scopeId = $socpeConfiguration->getDefaultScopeId();
            $stockRegistry = $this->stockRegistry;
            $stockItem = $stockRegistry->getStockItemBySku($sku, $scopeId);
            $stockItem->setData('is_in_stock', $isInStock);
            $stockItem->setData('qty', $qty);
            $stockItem->setData('manage_stock', 1);
            $stockItem->setData('use_config_notify_stock_qty', 1);
            $stockRegistry->updateStockItemBySku($sku, $stockItem);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage));
        }
    }
    /**
     * addAssociatedProducts for the configurable Products
     * @param int $productId [Product id of configurable Product]
     * @param mixed $data
     */
    public function addAssociatedProducts($productId, $data)
    {
        $helper = $this->_assignHelper;
        $storeId = $helper->getStoreId();
        $updatedProducts = [];
        if (isset($data['products'])) {
            foreach ($data['products'] as $existingProductId => $associatedProductData) {
                if (isset($associatedProductData['assign_product_id']) && $associatedProductData['assign_product_id']) {
                    $associatedProductData['new_product_id'] = $associatedProductData['assign_product_id'];
                    $product = $helper->getProduct($associatedProductData['assign_product_id']);
                    $associatedProductData['sku'] = $product->getSku();
                    if ($product->getPrice() != $associatedProductData['price']) {
                        $attributData = [
                        'price' => $associatedProductData['price']
                        ];
                        $helper->updateProductData([$product->getId()], $attributData, $storeId);
                    }
                    $updatedProducts[$existingProductId] = $associatedProductData;
                } else {
                    if ($associatedProductData['qty'] && $associatedProductData['price']) {
                        $product = $helper->getProduct($existingProductId);
                        $newProduct = $this->productCopier->copy($product);
                        $attributData = [
                        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                        'price' => $associatedProductData['price']
                        ];
                        $helper->updateProductData([$newProduct->getId()], $attributData, $storeId);
                        $associatedProductData['new_product_id'] = $newProduct->getId();
                        $associatedProductData['sku'] = $newProduct->getSku();
                        $updatedProducts[$existingProductId] = $associatedProductData;
                    }
                }
            }
        }
        return $updatedProducts;
    }
    /**
     * removeImages function is used to remove images of the assigned Product.
     */
    protected function removeImages($productId, $storeId = 0)
    {
        try {
            $product = $this->productRepository->getById(
                $productId,
                true
            );
            $images = $product->getMediaGalleryImages();
            foreach ($images as $child) {
                $this->imageProcessor->removeImage($product, $child->getFile());
                $this->productGallery->deleteGallery($child->getValueId());
            }
            $product->save();
          
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * adminStoreMediaImages function is used to store the images of the assigned Product.
     */
    protected function adminStoreMediaImages($productId, $wholedata, $storeId = 0)
    {
        if (!empty($wholedata['product']['media_gallery'])) {
            $catalogProduct = $this->productFactory->create()->load(
                $productId
            );
            $catalogProduct->addData($wholedata['product'])->save();
        }
    }
}

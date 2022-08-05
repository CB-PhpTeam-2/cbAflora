<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpHyperLocal\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Webkul\MpHyperLocal\Helper\Data as MpHelper;
use Webkul\MpHyperLocal\Model\ProductFactory as MpProductModel;
use Webkul\MpHyperLocal\Helper\Data as HyperLocalHelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;

/**
 * Seller Product's Collection Block.
 */
class Collection extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productlists;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $stringUtils;

    protected $storeManager;
	
	protected $helperData;
	
	protected $mpHelperData;
	
	protected $request;
	
	protected $redirect;

    protected $_session;
	
    /**
     * @param \Magento\Catalog\Block\Product\Context    $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Framework\Url\Helper\Data        $urlHelper
     * @param CollectionFactory                         $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver     $layerResolver
     * @param CategoryRepositoryInterface               $categoryRepository
     * @param \Magento\Framework\Stdlib\StringUtils     $stringUtils
     * @param MpHelper                                  $mpHelper
     * @param MpProductModel                            $mpProductModel
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		HyperLocalHelperData $helperData,
		MarketplaceHelperData $mpHelperData,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Session\SessionManagerInterface $session,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->stringUtils = $stringUtils;
        $this->_resource = $resource;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
		$this->helperData = $helperData;
		$this->mpHelperData = $mpHelperData;
		$this->request = $request;
		$this->redirect = $redirect;
        $this->_session = $session;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $title = __('Search  Collection');
        
        $this->pageConfig->getTitle()->set($title);

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $title) {
            $pageMainTitle->setPageTitle($title);
        }

        $this->pageConfig->addRemotePageAsset(
            $this->_urlBuilder->getCurrentUrl(''),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
        
        return $this;
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function _getProductCollection()
    {
        if (!$this->_productlists) {
			
            $paramData = $this->getRequest()->getParams();
            unset($paramData['step1']);
            unset($paramData['step2']);
            unset($paramData['step3']);
            
            $querydata = $this->getCustomCollection();
            
            $filterIds = [];
            foreach ($querydata as $key => $filterRow) {
                $filterIds[] = $filterRow['entity_id'];
            }
            
			$allowedProductIds = [];
            if($this->request->getFullActionName() == 'mphyperlocal_vendor_collection'){
				$sellerId = 0;
                if($this->mpHelperData->isSeller() == 1){
                    $sellerId = $this->mpHelperData->getCustomerId();
                }else if($this->getVisitorViewSellerId() != 0){
                    $sellerId = $this->getVisitorViewSellerId();
                }
				
                if($sellerId != 0){
                    $sellerIds = [$sellerId];
                    $allowedProductIds = $this->helperData->getNearestProducts($sellerIds);
                }
            }else{
                $savedAddress = $this->helperData->getSavedAddress();
                if ($savedAddress) {
                    $sellerIds = $this->helperData->getNearestSellers();
                    $allowedProductIds= $this->helperData->getNearestProducts($sellerIds);
                }
            }

			$resultIds = [];
			$resultIds = array_intersect($filterIds,$allowedProductIds);
			$resultIds = array_values($resultIds);
			
            $layer = $this->getLayer();

            $origCategory = null;
            if (isset($paramData['c']) || isset($paramData['cat'])) {
                try {
                    if (isset($paramData['c'])) {
                        $catId = $paramData['c'];
                    }
                    if (isset($paramData['cat'])) {
                        $catId = $paramData['cat'];
                    }
                    $category = $this->_categoryRepository->get($catId);
                } catch (\Exception $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }

            $collection = $layer->getProductCollection();

            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter(
                'entity_id',
                ['in' => $resultIds]
            );

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            $this->_productlists = $collection;

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
            $toolbar = $this->getToolbarBlock();
            $this->configureProductToolbar($toolbar, $collection);

            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );
        }
        $this->_productlists->getSize();

        return $this->_productlists;
    }

    /**
     * Configures the Toolbar block for sorting related data.
     *
     * @param ProductList\Toolbar $toolbar
     * @param ProductCollection $collection
     * @return void
     */
    public function configureProductToolbar(Toolbar $toolbar, ProductCollection $collection)
    {
        $availableOrders = $this->getAvailableOrders();
        if ($availableOrders) {
            $toolbar->setAvailableOrders($availableOrders);
        }
        $sortBy = $this->getSortBy();
        if ($sortBy) {
            $toolbar->setDefaultOrder($sortBy);
        }
        $defaultDirection = $this->getDefaultDirection();
        if ($defaultDirection) {
            $toolbar->setDefaultDirection($defaultDirection);
        }
        $sortModes = $this->getModes();
        if ($sortModes) {
            $toolbar->setModes($sortModes);
        }
        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);
    }

    public function getDefaultDirection()
    {
        return 'asc';
    }

    public function getSortBy()
    {
        return 'entity_id';
    }

    public function getCustomCollection()
    {
        $params['step1'] = $this->getRequest()->getParam('step1');
        $params['step2'] = $this->getRequest()->getParam('step2');
        $params['step3'] = $this->getRequest()->getParam('step3');

        $collection = [];
        $params = array_filter($params);
        if(sizeof($params) > 0){
            $storeId = $this->storeManager->getStore()->getId();
            if($storeId == ''): $storeId = 1; endif;
            $connection = $this->_resource->getConnection();
            $table = $connection->getTableName('catalog_product_flat_'.$storeId);

            $queryHtml = '';
            foreach ($params as $key => $param) {
                
                $backendData = $this->getBackendData($param);
                if(sizeof($backendData) > 0){
                    $data = $backendData[0];
                    $backendLabel = $data['backend_label'];
                    $attributeCode = $data['attribute_code'];

                    $backendLabelArray = [];
                    $backendLabelArray = explode(",", $backendLabel);
                    $backendValues = [];
                    
                    foreach ($backendLabelArray as $key => $backendLabel) {
                        $backendValues[] = $this->getOptionIdByLabel($attributeCode,$backendLabel);
                    }
                    
                    $backendValues = array_filter($backendValues);
                    if(sizeof($backendValues) > 0){
                        $backendValuesString = implode(",", $backendValues);

                        if($queryHtml != ''){
                            $queryHtml .= ' AND ';
                        }

                        if(sizeof($backendValues) > 1){
                            $subQueryHtml = '';
                            foreach ($backendValues as $key => $_value) {
                                if($subQueryHtml != ''){
                                    $subQueryHtml .= ' OR ';
                                }
                              $subQueryHtml .= "`".$attributeCode."` LIKE '%".$_value."%'";
                            }
                            $queryHtml .= '('.$subQueryHtml;
                            $queryHtml .= " OR `".$attributeCode."` LIKE '%".$backendValuesString."%')";
                        }else{
                            $queryHtml .= "(`".$attributeCode."` LIKE '%".$backendValuesString."%')";
                        }
                    }
                }
            }

            $query = "SELECT * FROM `".$table."` WHERE ".$queryHtml;
            $collection = $connection->fetchAll($query);
        }

        return $collection;
    }

    /* Get Backend Label by Frontend Label */
    public function getBackendData($step1)
    {
        $connection = $this->_resource->getConnection();
        $table = $connection->getTableName('recommendation_mapping_values');

        $query= "SELECT * FROM `".$table."` WHERE `frontend_label` LIKE '%".$step1."%'";
        $backendData = $connection->fetchAll($query);

        return $backendData;
    }
 
   /* Get Option id by Option Label */
    public function getOptionIdByLabel($attributeCode,$optionLabel)
    {
        $product = $this->productFactory->create();
        $isAttributeExist = $product->getResource()->getAttribute($attributeCode);
        $optionId = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionId = $isAttributeExist->getSource()->getOptionId($optionLabel);
        }
        return $optionId;
    }
	
	/* Get Referer Url */
    public function getRefererUrl()
    {
		return $this->redirect->getRefererUrl();
	}

    public function getVisitorViewSellerId()
    {
        $sellerId = 0;
        $this->_session->start();
        if($this->_session->getData('visitor_view_seller_id')){
            $sellerId = $this->_session->getData('visitor_view_seller_id');
        }
        return $sellerId;
    }
}

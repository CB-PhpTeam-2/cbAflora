<?php

/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\Config;

use Webkul\AdvancedLayeredNavigation\Model\CarouselFilterAttributes;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

  /**
   * @var Config $eavConfig
   */
    private $eavConfig;

    /**
     * @var Config $eavConfig
     */

    public $html = '';

    /**
     * @var Config $eavConfig
     */

    public $catHelper;
	
	protected $redirect;
    /**
     * @param Context $context
     * @param Config $eavConfig
     */

    /**
    * @var PriceCurrencyInterface
    */
    protected $priceCurrency;

    public function __construct(
        Context $context,
        Config $eavConfig,
        \Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilter\Collection $carouselFactory,
        CarouselFilterAttributes $carouselAttributesFactory,
        \Magento\Store\Model\StoreManagerInterface $store,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
		\Magento\Framework\App\Response\RedirectInterface $redirect,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productRepository = $productRepository;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->store = $store;
        $this->productVisibility = $productVisibility;
        $this->eavAttribute = $eavAttribute;
        $this->_attributeOptionCollection = $attributeOptionCollection;
        $this->stock = $stockFilter;
        $this->productloader = $productloader;
        $this->catHelper = $catalogHelper;
        $this->eavConfig = $eavConfig;
        $this->categoryFactory = $categoryFactory;
        $this->carouselAttributesFactory = $carouselAttributesFactory;
        $this->carouselFactory = $carouselFactory;
        $this->productCollection = $productCollection;
		$this->redirect = $redirect;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
    }

    public function getOptions($attribute)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attribute);
        $options = $attribute->getSource()->getAllOptions();
        unset($options[0]);
        return $options;
    }

  /**
   * get admin configuration values
   *
   * @return array
   */
    public function getConfigure()
    {
        $config = [];
        if ($this->scopeConfig->getValue('advancedlayerednavigation/multi_assign/choice_attribute_item', 'websites')) {
            $backgroundColor = $this->scopeConfig->getValue(
                'advancedlayerednavigation/multi_assign/backgroundColor',
                'websites'
            );
            $imageWidth = $this->scopeConfig->getValue(
                'advancedlayerednavigation/multi_assign/imageWidth',
                'websites'
            );
            $imageHeight = $this->scopeConfig->getValue(
                'advancedlayerednavigation/multi_assign/imageHeight',
                'websites'
            );
            $borderColor = $this->scopeConfig->getValue(
                'advancedlayerednavigation/multi_assign/borderColor',
                'websites'
            );
            $config['updateHeight'] = $imageHeight;
            $config['clicktop'] = $config['updateHeight']/2 - 7;
            $config['backgroundColor'] = $backgroundColor;
            $config['borderColor'] = $borderColor;
            $config['imageWidth'] = $imageWidth;
            $config['imageHeight'] = $imageHeight;
            $config['enable'] = true;
        } else {
            $config['enable'] = false;
        }
        return $config;
    }

    public function cacheFlush()
    {
        $types = ['block_html','full_page','layout','config','collections',
        'reflection','db_ddl','eav','config_integration','config_integration_api',
        'translate','config_webservice'];
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
    public function getCategoryCarouselImages($categoryId)
    {
        $inventory =  $this->productCollection->create()->getTable('cataloginventory_stock_item');
        $category = $this->categoryFactory->create()->load($categoryId);
        $categoryIds = [];
        if ($category->getAllChildren()) {
            $categoryIds = explode(',', $category->getAllChildren());
        } else {
            $categoryIds[] = $categoryId;
        }
        $carousel =  $this->carouselFactory->getTable('wk_layered_carousel_options');
        $allCarsoulImage = [];
        $model = $this->carouselAttributesFactory->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('categories', $categoryId)
                    ->addFieldToFilter('enable', 1);
        $model->getSelect()->join($carousel.' as ca', "main_table.entity_id = ca.carousel_id");
        foreach ($model->getData() as $mod) {
            $data = $this->productCollection->create();
            $data->addFieldToFilter($mod['attribute_code'], $mod['attribute_option_id']);
            $data->addCategoriesFilter(['in' => $categoryIds]);
            $data->addAttributeToFilter(
                'status',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            );
        
            $data->getSelect()->join($inventory.' as stock', "e.entity_id = stock.product_id")
            ->where('stock.is_in_stock=1');
            if ($data->getSize() > 0) {
                $allCarsoulImage[] =  $mod;
            }
        }
        
        return $allCarsoulImage;
    }
   
    public function getCategory($categoryId)
    {
        $category = $this->categoryFactory->create();
        $category->load($categoryId);
        return $category;
    }

    public function getCategoryProducts($categoryId)
    {
       
        $inventory =  $this->productCollection->create()->getTable('cataloginventory_stock_item');
        $products = $this->getCategory($categoryId)->getProductCollection();
        $products->addAttributeToSelect('*');
        $products->getSelect()->join($inventory.' as stock', "e.entity_id = stock.product_id")
        ->where('stock.is_in_stock=1');
        $products->addAttributeToFilter('status', 1);
        $products->addFieldToFilter('visibility', 4);
        return count($products->getData());
    }

    public function getSubCategory($categoryId)
    {
        $category = $this->categoryFactory->create()->load($categoryId);
        $childrenCategories = $category->getChildren();
        $categorySub = [];
        $categories = [];
        if ($childrenCategories) {
            $categorySub = explode(',', $childrenCategories);
            foreach ($categorySub as $sub) {
                $count =  $this->getCategoryProducts($sub);
                if ($count) {
                    $categories[] = $sub;
                }
            }
        }
        return $categories;
    }
    public function subCat($i, $j)
    {
        return '<span class="plus" id="plus-'.$i.$j.'">+</span>';
    }
    
    public function cate($catId, $selected_value, $i, $j)
    {
        $html ='';
        $z = 1;
        if ($j == 1) {
            $z = 1;
        }
        $subCategories =[];
        $subCategories = $this->getSubCategory($catId);
        if ($subCategories) {
             $html .= '<ol class="child'.$i.$j.' fffff" style="display:none;">';
            foreach ($subCategories as $id) {
                $productCount = $this->getCategoryProducts($id);
                if ($productCount > 0) {
                    $categoryDetail = $this->getCategory($id);
                    $html .= '<li class="my item">';
                    $count = 0;
                    $subCategories1 = [];
                    
                    $subCategories1 = $this->getSubCategory($id);
               
                    if (!empty($subCategories1)) {
                        $z = $j++;
                        $html .= '<span class="plus gfgg '.(json_encode($subCategories1)).'"
                         id="plus-'.$i.$z.'">+</span>';
                        $count = 1;
                    }
                    $html .= '<label class="layered-navigation-label " for="'.$id.'" 
                    data-url="'.$categoryDetail->getRequestPath().'">';
                    $checked = '';
                    if (in_array($id, $selected_value)) {
                        $checked = 'checked';
                    }
                    $html .= '<input data-attrname="cat" '.$checked.'  class="layered_attrs" id="'.$id.'" 
                    type="checkbox"/>'.$categoryDetail->getName().'';
                    if ($this->catHelper->shouldDisplayProductCountOnLayer()):
                        $html .= '<span class="count">'.$productCount.'<span class="filter-count-label">';
                        if ($productCount == 1):
                            $html .= __('item');
                        else:
                            $html .= __('items');
                        endif;
            
                        $html .= '</span></span></label>';
                    endif;
                    if ($count) {
                        $j = $j++;
                        $html .= $this->cate($id, $selected_value, $i, $j);
                        $html .= '</ol>';
                    } else {
                        $j = $j++;
                    }
                
                    $html .= '</li>';
                }
            }
        }
        return $html;
    }
    public function getRatingTypeView()
    {
        return $this->scopeConfig->getValue(
            'advancedlayerednavigation/multi_assign/rating_display',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /* Get Label by option id */
    public function getOptionLabelByValue($attributeCode, $optionId)
    {
        $_product = $this->productloader->create();
        $isAttributeExist = $_product->getResource()->getAttribute($attributeCode);
        $optionText = '';
        if ($isAttributeExist && $isAttributeExist->usesSource()) {
            $optionText = $isAttributeExist->getSource()->getOptionText($optionId);
        }
        return $optionText;
    }
	
	public function getFullAction()
	{
		return $this->request->getFullActionName();
	}
	
	public function getRootCategoryId()
    {
        return $this->store->getStore()->getRootCategoryId();
    }

    /**
    * result
    *
    * @return string
    */
    public function getCurrencySymbol()
    {
       // DEFAULT STORE
       $currencySymbol = $this->priceCurrency->getCurrencySymbol();
       return $currencySymbol;
    }
}

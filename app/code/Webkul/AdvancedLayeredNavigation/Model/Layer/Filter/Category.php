<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Category as AbstractFilter;
use Magento\Framework\Registry;

/**
 * Layer category filter
 */
class Category extends AbstractFilter
{
    /**
     * Active Category Id
     *
     * @var int
     */
    protected $_categoryId;

    /**
     * Applied Category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $_appliedCategory;

    /**
     * Core data
     *
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
 /**
  * @var CategoryModel
  */
    private $category;

    /**
     * @var int
     */
    private $categoryId;
    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;
 /**
  * @var Registry
  */
    private $coreRegistry;
    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param CategoryFactory $categoryDataProviderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        Registry $coreRegistry,
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        CategoryFactory $categoryDataProviderFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->categoryFactory = $categoryFactory;
        $this->coreRegistry = $coreRegistry;
        $this->_escaper = $escaper;
        $this->_requestVar = 'cat';
        $this->_coreSession = $coreSession;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
    }

    /**
     * Get filter value for reset current filter state
     *
     * @return mixed|null
     */
    public function getResetValue()
    {
        return $this->dataProvider->getResetValue();
    }

    /**
     * @return \Magento\Framework\App\RequestInterface
     */
    protected function _getRequest()
    {
        return $this->_request;
    }

    /**
     * Apply category filter to layer
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
        $categoryId = $request->getParam($this->getRequestVar());
        if (!$categoryId) {
            return $this;
        }

        $categoryAll = explode('_', $categoryId);
        $categoryIds = [];
        foreach ($categoryAll as $catId) {
            $category = $this->categoryFactory->create()->load($catId);
           
            if ($category->getAllChildren()) {
                $childCat = explode(',', $category->getAllChildren());
                foreach ($childCat as $child) {
                    if (!in_array($child, $categoryIds)) {
                        $categoryIds[] = $child;
                    }
                }
            } else {
                if (!in_array($catId, $categoryIds)) {
                    $categoryIds[] = $catId;
                }
            }
        }
       
        foreach ($categoryIds as $key => $id) {
            $this->dataProvider->setCategoryId($id);
            if ($this->dataProvider->isValid()) {
                $category = $this->dataProvider->getCategory();
                if ($request->getParam('id') != $id) {
                    if (count($categoryIds) == 1) {
                        $this->getLayer()->getProductCollection()->addCategoryFilter($category);
                    }
                    if (in_array($id, $categoryAll)) {
                         $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $id));
                    }
                }
            }
        }

        if (count($categoryIds) > 1) {
            $coll = $this->getLayer()->getProductCollection();

            if (!empty($categoryIds)) {
                $this->_isFilter = true;
                $coll->addCategoriesFilter(['in' => $categoryIds]);
            }
        }

        return $this;
    }

    /**
     * Get filter name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getName()
    {
        return __('Category');
    }

     /**
      * Get selected category object
      *
      * @return CategoryModel
      */
    public function getCategory()
    {
    
        if ($this->category === null) {
            /** @var CategoryModel|null $category */
            $category = null;
           
            if ($category === null || !$category->getId()) {
                $category = $this->getLayer()
                    ->getCurrentCategory();
            }

            $this->coreRegistry->register('current_category_filter', $category, true);
            $this->category = $category;
        }

        return $this->category;
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $query = '';
        if ($this->_getRequest()->getParam('q')) {
            $query = $this->_getRequest()->getParam('q');
        }

        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        $optionsFacetedData = $productCollection->getFacetedData('category');
        $category           = $this->getCategory();
        $categories         = $category->getChildrenCategories();
        $collectionSize = $productCollection->getSize();
        $categoryData = [];
        $filterAvailable = false;
        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                $category = $category->getData();
                $categoryData[] = $category;
                $count = isset($optionsFacetedData[$category['entity_id']]) ?
                 $optionsFacetedData[$category['entity_id']]['count'] : 0;
                if ($category['is_active']
                    && $count) {
                    $filterAvailable = true;
                    $this->itemDataBuilder->addItemData(
                        $this->_escaper->escapeHtml($category['name']),
                        $category['entity_id'],
                        $count
                    );
                }
            }
        }

        $sessionOptionsFacetedData = $this->_coreSession->getData('facted_data_category'.$query);
        $categoriesList = $this->_coreSession->getData('facted_data_categorylist'.$query);

        if (!$filterAvailable && $this->_getRequest()->getParam($this->_requestVar) && (!empty($categoriesList))) {
            if ($this->dataProvider->getCategory()->getIsActive()) {
                foreach ($categoriesList as $category) {
                    $count = isset($sessionOptionsFacetedData[$category['entity_id']]) ?
                    $sessionOptionsFacetedData[$category['entity_id']]['count'] : 0;
                    if ($category['is_active']
                        && $count) {
                        $this->itemDataBuilder->addItemData(
                            $this->_escaper->escapeHtml($category['name']),
                            $category['entity_id'],
                            $count
                        );
                    }
                }
            }
        }
        
        if (!empty($categoryData)) {
            $this->_coreSession->setData('facted_data_category'.$query, $optionsFacetedData);
            $this->_coreSession->setData('facted_data_categorylist'.$query, $categoryData);
        }

        return $this->itemDataBuilder->build();
    }
}

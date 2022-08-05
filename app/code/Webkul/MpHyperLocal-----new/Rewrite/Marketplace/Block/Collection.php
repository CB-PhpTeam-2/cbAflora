<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpHyperLocal\Rewrite\Marketplace\Block;

use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * Seller Product's Collection Block.
 */
class Collection extends \Webkul\Marketplace\Block\Collection
{
    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function _getProductCollection()
    {
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $partner = $this->getProfileDetail();
            try {
                $sellerId = $partner->getSellerId();
            } catch (\Exception $e) {
                $sellerId = 0;
            }

            $productname = $this->getRequest()->getParam('name');
            $inventorySourceItem = $this->mpProductModel->create()
                                        ->getCollection()
                                        ->getTable('inventory_source_item');
            $querydata = $this->mpProductModel->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        )
                        ->addFieldToFilter(
                            'status',
                            ['eq' => 1]
                        )
                        ->addFieldToSelect('mageproduct_id')
                        ->setOrder('mageproduct_id');

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
                ['in' => $querydata->getData()]
            );
            if (isset($paramData['outlet']) && $paramData['outlet']) {
                $outlet = $paramData['outlet'];
                $collection->getSelect()
                ->join(
                    ['ist' => $inventorySourceItem],
                    'e.sku = ist.sku'
                )->where("ist.source_code = '".$outlet."'");
            }
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
        $productname = $this->getRequest()->getParam('name');
        if ($productname !="") {
            $this->_productlists->addAttributeToFilter(
                [
                [
                    'attribute' => 'name',
                    'like' => '%'.$productname.'%'
                ],
                [
                    'attribute' => 'sku',
                    'like' => $productname.'%'
                ]
                ]
            );
        }
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
        $productname = $this->getRequest()->getParam('name');
        if ($productname !="") {
            $collection->addAttributeToFilter(
                [
                    [
                        'attribute' => 'name',
                        'like' => '%'.$productname.'%'
                    ],
                    [
                        'attribute' => 'sku',
                        'like' => $productname.'%'
                    ]
                ]
            );
        }
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
}

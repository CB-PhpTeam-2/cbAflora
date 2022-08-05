<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Plugin\Catalog\Model;

class Collection
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
    protected $_productStoreId;

    /**
     * @var \Webkul\MpAssignProduct\Model\AssociatesFactory
     */
    protected $associatesFactory;

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
        $this->_storeManager = $storeManager;
        $this->resource = $resource;
        $this->_conn = $resource->getConnection();
    }

    /**
     * Plugin for getProductCollection
     *
     * @param \Magento\Catalog\Model\Layer $subject
     * @return $result
     */
    public function afterLoadProductCount(
        \Magento\Catalog\Model\ResourceModel\Category\Collection $subject,
        $items,
        $countRegular,
        $countAnchor,
        $result
    ) {
        $assignProductsIds = $this->helper->getCollection()->getAllIds();
        $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
        $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
        if (!empty($assignProductsIds)) {
            
            $anchor = [];
            $regular = [];
            $websiteId = $this->_storeManager->getStore($this->getProductStoreId())->getWebsiteId();

            foreach ($items as $item) {
                if ($item->getIsAnchor()) {
                    $anchor[$item->getId()] = $item;
                } else {
                    $regular[$item->getId()] = $item;
                }
            }

            if ($countRegular) {
                // Retrieve regular categories product counts
                $regularIds = array_keys($regular);
                
                if (!empty($regularIds)) {
                    
                    $select = $this->_conn->select();
                    
                    $select->from(
                        ['main_table' => 'catalog_category_product'],
                        ['category_id', new \Zend_Db_Expr('COUNT(main_table.product_id)')]
                    )->where(
                        $this->_conn->quoteInto('main_table.category_id IN(?)', $regularIds).
                        'AND main_table.product_id NOT IN (\'' . implode("', '", $assignProductsIds) . "' )"
                    )->group(
                        'main_table.category_id'
                    );
                    if ($websiteId) {
                        $select->join(
                            ['w' => 'catalog_product_website'],
                            'main_table.product_id = w.product_id',
                            []
                        )->where(
                            'w.website_id = ?',
                            $websiteId
                        );
                    }
                    $counts = $this->_conn->fetchPairs($select);
                    foreach ($regular as $item) {
                        if (isset($counts[$item->getId()])) {
                            $item->setProductCount($counts[$item->getId()]);
                        } else {
                            $item->setProductCount(0);
                        }
                    }
                }
            }

            if ($countAnchor) {
                // Retrieve Anchor categories product counts
                foreach ($anchor as $item) {
                    if ($allChildren = $item->getAllChildren()) {
                        $bind = ['entity_id' => $item->getId(), 'c_path' => $item->getPath() . '/%'];
                        $select = $this->_conn->select();
                        $select->from(
                            ['main_table' => 'catalog_category_product'],
                            new \Zend_Db_Expr('COUNT(DISTINCT main_table.product_id)')
                        )->joinInner(
                            ['e' => ('catalog_category_entity')],
                            'main_table.category_id=e.entity_id',
                            []
                        )->where(
                            '(e.entity_id = :entity_id OR e.path LIKE :c_path) AND
                            main_table.product_id NOT IN (\'' . implode("', '", $assignProductsIds) . "' )"
                        );
                        if ($websiteId) {
                            $select->join(
                                ['w' => 'catalog_product_website'],
                                'main_table.product_id = w.product_id',
                                []
                            )->where(
                                'w.website_id = ?',
                                $websiteId
                            );
                        }
                        $item->setProductCount((int)$this->_conn->fetchOne($select, $bind));
                    } else {
                        $item->setProductCount(0);
                    }
                }
            }
            return $this;
        }
        return $result;
    }
    /**
     * Get id of the store that we should count products on
     *
     * @return int
     */
    public function getProductStoreId()
    {
        if ($this->_productStoreId === null) {
            $this->_productStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        return $this->_productStoreId;
    }
}

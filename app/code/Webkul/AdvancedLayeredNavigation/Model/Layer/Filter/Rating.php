<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter as AbstractFilter;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryFactory as CategoryModelFactory;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Layer rating filter
 */
class Rating extends AbstractFilter
{
    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceConnection $resource
     */
    private $resource;
    /**
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resource
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resource,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
        $this->objectManager = $objectManager;
        $this->escaper = $escaper;
        $this->_productModel = $productModel;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_requestVar = 'rat';
        $this->scopeConfig = $scopeConfig;
        $this->resource = $resource;
    }

    /**
     * @var array
     */
    protected $stars = [
        1 => 20,
        2 => 40,
        3 => 60,
        4 => 80,
        5 => 100,
    ];

    /**
     * Apply category filter to layer
     *
     * @param   \Magento\Framework\App\RequestInterface $request
     * @return  $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $applyJoin = true;
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $rating = $request->getParam($this->getRequestVar());
        if (!$rating) {
            return $this;
        }
        $collection = $this->getLayer()->getProductCollection();
        $select = $collection->getSelect();
        $ratings = explode("_", $rating);
        $i = 0;
        $query ='';
        if (!empty($ratings)) {
            foreach ($ratings as $rat) {
                if ($i >0) {
                    $query .= '||';
                }
                $minRating = (array_key_exists($rat, $this->stars)) ? $this->stars[$rat] : 0;
                $query .= '`rating`.`rating_summary` >='.$minRating;
                $i++;
            }
        } else {
            $query .= '`rating`.`rating_summary` >= 0';
        }
        $reviewSummary = $this->resource->getTableName('review_entity_summary');
        $partFrom = $collection->getSelect()->getPart('from');
        foreach ($partFrom as $key => $dataValue) {
            if ($key == 'rating') {
                $applyJoin = false;
            }
        }
        if ($applyJoin) {
            $select->joinLeft(
                ['rating' => $reviewSummary],
                sprintf(
                    '`rating`.`entity_pk_value`=`e`.entity_id
                    AND `rating`.`entity_type` = 1
                    AND `rating`.`store_id`  =  %d',
                    $this->_storeManager->getStore()->getId()
                ),
                ''
            );
        }
        $select->where('`rating`.`reviews_count` > 0 && '.$query);
        $ratings = explode('_', $rating);
        if (!empty($ratings)) {
            foreach ($ratings as $value) {
                $state = $this->_createItem($this->getLabelHtml($value), $value)
                            ->setVar($this->_requestVar);
                $this->getLayer()->getState()->addFilter($state);
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
        return __('Rating');
    }

    /**
     * @return array
     */
    protected function _getItemsData()
    {
        $data = [];
        $count = $this->_getCount();

        $currentValue = $this->getRequestVar();
        for ($i=4; $i>=1; $i--) {
            $data[] = [
                'label' => $this->getLabelHtml($i),
                'value' => ($currentValue == $i) ? null : $i,
                'count' => $count[($i-1)],
                'option_id' => $i,
            ];
        }
        return $data;
    }

    /**
     * @return array
     */
    protected function _getCount()
    {
        $collection = $this->getLayer()->getProductCollection();
        $connection = $collection->getConnection();
        $connection->query('SET @ONE :=0, @TWO := 0, @THREE := 0, @FOUR := 0, @FIVE := 0');
        $select = clone $collection->getSelect();
        $select->reset(\Zend_Db_Select::COLUMNS);
        $select->reset(\Zend_Db_Select::ORDER);
        $select->reset(\Zend_Db_Select::LIMIT_COUNT);
        $select->reset(\Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(\Zend_Db_Select::WHERE);

        $reviewSummary = $this->resource->getTableName('review_entity_summary');

        $select->joinLeft(
            ['rsc' => $reviewSummary],
            sprintf(
                '`rsc`.`entity_pk_value`=`e`.entity_id
                AND `rsc`.entity_type = 1
                AND `rsc`.store_id  =  %d',
                $this->_storeManager->getStore()->getId()
            ),
            'rsc.rating_summary AS rating'
        );
        
        $columns = new \Zend_Db_Expr("
            IF(`rsc`.`rating_summary` >= 20, @ONE := @ONE + 1, 0),
            IF(`rsc`.`rating_summary` >= 40, @TWO := @TWO + 1, 0),
            IF(`rsc`.`rating_summary` >= 60, @THREE := @THREE + 1, 0),
            IF(`rsc`.`rating_summary` >= 80, @FOUR := @FOUR + 1, 0)
            
        ");
        $select->columns($columns);
        $connection->query($select);
        $result = $connection->fetchRow('SELECT @ONE, @TWO, @THREE, @FOUR');
        return array_values($result);
    }

    /**
     * @return array
     */
    protected function _initItems()
    {
        $data  = $this->_getItemsData();
        $items = [];
        if ($this->scopeConfig->getValue('advancedlayerednavigation/multi_assign/show_ratings_item', 'websites')) {
            foreach ($data as $itemData) {
                $item = $this->_createItem(
                    $itemData['label'],
                    $itemData['value'],
                    $itemData['count']
                );
                $item->setOptionId($itemData['option_id']);
                $items[] = $item;
            }
        }
        $this->_items = $items;
        return $this;
    }

    /**
     * get label
     *
     * @param int $countStars
     * @return string
     */
    protected function getLabelHtml($countStars)
    {
        $width = (int)$countStars*20;
        $str = __(' & up');
        $html = '<div class="rating-summary" style="display: inline-block;margin-top: -5px;">
                                        <div class="rating-result" title="100%">
                                            <span style="width:'.$width.'%">
                                            <span class="wk-count">'.$countStars.' stars</span></span>
                                        </div>'.$str.'
                                    </div>';
        return $html;
    }
}

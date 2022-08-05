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

namespace Webkul\MpHyperLocal\Block;

use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\ProductFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Webkul MpHyperLocal Storelist Block.
 */
class Storelist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $_sellerlistCollectionFactory;

    /** @var \Webkul\Marketplace\Model\Seller */
    protected $sellerList;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var ProductFactory
     */
    protected $productModel;

    /**
     * @var CollectionFactory
     */
    protected $sellerCollection;

    /**
     * @var \Webkul\MpHyperLocal\Model\OutletFactory
     */
    protected $outletModel;

    /**
     * @param Context                                    $context
     * @param array                                      $data
     * @param MpHelper                                   $mpHelper
     * @param ProductFactory                             $productModel
     * @param CollectionFactory                          $sellerCollection
     * @param \Webkul\MpHyperLocal\Model\OutletFactory   $outletModel
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
        MpHelper $mpHelper,
        ProductFactory $productModel,
        CollectionFactory $sellerCollection,
        \Webkul\MpHyperLocal\Helper\Data $helper,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletModel,
        array $data = []
    ) {
        $this->_sellerlistCollectionFactory = $sellerlistCollectionFactory;
        $this->mpHelper = $mpHelper;
        $this->productModel = $productModel;
        $this->sellerCollection = $sellerCollection;
        $this->helper = $helper;
        $this->outletModel = $outletModel;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getSellerCollection()
    {
        if (!$this->sellerList) {
            $helper = $this->mpHelper;
            $sellerArr = [];
            $savedAddress = $this->helper->getSavedAddress();
            if ($savedAddress) {
                $sellerArr = $this->helper->getNearestSellers();
            } else {
                $sellerProductColl = $this->productModel->create()
                ->getCollection()
                ->addFieldToFilter(
                    'status',
                    ['eq' => 1]
                )
                ->addFieldToSelect('seller_id')
                ->distinct(true);
                $sellerArr = $sellerProductColl->getAllSellerIds();
            }
            $storeCollection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'seller_id',
                ['in' => $sellerArr]
            )
            ->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            )->addFieldToFilter(
                'store_id',
                $helper->getCurrentStoreId()
            )->setOrder(
                'entity_id',
                'desc'
            );
            $storeSellerIDs = $storeCollection->getAllIds();
            $storeMainSellerIDs = $storeCollection->getAllSellerIds();

            $sellerArr = array_diff($sellerArr, $storeMainSellerIDs);

            $adminStoreCollection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'seller_id',
                ['in' => $sellerArr]
            );
            if (!empty($storeSellerIDs)) {
                $adminStoreCollection->addFieldToFilter(
                    'entity_id',
                    ['nin' => $storeSellerIDs]
                );
            }
            $adminStoreCollection->addFieldToFilter(
                'is_seller',
                ['eq' => 1]
            )->addFieldToFilter(
                'store_id',
                0
            )->setOrder(
                'entity_id',
                'desc'
            );
            $adminStoreSellerIDs = $adminStoreCollection->getAllIds();

            $allSellerIDs = array_merge($storeSellerIDs, $adminStoreSellerIDs);

            $collection = $this->_sellerlistCollectionFactory
            ->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'entity_id',
                ['in' => $allSellerIDs]
            )->setOrder(
                'entity_id',
                'desc'
            );
            $websiteId = $helper->getWebsiteId();
            $joinTable = $this->sellerCollection->create()->getTable('customer_grid_flat');
            $collection->getSelect()->join(
                $joinTable.' as cgf',
                'main_table.seller_id = cgf.entity_id AND website_id= '.$websiteId
            );
            $this->sellerList = $collection;
        }

        return $this->sellerList;
    }

    public function getOutletCollection()
    {
        $helper = $this->mpHelper;
        $storeId = $helper->getCurrentStoreId();
        list($sellerArr, $userIds) = $this->helper->getNearestOutlets($storeId);
        $sellerArr = implode(",", $sellerArr);
        $userIds = implode(",", $userIds);
        $marketplaceUserData = $this->outletModel->create()
                                    ->getCollection()
                                    ->getTable('marketplace_userdata');
        $outletModel = $this->outletModel->create()
                            ->getCollection()
                            ->addFieldToFilter('status', 1);
        $outletModel->getSelect()->join(
            $marketplaceUserData.' as cgf',
            'main_table.seller_id = cgf.seller_id AND cgf.entity_id IN ('.$userIds.')',
            [
                'shop_url' => 'shop_url',
                'shop_title' => 'shop_title',
                'logo_pic' => 'logo_pic'
            ]
        )->where('main_table.entity_id IN ('.$sellerArr.')');
        return $outletModel;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getSellerCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.seller.list.pager'
            )
            ->setCollection(
                $this->getSellerCollection()
            );
            $this->setChild('pager', $pager);
            $this->getSellerCollection()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get URL for ajax call
     *
     * @return string
     */
    public function getStoreUrl()
    {
        return $this->getUrl(
            'mphyperlocal/outlet/listajax',
            [
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }
}

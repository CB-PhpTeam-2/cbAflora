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
namespace Webkul\MpHyperLocal\Plugin\Helper;

use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\Marketplace\Model\Product as SellerProduct;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Data
{
    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @var MpProductCollection
     */
    protected $mpProductCollectionFactory;

    /**
     * @var ProductCollection
     */
    protected $productCollectionFactory;

    /**
     * @param MpHelper $mpHelper
     * @param \Webkul\MpHyperLocal\Helper\Data $helper
     * @param MpProductCollection $mpProductCollectionFactory
     * @param ProductCollection $productCollectionFactory
     */
    public function __construct(
        MpHelper $mpHelper,
        \Webkul\MpHyperLocal\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        MpProductCollection $mpProductCollectionFactory,
        ProductCollection $productCollectionFactory
    ) {
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->request = $request;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function aroundGetSellerProCount(
        \Webkul\Marketplace\Helper\Data $subject,
        callable $proceed,
        $sellerId
    ) {
        if ($this->helper->isEnabled()) {
            $params = $this->request->getParams();
            if (isset($params['outlet']) && $params['outlet']) {
                $outlet = $params['outlet'];
                $inventorySourceItem = $this->_mpProductCollectionFactory->create()->getTable('inventory_source_item');
                $querydata = $this->_mpProductCollectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED])
                ->addFieldToSelect('mageproduct_id')
                ->setOrder('mageproduct_id');
                $collection = $this->_productCollectionFactory->create();
                $collection->addAttributeToSelect('*');
                $collection->addAttributeToFilter('entity_id', ['in' => $querydata->getData()]);
                $collection->addAttributeToFilter('visibility', ['in' => [4]]);
                $collection->addAttributeToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED]);
                $collection->getSelect()
                ->join(
                    ['ist' => $inventorySourceItem],
                    'e.sku = ist.sku'
                )->where("ist.source_code = '".$outlet."'");
                $collection->addStoreFilter();
                return $collection->getSize();
            }
        }
        return $proceed($sellerId);
    }
}

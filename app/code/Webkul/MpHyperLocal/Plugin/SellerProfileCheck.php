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
namespace Webkul\MpHyperLocal\Plugin;

use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\Marketplace\Helper\Data as MpHelper;

class SellerProfileCheck
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    private $hyperLocalHelper;
    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var MpProductCollection
     */
    protected $mpProductCollectionFactory;

    /**
     * @var ProductCollection
     */
    protected $productCollectionFactory;

    public function __construct(
        MpHelper $mpHelper,
        \Webkul\MpHyperLocal\Helper\Data $hyperLocalHelper,
        \Magento\Framework\App\RequestInterface $request,
        MpProductCollection $mpProductCollectionFactory,
        ProductCollection $productCollectionFactory
    ) {
        $this->mpHelper = $mpHelper;
        $this->hyperLocalHelper = $hyperLocalHelper;
        $this->request = $request;
        $this->mpProductCollection = $mpProductCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    public function aroundGetBestsellProducts(
        \Webkul\Marketplace\Block\Profile $list,
        callable $proceed
    ) {
        $params = $this->request->getParams();
        if ($this->hyperLocalHelper->isEnabled() &&
        isset($params['outlet']) &&
        $params['outlet']
        ) {
            $products = [];
            $partner = $list->getProfileDetail();
            if ($partner) {
                $outlet = $params['outlet'];
                $catalogProductWebsite = $this->mpProductCollection->create()->getTable('catalog_product_website');
                $inventorySourceItem = $this->mpProductCollection->create()->getTable('inventory_source_item');
                $helper = $this->mpHelper;
                if (count($helper->getAllWebsites()) == 1) {
                    $websiteId = 0;
                } else {
                    $websiteId = $helper->getWebsiteId();
                }
                $querydata = $this->mpProductCollection->create()
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $partner->getSellerId()]
                                    )
                                    ->addFieldToFilter(
                                        'status',
                                        ['neq' => 2]
                                    )
                                    ->addFieldToSelect('mageproduct_id')
                                    ->setOrder('mageproduct_id');
                $products = $this->_productCollectionFactory->create();
                $products->addAttributeToSelect('*');
                $products->addAttributeToFilter('entity_id', ['in' => $querydata->getAllIds()]);
                $products->addAttributeToFilter('visibility', ['in' => [4]]);
                $products->addAttributeToFilter('status', 1);
                if ($websiteId) {
                    $products->getSelect()
                    ->join(
                        ['cpw' => $catalogProductWebsite],
                        'cpw.product_id = e.entity_id'
                    )->where(
                        'cpw.website_id = '.$websiteId
                    );
                }
                $products->getSelect()
                ->join(
                    ['ist' => $inventorySourceItem],
                    'e.sku = ist.sku'
                )->where("ist.source_code = '".$outlet."'");
                $products->setPageSize(4)->setCurPage(1)->setOrder('entity_id');
            }

            return $products;
        }
        return $proceed();
    }
}

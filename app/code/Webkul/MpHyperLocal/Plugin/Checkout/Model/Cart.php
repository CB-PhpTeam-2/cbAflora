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
namespace Webkul\MpHyperLocal\Plugin\Checkout\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\SellerFactory;

class Cart
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param \Webkul\MpHyperLocal\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Webkul\MpHyperLocal\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        ProductRepositoryInterface $productRepository,
        SellerFactory $sellerFactory,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletModel
    ) {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->mpHelper = $mpHelper;
        $this->productRepository = $productRepository;
        $this->sellerFactory = $sellerFactory;
        $this->outletModel = $outletModel;
    }

    /**
     * Plugin for addProduct
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        if (isset($requestInfo['outlet_id'])) {
            return [$productInfo, $requestInfo];
        }
        $outletId = '';
        $product = $this->_getProduct($productInfo);
        if ($product === null) {
            return [$productInfo, $requestInfo];
        }
        $productId = $product->getId();
        $sellerId = $this->mpHelper->getSellerIdByProductId(
            $productId
        );
        $sellerData = $this->sellerFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('seller_id', $sellerId)
                                ->addFieldToFilter(
                                    ['latitude','longitude'],
                                    [
                                        ['neq'=> 'NULL'],
                                        ['neq'=> 'NULL']
                                    ]
                                )->getFirstItem();
        $radius = $sellerData->getRadius();
        $shipArea['latitude'] = $sellerData->getLatitude();
        $shipArea['longitude'] = $sellerData->getLongitude();
        if (!$this->helper->isInRadious($shipArea, $radius)) {
            $outletModel = $this->outletModel->create()
                                ->getCollection()
                                ->addFieldToFilter('seller_id', $sellerId)
                                ->addFieldToFilter('status', 1);
            foreach ($outletModel as $outlet) {
                $shipArea['latitude'] = $outlet->getLatitude();
                $shipArea['longitude'] = $outlet->getLongitude();
                if ($this->helper->isInRadious($shipArea, $radius)) {
                    $outletId = $outlet->getSourceCode();
                    break;
                }
            }
        }
        if ($outletId && $outletId != '') {
            $requestInfo['outlet_id'] = $outletId;
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Get product object based on requested product information
     *
     * @param   Product|int|string $productInfo
     * @return  Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }
        return $product;
    }
}

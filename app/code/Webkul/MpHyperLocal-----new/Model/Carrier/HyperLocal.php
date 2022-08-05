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
namespace Webkul\MpHyperLocal\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Backend\App\Action;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Session\SessionManager;
use Webkul\MpHyperLocal\Model\ShipRateFactory;
use Magento\Quote\Model\Quote\Item\OptionFactory as ItemOptionFactory;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Webkul\MpHyperLocal\Logger\Logger;
use Webkul\MpHyperLocal\Helper\Data as HyperLocalHelperData;
use Webkul\Marketplace\Model\SellerFactory as SellerFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Hyper local shipping model
 */
class HyperLocal extends AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var string
     */
    private $code = 'mplocalship';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Mage nto\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipRateFactory
     */
    private $shipRate;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    private $itemOption;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    private $mpProduct;

    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpHyperLocal\Logger\Logger
     */
    private $customlogger;

    /**
     * @var Object
     */
    private $objectManager;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    
    /**
     * @param Action\Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param Psr\Log\LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Magento\Catalog\Model\ProductFactory $productFactory
     * @param Magento\Checkout\Model\Session $checkoutSession
     * @param SessionManager $coreSession,
     * @param ShipRateFactory $shipRate,
     * @param Curl $curl,
     * @param AddressRepositoryInterface $addressRepository,
     * @param ItemOptionFactory $itemOption,
     * @param MpProductFactory $mpProduct,
     * @param HyperLocalHelperData $helperData,
     * @param Logger $customlogger
     * @param array $data
     */

    public function __construct(
        Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        SessionManager $coreSession,
        ShipRateFactory $shipRate,
        Curl $curl,
        AddressRepositoryInterface $addressRepository,
        ItemOptionFactory $itemOption,
        MpProductFactory $mpProduct,
        HyperLocalHelperData $helperData,
        Logger $customlogger,
        SellerFactory $sellerFactory,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        $this->objectManager = $context->getObjectManager();
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productFactory = $productFactory;
        $this->checkoutSession =$checkoutSession;
        $this->coreSession = $coreSession;
        $this->shipRate = $shipRate;
        $this->curl = $curl;
        $this->itemOption = $itemOption;
        $this->mpProduct = $mpProduct;
        $this->addressRepository = $addressRepository;
        $this->helperData = $helperData;
        $this->logger = $customlogger;
        $this->sellerFactory = $sellerFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlagCustom('active')
            || $this->_scopeConfig->getValue('carriers/mp_multishipping/active')
            || !$this->helperData->isEnabled()) {
            return false;
        }
        
        $this->setRequest($request);

        $shippingpricedetail = $this->getShippingPricedetail($this->_rawRequest);
        $result = $this->rateResultFactory->create();

        if ($shippingpricedetail['errormsg'] !== '') {
            // Display error message if there
            if ($this->getConfigFlagCustom('showmethod')) {
                $this->_errors[$this->code] = $shippingpricedetail['errormsg'];
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->code);
                $error->setCarrierTitle($this->getConfigFlagCustom('name'));
                $error->setErrorMessage($shippingpricedetail['errormsg']);
                return $error;
            }
            return false;
        }
        /*store shipping in session*/
        $shippingAll = $this->checkoutSession->getShippingInfo();
        $shippingAll[$this->code] = $shippingpricedetail['shippinginfo'];
        $this->checkoutSession->setShippingInfo($shippingAll);
        /*store shipping in session*/
        $method = $this->rateMethodFactory->create();
        $method->setCarrier($this->code);
        $method->setCarrierTitle($this->getConfigFlagCustom('name'));
        /* Use method name */
        $method->setMethod($this->code);
        $method->setMethodTitle($this->getConfigFlagCustom('name'));
        $method->setCost($shippingpricedetail['handlingfee']);
        $method->setPrice($shippingpricedetail['handlingfee']);
        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['mplocalship' => $this->getConfigFlagCustom('allowed_methods')];
    }

    /**
     * Prepare and set request to this instance.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $this->_request = $request;
        $requestData = new \Magento\Framework\DataObject();
        $product = $this->productFactory->create();
        $shippingdetail = [];
        $handling = 0;
        foreach ($request->getAllItems() as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $proid = $item->getProductId();
            $partner = $this->getProductSellerId($item);
            $weight = $this->getItemTotalWeight($item);
            if (empty($shippingdetail)) {
                array_push(
                    $shippingdetail,
                    [
                        'seller_id' => $partner,
                        'items_weight' => $weight,
                        'product_name' => $item->getName(),
                        'item_id' => $item->getId(),
                    ]
                );
            } else {
                $shipinfoflag = true;
                $index = 0;
                foreach ($shippingdetail as $itemship) {
                    if ($itemship['seller_id'] == $partner) {
                        $itemship['items_weight'] = $itemship['items_weight'] + $weight;
                        $itemship['product_name'] = $itemship['product_name'].','.$item->getName();
                        $itemship['item_id'] = $itemship['item_id'].','.$item->getId();
                        $shippingdetail[$index] = $itemship;
                        $shipinfoflag = false;
                    }
                    ++$index;
                }
                if ($shipinfoflag == true) {
                    array_push(
                        $shippingdetail,
                        [
                            'seller_id' => $partner,
                            'items_weight' => $weight,
                            'product_name' => $item->getName(),
                            'item_id' => $item->getId(),
                        ]
                    );
                }
            }
        }

        if ($request->getShippingDetails()) {
            $shippingdetail = $request->getShippingDetails();
        }
        $requestData->setShippingDetails($shippingdetail);
        $requestData->setDestCountryId($request->getDestCountryId());

        if ($request->getDestPostcode()) {
            $requestData->setDestPostal(str_replace('-', '', $request->getDestPostcode()));
        }
        $this->setRawRequest($requestData);

        return $this;
    }

    /**
     * getProductSellerId
     * @param Magento\Quote\Model\Quote\Item $item
     * @return int
     */
    private function getProductSellerId($item)
    {
        $mpAssignProId = 0;
        $sellerId = 0;
        $itemOption = $this->itemOption->create()->getCollection()
                                    ->addFieldToFilter('item_id', ['eq' => $item->getId()])
                                    ->addFieldToFilter('code', ['eq' => 'info_buyRequest'])
                                    ->setPageSize(1)->getFirstItem();
        $optionValue = '';
        if ($itemOption->getEntityId()) {
            $optionValue = $itemOption->getValue();
            if (!empty($optionValue)) {
                $temp = $this->jsonHelper->jsonDecode($optionValue);
                $mpAssignProId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
            }
        } else {
            foreach ($item->getOptions() as $option) {
                if (isset($option['value']) && is_array($option['value'])) {
                    $temp = $this->jsonHelper->jsonDecode($option['value']);
                    $mpAssignProId = isset($temp['mpassignproduct_id']) ? $temp['mpassignproduct_id'] : 0;
                }
            }
        }
        if ($mpAssignProId) {
            $mpassignModel = $this->objectManager->create(\Webkul\MpAssignProduct\Model\Items::class)
                                                    ->load($mpAssignProId);
            $sellerId = $mpassignModel->getSellerId();
        } else {
            $sellerProduct = $this->mpProduct->create()->getCollection()
                                                ->addFieldToFilter('mageproduct_id', ['eq' => $item->getProductId()])
                                                ->setPageSize(1)->getFirstItem();
            if ($sellerProduct->getEntityId()) {
                $sellerId = $sellerProduct->getSellerId();
            }
        }
        return $sellerId;
    }

    /**
     * getItemTotalWeight
     * @param Magento\Quote\Model\Quote\Item $item
     * @return float
     */
    private function getItemTotalWeight($item)
    {
        $weight = 0;
        if ($item->getHasChildren()) {
            $childWeight = 0;
            $productType = $item->getProduct()->getTypeId();
            if ($productType == 'bundle') {
                foreach ($item->getChildren() as $child) {
                    $productWeight = $this->getProductWeight($child->getProductId());
                    $childWeight += $productWeight * $child->getQty();
                }
                $weight = $childWeight * $item->getQty();
            } elseif ($productType == 'configurable') {
                foreach ($item->getChildren() as $child) {
                    $productWeight = $this->getProductWeight($child->getProductId());
                    $weight = $productWeight * $item->getQty();
                }
            }
        } else {
            $weight = $item->getWeight() * $item->getQty();
        }
        return $weight;
    }

    /**
     * getProductWeight
     * @param int $proId
     * @return float
     */
    private function getProductWeight($proId)
    {
        return $this->productFactory->create()->load($proId)->getWeight();
    }

    /**
     * Calculate the rate according to Tabel Rate shipping defined by the sellers.
     *
     * @return Result
     */
    public function getShippingPricedetail(\Magento\Framework\DataObject $request)
    {
        $requestData = $request;
        $shippinginfo = [];
        $handling = 0;

        if ($request->getDestPostcode() == '') {
            $destAddress = $this->helperData->getSavedAddress();
        } else {
            $destAddress = $request->getDestRegionCode()."+".$request->getDestPostcode()
                            ."+".$request->getDestCountryId();
        }
        foreach ($requestData->getShippingDetails() as $shipdetail) {
            $distance = $this->getDistanceFromBuyer($shipdetail['seller_id'], $destAddress).',';
            $shipRate = $this->getShipRate($shipdetail, $distance);

            $submethod = $shipRate['sub-method'];
            $handling = $handling + $submethod[0]['cost'];
            if (isset($shipRate['msg']) && $shipRate['msg'] == '') {
                array_push(
                    $shippinginfo,
                    [
                        'seller_id' => $shipdetail['seller_id'],
                        'methodcode' => $this->code,
                        'shipping_ammount' => $submethod[0]['cost'],
                        'product_name' => $shipdetail['product_name'],
                        'submethod' => $submethod,
                        'item_ids' => $shipdetail['item_id'],
                    ]
                );
            }
        }

        $shippingAll = $this->coreSession->getShippingInfo();
        $shippingAll[$this->code] = $shippinginfo;
        $this->coreSession->setShippingInfo($shippingAll);

        return ['handlingfee' => $handling, 'shippinginfo' => $shippinginfo, 'errormsg' => $shipRate['msg']];
    }

    /**
     * getDistanceFromBuyer
     * @param int $sellerId
     * @param string $shippingAddress
     */

    private function getDistanceFromBuyer($sellerId, $shippingAddress)
    {
        try {
            if ($sellerId) {
                $sellerInfo = $this->sellerFactory->create()->getCollection()
                                                        ->addFieldToFilter('seller_id', $sellerId)
                                                        ->setPageSize(1)->getFirstItem();
                if ($sellerInfo->getLatitude() && $sellerInfo->getLongitude()) {
                    $from = ['latitude' => $sellerInfo->getLatitude(), 'longitude' => $sellerInfo->getLongitude()];
                } else {
                    $latitude = $this->_scopeConfig->getValue('mphyperlocal/general_settings/latitude');
                    $longitude = $this->_scopeConfig->getValue('mphyperlocal/general_settings/longitude');
                    $from = ['latitude' => $latitude, 'longitude' => $longitude];
                }
                $to = isset($shippingAddress['latitude']) ? $shippingAddress : $this->getLocation($shippingAddress);
                $radiousUnit = $this->_scopeConfig->getValue('mphyperlocal/general_settings/radious_unit');
                return $this->helperData->getDistanceFromTwoPoints($from, $to, $radiousUnit);
            } else {
                //If seller not set address then hyper local origin address will use as pickup address
                return 0;
            }
        } catch (\Exception $e) {
            $this->logger->info('getDistanceFromBuyer :'.$e->getMessage());
            throw new StateException(__($e->getMessage()));
        }
    }

    /**
     * getLocation
     * @param string $address
     * @return array
     */

    private function getLocation($address)
    {
        try {
            $address = str_replace(' ', '+', $address);
            $address = str_replace('++', '+', $address);
            $apiKey = $this->helperData->getGoogleApiKey();
            $url = 'http://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&sensor=false&key='.$apiKey;
            $this->curl->get($url);
            $response = $this->jsonHelper->jsonDecode($this->curl->getBody());
            $location = $response['results'][0]['geometry']['location'];
            return [ 'latitude' => $location['lat'], 'longitude' => $location['lng']];
        } catch (\Exception $e) {
            $this->logger->info('getLocation :'.json_encode($address).' is invalid'.$e->getMessage());
            $msg = __('Please set your location.');
            if ($address) {
                $msg = __($address.' is invalid');
            }
            throw new StateException($msg);
        }
    }
    
    /**
     * @param \Magento\Framework\DataObject|null $request
     * @return $this
     * @api
     */
    public function setRawRequest($request)
    {
        $this->_rawRequest = $request;
        return $this;
    }

    /**
     * getShipRate
     * @param attay $shipdetail
     * @param float $distance
     * @return array
     */

    private function getShipRate($shipdetail, $distance)
    {
        $submethod  = false;
        $shipRate = false;
        $msg = '';
        if ($shipdetail['seller_id']) {
            $shipRate = $this->shipRate->create()->getCollection()
                                    ->addFieldToFilter('seller_id', ['eq' => $shipdetail['seller_id']])
                                    ->addFieldToFilter('distance_from', ['lteq' => (float)$distance])
                                    ->addFieldToFilter('distance_to', ['gteq' => (float)$distance])
                                    ->addFieldToFilter('weight_from', ['lteq' => $shipdetail['items_weight']])
                                    ->addFieldToFilter('weight_to', ['gteq' => $shipdetail['items_weight']])
                                    ->setPageSize(1)->getFirstItem();
        } else {
            $shipRate = $this->shipRate->create()->getCollection()
                                    ->addFieldToFilter('seller_id', ['eq' => 0])
                                    ->addFieldToFilter('distance_from', ['lteq' => (float)$distance])
                                    ->addFieldToFilter('distance_to', ['gteq' => (float)$distance])
                                    ->addFieldToFilter('weight_from', ['lteq' => $shipdetail['items_weight']])
                                    ->addFieldToFilter('weight_to', ['gteq' => $shipdetail['items_weight']])
                                    ->setPageSize(1)->getFirstItem();
        }
        
        if ($shipRate && $shipRate->getEntityId()) {
            $price = floatval($shipRate->getCost());
            $submethod = [
                [
                    'method' => $this->getConfigFlagCustom('name'),
                    'cost' =>  $price,
                    'base_amount' => $price,
                    'error' => 0
                ]
            ];
        } else {
            $msg = 'Seller Of Product '.$shipdetail['product_name'].' Not Provide Shipping Service At Your Location.';
            $submethod = [
                [
                    'method' => $this->getConfigFlagCustom('name'),
                    'cost' => 0,
                    'base_amount' => 0,
                    'error' => 1,
                    'base_amount' => 0
                ]
            ];
        }
        return ['sub-method' => $submethod, 'msg' => $msg];
    }

    /**
     * getConfigFlagCustom
     * @param string $code
     * @param string
     */
    private function getConfigFlagCustom($code)
    {
        return $this->_scopeConfig->getValue('carriers/'.$this->code.'/'.$code);
    }
}

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
namespace Webkul\MpHyperLocal\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Catalog\Model\ProductFactory;
use Webkul\Marketplace\Model\ProductFactory as MpProductFactory;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Framework\HTTP\Client\Curl;

class Data extends AbstractHelper
{
    const ALLOW_SINGLE_SELLER = 'mphyperlocal/general_settings/allow_single_seller';
	
	/**
     * @var StoreManagerInterface
     */
    protected $storeConfig;

    /**
     * @var CurrencyFactory
     */
    protected $currencyCode;
	
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Framework\Json\Helper\Data as JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    protected $shipAreaFactory;

    protected $_urlInterface;

    /**
     * @var SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var Curl
     */
    protected $curl;
    
    /**
     * @param Context $context
     * @param CookieManagerInterface $cookieManager
     * @param EncryptorInterface $encryptor
     * @param JsonHelper $jsonHelper
     * @param ProductFactory $productFactory
     * @param MpProductFactory $mpProductFactory
     * @param ShipAreaFactory $shipAreaFactory
     * @param SellerFactory $sellerFactory
     * @param Curl $curl
     */
    public function __construct(
        Context $context,
		StoreManagerInterface $storeConfig,
        CurrencyFactory $currencyFactory,
        CookieManagerInterface $cookieManager,
        EncryptorInterface $encryptor,
        JsonHelper $jsonHelper,
        ProductFactory $productFactory,
        MpProductFactory $mpProductFactory,
        ShipAreaFactory $shipAreaFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Request\Http $request,
        SellerFactory $sellerFactory = null,
        Curl $curl = null,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletModel = null
    ) {
        parent::__construct($context);
		$this->storeConfig = $storeConfig;
        $this->currencyCode = $currencyFactory->create();
        $this->cookieManager = $cookieManager;
        $this->encryptor = $encryptor;
        $this->jsonHelper = $jsonHelper;
        $this->productFactory = $productFactory;
        $this->mpProductFactory = $mpProductFactory;
        $this->shipAreaFactory = $shipAreaFactory;
        $this->_urlInterface = $urlInterface;
        $this->request = $request;
        $this->sellerFactory = $sellerFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
                                                ->create(SellerFactory::class);
        $this->curl = $curl ?: \Magento\Framework\App\ObjectManager::getInstance()
                                                ->create(Curl::class);
        $this->outletModel = $outletModel ?: \Magento\Framework\App\ObjectManager::getInstance()
                                                ->create(\Webkul\MpHyperLocal\Model\OutletFactory::class);
    }
	
	/**
     * @return string
     */
    public function getCurrencySymbol()
    {
        $currentCurrency = $this->storeConfig->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyCode->load($currentCurrency);
        return $currency->getCurrencySymbol();
    }
	
    /**
     * Get Field Value for HyperLocal Configuration
     */
    public function getHyperLocalConfig($field)
    {
        return $this->scopeConfig->getValue('mphyperlocal/general_settings/'.$field);
    }

    /**
     * Get Enabled value from HyperLocal Configuration
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getHyperLocalConfig('enable');
    }

    /**
     * Get Google API Key from HyperLocal Configuration
     *
     * @return string
     */
    public function getGoogleApiKey()
    {
        $encryptApiKey = $this->getHyperLocalConfig('google_api_key');
        $googleApiKey = $this->encryptor->decrypt($encryptApiKey);
        return $googleApiKey;
    }

    /**
     * Get Popup Window Message from HyperLocal Configuration
     *
     * @return string
     */
    public function getPopupMessage()
    {
        return $this->getHyperLocalConfig('location_popup_window');
    }

    /**
     * Get Product Collection Filter Type from HyperLocal Configuration
     *
     * @return string
     */
    public function getFilterCollectionType()
    {
        return 'radius';
    }

    /**
     * Get Radius Unit Value from HyperLocal Configuration
     *
     * @return string
     */
    public function getRadiusUnitValue()
    {
        return $this->getHyperLocalConfig('radious_unit');
    }

    /**
     * Get Admin Address Value from HyperLocal Configuration
     *
     * @return string
     */
    public function getAdminAddress()
    {
        return $this->getHyperLocalConfig('address');
    }

    /**
     * Get Admin Latitude Value from HyperLocal Configuration
     *
     * @return float
     */
    public function getAdminLatitude()
    {
        return $this->getHyperLocalConfig('latitude');
    }

    /**
     * Get Admin Longitude Value from HyperLocal Configuration
     *
     * @return float
     */
    public function getAdminLongitude()
    {
        return $this->getHyperLocalConfig('longitude');
    }
    
    /**
     * Get Formatted Time
     *
     * @return Time
     */
    public function getFormettedTime($time)
    {
        return date('h:i:s a', strtotime($time));
    }

    /**
     * getSavedAddress
     *
     * @return array
     */
    public function getSavedAddress()
    {
        $location = [];
        if ($this->isEnabled()) {
            $cachedAddress = $this->cookieManager->getCookie('hyper_local') ?? "[]";
            $setAddress = $this->jsonHelper->jsonDecode($cachedAddress);
            if (!empty($setAddress)) {
                $location['service_type'] = $setAddress['service_type'];
                $location['latitude'] = $setAddress['lat'];
                $location['longitude'] = $setAddress['lng'];
                $location['address'] = $setAddress['address'];
                $location['city'] = $setAddress['city'] ?? '';
                $location['state'] = $setAddress['state'] ?? '';
                $location['country'] = $setAddress['country'] ?? '';
                $location['countryCode'] = $setAddress['country_code'] ?? '';
                $location['stateCode'] = $setAddress['state_code'] ?? '';
                $location['postcode'] = $setAddress['postcode'] ?? '';
            }
        }
        return $location;
    }

    /**
     * getDistanceFromTwoPoints
     * @param string $from
     * @param string $to
     * @return float $d
     */
    public function getDistanceFromTwoPoints($from, $to, $radiousUnit)
    {
        $R = 6371; // km
        $dLat = ($from['latitude'] - $to['latitude']) * M_PI / 180;
        $dLon = ($from['longitude'] - $to['longitude']) * M_PI / 180;
        $lat1 = $to['latitude'] * M_PI / 180;
        $lat2 = $from['latitude'] * M_PI / 180;
     
        $a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $d = $R * $c;
        if ($radiousUnit == 'mile') {
            $m = $d * 0.621371; //for milles
            return $m;
        }
        return $d;
    }

    /**
     * getSellersProducts
     * @param array $sellerIds
     * @return array
     */
    public function getNearestProducts($sellerIds)
    {
        $adminProList = [];
        $mpProColl = $this->mpProductFactory->create()->getCollection()
                        ->addFieldToFilter('seller_id', ['in' => $sellerIds])
                        ->getColumnValues('mageproduct_id');
        
        if (in_array(null, $sellerIds, true)) {
            $adminProList = $this->getAdminProducts();
        }
        $allowedProList = array_merge($mpProColl, $adminProList);
        
        return empty($allowedProList) ? [0] : $allowedProList;
    }

    /**
     * getAdminProducts
     * @return array
     */
    public function getAdminProducts()
    {
        $sellerProList = $this->mpProductFactory->create()->getCollection()
                            ->getColumnValues('mageproduct_id');
        $collection = $this->productFactory->create()->getCollection();
        
        if (!empty($sellerProList)) {
            $collection->addFieldToFilter('entity_id', ['nin' => $sellerProList]);
        }
        $adminProList = $collection->getColumnValues('entity_id');

        return empty($adminProList) ? [0] : $adminProList;
    }

    /**
     * getNearestSellers
     * @return array
     */
    public function getNearestSellers()
    {
        $savedAddress = $this->getSavedAddress();
        $service_type = '';
        if ($savedAddress) {
            $service_type = $savedAddress['service_type'];
        }
        $pickupServiceRadius = $this->getHyperLocalConfig('pickup_service_radius');
        $collectionFilterOption = $this->getFilterCollectionType();
        $sellerIds = [0];
        if ($collectionFilterOption == 'address') {
            $collection = $this->shipAreaFactory->create()->getCollection();
            $allowedAddress = $this->getAllowedAddress();
            $collectionArray = [];
            foreach ($allowedAddress as $key => $value) {
                if ($value) {
                    $collectionArray[] = $this->shipAreaFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('address_type', $key)
                    ->addFieldToFilter('address', ['like' => '%'.$value.'%'])->getSelect();
                }
            }
            if (count($collectionArray) == 3) {
                $collection->getSelect()->reset();
                $collection->getSelect()->union([$collectionArray[0], $collectionArray[1], $collectionArray[2]]);
            } elseif (count($collectionArray) == 2) {
                $collection->getSelect()->reset();
                $collection->getSelect()->union([$collectionArray[0], $collectionArray[1]]);
            } elseif (count($collectionArray) == 1) {
                $collection->getSelect()->reset();
                $collection->getSelect()->union([$collectionArray[0]]);
            }
            foreach ($collection as $shipArea) {
                $sellerIds[] = $shipArea->getSellerId();
            }
        } else {
            $sellerCollection = $this->sellerFactory->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        ['latitude','longitude'],
                                        [
                                            ['neq'=> 'NULL'],
                                            ['neq'=> 'NULL']
                                        ]
                                    );
                                    
            if($service_type != ''){
                $sellerCollection->addFieldToFilter('service_type', ['like' => '%'.$service_type.'%']);
            }
            
            $currentUrl = $this->_urlInterface->getCurrentUrl();
            $storeLocatorUrl = $this->_urlInterface->getUrl('store-locator');
            $storeLocatorUrl = trim($storeLocatorUrl, '/');
            $params = $this->request->getParams();
            $radiusParam = '';
            if(array_key_exists('radius', $params)){
                $radiusParam = $params['radius'];
            }
            $validateUrl = $storeLocatorUrl.'?radius='.$radiusParam;

            foreach ($sellerCollection as $sellerData) {
                $shipArea['latitude'] = $sellerData->getLatitude();
                $shipArea['longitude'] = $sellerData->getLongitude();

                if($service_type != '' && $service_type == 2){
                    $radious = $pickupServiceRadius;
                }else{
                    $radious = $sellerData->getRadius();
                }

                if($currentUrl == $validateUrl){
                   $radious = $radiusParam;
                }
                
                if ($this->isInRadious($shipArea, $radious)) {
                    $sellerIds[] = $sellerData->getSellerId();
                }
            }
        }
        return array_unique($sellerIds);
    }

    public function getAllowedAddress()
    {
        $address = $this->getSavedAddress();
        return [
            'city'    => $address['city'] ?? '',
            'state'   => $address['state'] ?? '',
            'country' => $address['country'] ?? ''
        ];
    }

    /**
     * isInRadious
     * @param \Webkul\MpHyperLocal\Model\ShipArea $shipArea
     * @return bool
     */
    public function isInRadious($shipArea, $radious = 0)
    {
        $distance = 0;
        $radiousUnit = $this->getRadiusUnitValue();
        $to['latitude'] = $shipArea['latitude'];
        $to['longitude'] = $shipArea['longitude'];
        $savedAddress = $this->getSavedAddress();
        if ($savedAddress) {
            $distance = $this->getDistanceFromTwoPoints($savedAddress, $to, $radiousUnit);
        }
        return $radious >= $distance;
    }

    /**
     * isSellerAvilableInSavedLocation
     * @param int $sellerId
     * @return boolean
     */
    public function isSellerAvilableInSavedLocation($sellerId)
    {
        $sellerlist = $this->getNearestSellers();
        return in_array($sellerId, $sellerlist);
    }
    /**
     * [getAllowSingleSellerSettings
     * used to get settings saved in admin to allow single seller checkout]
     * @return bool
     */
    public function getAllowSingleSellerSettings()
    {
        return $this->scopeConfig->getValue(
            self::ALLOW_SINGLE_SELLER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * [getSellerIdFromMpassign used to get seller id who has assigned the product of other seller]
     * @param  int $assignId [contains assign id]
     * @return int [returns seller id]
     */
    public function getSellerIdFromMpassign($assignId)
    {
        $sellerId=0;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create(
            \Webkul\MpAssignProduct\Model\Items::class
        )->load($assignId);
        if ($model->getSellerId()) {
            $sellerId = $model->getSellerId();
        }
        return $sellerId;
    }

    public function getOutletStatus($sellerId = 0, $outlet = '')
    {
        $status = false;
        $outletModel = $this->outletModel->create()
                         ->getCollection()
                         ->addFieldToFilter('seller_id', $sellerId)
                         ->addFieldToFilter('status', 1);
        if ($outlet) {
            $outletModel->addFieldToFilter('source_code', $outlet);
        }
        if ($outletModel->getSize()) {
            $radious = $this->sellerFactory->create()
                                 ->getCollection()
                                 ->addFieldToFilter('seller_id', $sellerId)
                                 ->addFieldToFilter(
                                     ['latitude','longitude'],
                                     [
                                         ['neq'=> 'NULL'],
                                         ['neq'=> 'NULL']
                                     ]
                                 )
                                 ->getFirstItem()
                                 ->getRadius();
            foreach ($outletModel as $outlet) {
                  $shipArea['latitude'] = $outlet->getLatitude();
                  $shipArea['longitude'] = $outlet->getLongitude();
                if ($this->isInRadious($shipArea, $radious)) {
                    $status = true;
                    break;
                }
            }
        }
        return $status;
    }

    public function getNearestOutlets($storeId = 0)
    {
        $outletIds = [0];
        $sellerids = [];
        $userIds = [0];
        $marketplaceUserData = $this->outletModel->create()
                                    ->getCollection()
                                    ->getTable('marketplace_userdata');
        $outletModel = $this->outletModel->create()
                        ->getCollection()
                        ->addFieldToFilter('status', 1);
        $outletModel->getSelect()->join(
            $marketplaceUserData.' as cgf',
            'main_table.seller_id = cgf.seller_id AND store_id='.$storeId,
            [
                'radius' => 'radius',
                'wk_user_id' => 'entity_id'
            ]
        );
        foreach ($outletModel as $outlet) {
            $sellerids[] = $outlet->getSellerId();
            $shipArea['latitude'] = $outlet->getLatitude();
            $shipArea['longitude'] = $outlet->getLongitude();
            $radious = $outlet->getRadius();
            if ($this->isInRadious($shipArea, $radious)) {
                $userIds[] = $outlet->getWkUserId();
                $outletIds[] = $outlet->getEntityId();
            }
        }
        $adminStoreModel = $this->outletModel->create()
                                ->getCollection()
                                ->addFieldToFilter('status', 1);
        $adminStoreModel->getSelect()->join(
            $marketplaceUserData.' as cgf',
            'main_table.seller_id = cgf.seller_id AND store_id=0',
            [
                'radius' => 'radius',
                'wk_user_id' => 'entity_id'
            ]
        );
        if (!empty($sellerids)) {
            $sellerIds = implode(",", array_unique($sellerids));
            $adminStoreModel->getSelect()
                            ->where('main_table.seller_id NOT IN ('.$sellerIds.')');
        }
        foreach ($adminStoreModel as $outlet) {
            $shipArea['latitude'] = $outlet->getLatitude();
            $shipArea['longitude'] = $outlet->getLongitude();
            $radious = $outlet->getRadius();
            if ($this->isInRadious($shipArea, $radious)) {
                $userIds[] = $outlet->getWkUserId();
                $outletIds[] = $outlet->getEntityId();
            }
        }
        $outletIds = array_unique($outletIds);
        $userIds = array_unique($userIds);
        return [$outletIds, $userIds];
    }

    /**
     * getLocation
     * @param string $address
     * @return array
     */
    public function getLocation($address)
    {
        try {
            $address = str_replace(' ', '+', $address);
            $address = str_replace('++', '+', $address);
            $apiKey = $this->getGoogleApiKey();
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&sensor=false&key='.$apiKey;
            $this->curl->get($url);
            $response = $this->jsonHelper->jsonDecode($this->curl->getBody());
            $location = $response['results'][0]['geometry']['location'];
            return ['latitude' => $location['lat'], 'longitude' => $location['lng']];
        } catch (\Exception $e) {
            return ['latitude' => '', 'longitude' => ''];
        }
    }

    /**
     * Get Address change Method from HyperLocal Configuration
     *
     * @return string
     */
    public function getAddressOption()
    {
        return $this->getHyperLocalConfig('address_option');
    }
}

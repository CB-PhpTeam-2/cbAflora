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

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\MpHyperLocal\Helper\Data;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class for address forms.
 */
class AddressForm extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var MpHelper
     */
    protected $_mpHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param Context                                    $context
     * @param Data                                       $helper
     * @param MpHelper                                   $mpHelper
     * @param SearchCriteriaBuilder                      $searchCriteriaBuilder
     * @param AddressRepositoryInterface                 $addressRepository
     * @param \Magento\Directory\Model\CountryFactory    $countryFactory
     * @param ScopeConfigInterface                       $scopeConfig
     * @param \Psr\Log\LoggerInterface                   $logger
     * @param array                                      $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        MpHelper $mpHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterface $addressRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->_mpHelper = $mpHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
        $this->_countryFactory = $countryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Get Save Action URL
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('mphyperlocal/index/setaddress', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get Address Action URL
     * @return string
     */
    public function getAddressAction()
    {
        return $this->getUrl('mphyperlocal/index/getaddress', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Get Hyperlocal Helper
     *
     * @return object $helper
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Address List
     *
     * @return array $addressList
     */
    public function getAddressList()
    {
        $addressesList = [];
        try {
            if ($this->_mpHelper->isCustomerLoggedIn()) {
                $customerId = $this->_mpHelper->getCustomerId();
                $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                    'parent_id',
                    $customerId
                )->create();
                $addressRepository = $this->addressRepository->getList($searchCriteria);
                foreach ($addressRepository->getItems() as $address) {
                    $addressesList[] = $address;
                }
            }
            return $addressesList;
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Get country name
     * @param int $countryCode
     *
     * @return string
     */
    public function getCountryname($countryCode)
    {
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    /**
     * get congig data
     *
     * @return void
     */
    public function getConfigure()
    {
        $str = "mphyperlocal/shopLocatorStyle/";
        $config = [];
        $config['btnColor'] = $this->scopeConfig->getValue($str.'btnColor');
        $config['backgroundImage'] = $this->scopeConfig->getValue($str.'backgroundImage');
        if ($config['btnColor'][0] != "#") {
            $config['btnColor'] = "#".$config['btnColor'];
        }
        return $config;
    }
}

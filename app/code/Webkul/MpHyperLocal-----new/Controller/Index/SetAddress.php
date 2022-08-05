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
namespace Webkul\MpHyperLocal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\MpHyperLocal\Helper\Data;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\AddressRepositoryInterface;

class SetAddress extends Action
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'hyper_local';

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadata;

    /**
     * @var Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Data
     */
    protected $_helper;

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
     * @param Context                       $context
     * @param JsonHelper                    $jsonHelper
     * @param HttpContext                   $httpContext
     * @param JsonFactory                   $jsonFactory
     * @param SessionManagerInterface       $sessionManager
     * @param CookieMetadataFactory         $cookieMetadata
     * @param CookieManagerInterface        $cookieManager
     * @param CheckoutSession               $checkoutSession
     * @param Data                          $helper
     * @param MpHelper                      $mpHelper
     * @param SearchCriteriaBuilder         $searchCriteriaBuilder
     * @param AddressRepositoryInterface    $addressRepository
     */
    public function __construct(
        Context $context,
        JsonHelper $jsonHelper,
        HttpContext $httpContext,
        JsonFactory $jsonFactory,
        SessionManagerInterface $sessionManager,
        CookieMetadataFactory $cookieMetadata,
        CookieManagerInterface $cookieManager,
        CheckoutSession $checkoutSession,
        Data $helper,
        MpHelper $mpHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\AddressFactory $customerAddress
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->httpContext = $httpContext;
        $this->jsonFactory = $jsonFactory;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadata = $cookieMetadata;
        $this->cookieManager = $cookieManager;
        $this->checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_mpHelper = $mpHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressRepository = $addressRepository;
        $this->_customerAddress = $customerAddress;
        parent::__construct($context);
    }

    /**
     * Set Address
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_helper->isEnabled()) {
            $jsonFactory = $this->jsonFactory->create();
            $data = $this->getRequest()->getPostValue();
            $addressId = 0;
            if ($data && $data['lat'] != '' && $data['lng'] != '') {
                if (isset($data['redirect_url']) && $data['redirect_url'] != '') {
                    $redirectUrl = $this->_url->getUrl($data['redirect_url']);
                } else {
                    $redirectUrl = $this->_url->getUrl();
                }

                if (isset($data['address-id']) && $data['address-id']) {
                    $addressId = $data['address-id'];
                }
        
                if ($this->_mpHelper->isCustomerLoggedIn()) {
                    $customerId = $this->_mpHelper->getCustomerId();
                    $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                        'parent_id',
                        $customerId
                    )->create();
                    $addressRepository = $this->addressRepository->getList($searchCriteria);
                    
                    foreach ($addressRepository->getItems() as $address) {
                        $changeStatus = false;
                        if ($address->getId() == $addressId) {
                            $add = $this->_customerAddress->create()->load($addressId)
                            ->setIsDefaultShipping(1);
                            $changeStatus = true;
                        } elseif ($address->isDefaultShipping()) {
                            $add = $this->_customerAddress->create()->load($address->getId())
                            ->setIsDefaultShipping(0);
                            $changeStatus = true;
                        }
                        if ($changeStatus) {
                            $add->save();
                        }
                    }
                }
            
                $jsonAddressData = $this->jsonHelper->jsonEncode($data);
                $metadata = $this->cookieMetadata->createPublicCookieMetadata()
                    ->setDuration(86400)
                    ->setPath($this->sessionManager->getCookiePath())
                    ->setDomain($this->sessionManager->getCookieDomain());
                $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $jsonAddressData, $metadata);
                $this->httpContext->setValue(
                    'hyperlocal_data',
                    $jsonAddressData,
                    false
                );
                $this->checkoutSession->getQuote()->delete();
                $result = ['status'=> 1, 'msg' => __('Address Set'), 'redirect_url' => $redirectUrl];
            } else {
                $result = ['status'=> 0, 'msg' => __('Fill Correct Address.')];
            }
            return $jsonFactory->setData($result);
        }
    }
}

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
namespace Webkul\MpHyperLocal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpHyperLocal\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\App\Http\Context as HttpContext;

class ControllerPredispatchObserver implements ObserverInterface
{
    const COOKIE_NAME = 'hyper_local';

    /**
     * @var Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var Webkul\MpHyperLocal\Helper\Data
     */
    private $_helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $_request;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $httpHeader;

    /**
     * @param UrlInterface    $urlInterface
     * @param Data            $helper
     * @param Http            $request
     * @param Header          $httpHeader
     */
    public function __construct(
        UrlInterface $urlInterface,
        Data $helper,
        Http $request,
        Header $httpHeader,
        SessionManagerInterface $sessionManager,
        CookieMetadataFactory $cookieMetadata,
        CookieManagerInterface $cookieManager,
        HttpContext $httpContext
    ) {
        $this->urlInterface = $urlInterface;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->httpHeader = $httpHeader;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadata = $cookieMetadata;
        $this->cookieManager = $cookieManager;
        $this->httpContext = $httpContext;
    }

    /**
     * checking if the address is set or not.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helper->isEnabled()) {
            $userAgent = $this->httpHeader->getHttpUserAgent();
            if (strpos($userAgent, 'curl') === false) {
                if (!$this->_request->isAjax()) {
                    return $this->isRedirect($observer);
                }
            }
        }
    }

    public function isRedirect($observer)
    {
        if ($this->_helper->getAddressOption() == 'redirect') {
            
            $currentUrl = $this->urlInterface->getCurrentUrl();
            if (strpos($currentUrl, 'customer/account/createPassword') == true){
                $jsonAddressData = '';
                $AddressDataParts = explode("cookie_address=", $currentUrl);
                if(sizeof($AddressDataParts) > 1){
                    if(array_key_exists(1, $AddressDataParts) && $AddressDataParts[1] != ''){
                      $params_Arr = explode("&", $AddressDataParts[1]);
                      if(sizeof($params_Arr) > 0){
                        $jsonAddressData = $params_Arr[0];
                      }
                    }
                }

                if($jsonAddressData != ''){
                    $jsonAddressData = urldecode($jsonAddressData);
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
                }
            }

            $address = $this->_helper->getSavedAddress();
            if (strpos($currentUrl, 'mphyperlocal/address/index') === false) {
                if (!$address) {
                    $addressUrl = $this->urlInterface->getUrl('mphyperlocal/address/index');
                    $observer->getControllerAction()->getResponse()->setRedirect($addressUrl);
                }
            }
        }
    }
}

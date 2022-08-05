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

class ControllerPredispatchObserver implements ObserverInterface
{
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
        Header $httpHeader
    ) {
        $this->urlInterface = $urlInterface;
        $this->_helper = $helper;
        $this->_request = $request;
        $this->httpHeader = $httpHeader;
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
            $address = $this->_helper->getSavedAddress();
            $currentUrl = $this->urlInterface->getCurrentUrl();
            if (strpos($currentUrl, 'mphyperlocal/address/index') === false) {
                if (!$address) {
                    $addressUrl = $this->urlInterface->getUrl('mphyperlocal/address/index');
                    $observer->getControllerAction()->getResponse()->setRedirect($addressUrl);
                }
            }
        }
    }
}

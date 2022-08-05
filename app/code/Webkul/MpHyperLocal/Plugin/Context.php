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
 
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Context
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
 
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;
 
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        CookieManagerInterface $cookieManager,
        JsonHelper $jsonHelper
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->cookieManager = $cookieManager;
        $this->jsonHelper = $jsonHelper;
    }
 
    /**
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $address = $this->cookieManager->getCookie('hyper_local') ?? "[]";
        $setAddress = $this->jsonHelper->jsonDecode($address);
        $this->httpContext->setValue(
            'address',
            $setAddress,
            false
        );
 
        return $proceed($request);
    }
}

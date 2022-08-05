<?php

namespace Cb\GreenlineApi\Block\Account;

/**
 * GreenlineApi Navigation link
 *
 */
class Navigation extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @return string current url
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * getMpAuction for get complete url using url key
     * @param String $urlKey
     * @return String url
     */
    public function getDealUrl($urlKey)
    {
        return $this->getUrl($urlKey, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getCurrentNavClass return nav item active or not class
     * @param  string $urlKey url key for match with current url
     * @return string|""
     */
    public function getCurrentNavClass($urlKey)
    {
        $currentUrl = $this->getCurrentUrl();
        return strpos($currentUrl, $urlKey) ? "current" : "";
    }

    /**
     * @return string current url
     */
    public function isModuleEnable()
    {
        return $this->_scopeConfig->getValue('greenlineapi/general_settings/enable');
    }
}

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
namespace Webkul\MpHyperLocal\Rewrite\Controller\Category;

/**
 * View a category on storefront.
 */
class View extends \Magento\Catalog\Controller\Category\View
{
    /**
     * inherit
     */
    public function execute()
    {
        if (!$this->isCurl()) {
            if ($this->getStatus()) {
                $url = $this->_url->getUrl();
                return $this->resultRedirectFactory->create()->setUrl($url);
            }
        }
        return parent::execute();
    }

    /**
     * Get Status based on the configuration
     *
     * @return bool
     */
    private function getStatus()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get(\Webkul\MpHyperLocal\Helper\Data::class);
        return $helper->isEnabled();
    }
    
    /**
     * Checking if curl request or not
     *
     * @return bool
     */
    private function isCurl()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $httpHeader = $objectManager->get(\Magento\Framework\HTTP\Header::class);
        $userAgent = $httpHeader->getHttpUserAgent();
        return strpos($userAgent, 'curl') !== false;
    }

}

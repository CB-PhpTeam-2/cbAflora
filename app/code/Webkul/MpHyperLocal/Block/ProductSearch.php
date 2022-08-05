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

/**
 * Collection Product Search
 *
 */
class ProductSearch extends \Magento\Framework\View\Element\Template
{

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * [getProfileUrl ]
     * @return [string] [seller shop name]
     */
    public function getProfileUrl()
    {
        $shopUrl = $this->mpHelper->getCollectionUrl();
        if (!$shopUrl) {
            return $shopUrl = $this->getRequest()->getParam('shop');
        }
        return $shopUrl;
    }

    /**
     * getSearchText
     *
     * @return string
     */
    public function getSearchText()
    {
        $searchText = $this->getRequest()->getParam('name');
        return $searchText;
    }
    /**
     * getJsonHelper function
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}

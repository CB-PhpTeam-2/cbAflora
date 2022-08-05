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

use \Magento\Framework\App\Helper\Context;

class CatalogBlockProductCollectionBeforeToHtmlObserver
{
    /**
     *
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $_helper;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param Context                          $context
     * @param \Webkul\MpHyperLocal\Helper\Data $data
     */
    public function __construct(
        Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Webkul\MpHyperLocal\Helper\Data $data
    ) {
        $this->_helper = $data;
        $this->_reviewFactory = $reviewFactory;
    }

    /**
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundExecute(\Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject, callable $proceed, \Magento\Framework\Event\Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $savedAddress = $this->_helper->getSavedAddress();
        if ($savedAddress) {
            $sellerIds = $this->_helper->getNearestSellers();
            $allowedProList = $this->_helper->getNearestProducts($sellerIds);
            $productCollection->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
            $observer->setCollection($productCollection);
        }
        $proceed($observer);
        return $this;
    }
}

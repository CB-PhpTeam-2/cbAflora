<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Plugin\App\Action;

use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;

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
     * @var CollectionFactory
     */
    public $_collectionFactory;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        CollectionFactory $collectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->_collectionFactory = $collectionFactory;
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

        $customerId = $this->customerSession->getCustomerId();
        $subAccountCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId);

        if(sizeof($subAccountCollection) > 0){
            $customerId = $subAccountCollection->getFirstItem()->getSellerId();
        }
               
        $this->httpContext->setValue(
            'customer_id',
            $customerId,
            false
        );

        return $proceed($request);
    }
}

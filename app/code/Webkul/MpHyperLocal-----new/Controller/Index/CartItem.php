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
use Webkul\MpHyperLocal\Helper\Data as HyperLocalHelper;
use Magento\Quote\Model\Quote\ItemFactory;

class CartItem extends Action
{
    /**
     * @var HyperLocalHelper
     */
    private $helper;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var ItemFactory
     */
    private $quoteItemFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param HyperLocalHelper $helper
     * @param JsonFactory $js
     * @param ItemFactory $quoteItemFactory
     */
    public function __construct(
        Context $context,
        HyperLocalHelper $helper,
        JsonFactory $jsonFactory,
        ItemFactory $quoteItemFactory     
    ) {
        $this->helper = $helper;
        $this->jsonFactory = $jsonFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        parent::__construct($context);
    }

    /**
     * Set Address
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->helper->isEnabled()) {
            $jsonFactory = $this->jsonFactory->create();
            $params = $this->getRequest()->getPostValue();
            if (isset($params['item-id'])) {
                $ItemId = $params['item-id'];
                $quoteItem = $this->quoteItemFactory->create()->load($ItemId);
                if ($quoteItem->getParentId()) {
                    $quoteItem = $this->quoteItemFactory->create()->load($quoteItem->getParentId());
                }
                $result = ['status' => 1, 'id' => $quoteItem->getProductId()];
            } else {
                $result = ['status' => 0, 'msg' => __("Item Id Not found.")];
            }
            return $jsonFactory->setData($result);
        }
    }
}

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

namespace Webkul\MpHyperLocal\Controller\Address;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\MpHyperLocal\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class to get coordinates for the address
 */
class Coordinates extends Action
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @var Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    public function __construct(
        Context $context,
        Data $helper,
        JsonFactory $jsonFactory
    ) {
        $this->helper = $helper;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $jsonFactory = $this->jsonFactory->create();
        $params = $this->getRequest()->getPostValue();
        if (isset($params['address'])) {
            $address = $params['address'];
            $result = $this->helper->getLocation($address);
        } else {
            $result = ['status' => 0, 'msg' => __("Address Not found.")];
        }
        return $jsonFactory->setData($result);
    }
}

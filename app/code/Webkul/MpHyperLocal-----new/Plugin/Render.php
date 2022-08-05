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

class Render
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $_request;

    /**
     * @param Context                             $context
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
    }

    /**
     * @param \Magento\Theme\Block\Html\Header\Logo $subject
     * @param $result
     * @return string
     */
    public function beforeExecute(\Magento\Ui\Controller\Adminhtml\Index\Render $subject)
    {
        $params = $this->_request->getParams();
        if (!empty($params['namespace']) && !empty($params['filters']['name']) &&
        ($params['namespace'] == "mphyperlocal_shiprate_grid_list" || $params['namespace']
        == "mphyperlocal_shiparea_grid_list") && $params['filters']['name'] == "Admin") {
            $params['filters']['name'] = null;
            $params['filters']['admin'] = "admin";
            $this->_request->setParams($params);
        }
        return $this;
    }
}

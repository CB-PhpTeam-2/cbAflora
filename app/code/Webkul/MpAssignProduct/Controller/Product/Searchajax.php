<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Searchajax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Escaper $_escaper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory  = $resultJsonFactory;
        $this->_objectManager = $objectManager;
        $this->_escaper = $_escaper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $blockInstance = $this->_objectManager->get('Webkul\MpAssignProduct\Block\Product\ProductList');
        $collection = $blockInstance->getAllProducts();
        

        $html_content = '';
        foreach ($collection as $key => $product) {
            $_optionText = '';
            $_attributeId = $product->getResource()->getAttribute('size');
            if ($_attributeId->usesSource()) {
                  $_optionText = $_attributeId->getSource()->getOptionText($product->getSize());
            }

            $html_content .= '<tr>
                        <td class="col wk-ap-img-col">
                            <img src="'.$this->_escaper->escapeHtml($blockInstance->getProductImage($product)).'">
                        </td>
                        <td class="col">'.$this->_escaper->escapeHtml($product->getName()).'</td>
                        <td class="col">'.$this->_escaper->escapeHtml($product->getBarcode()).'</td>
                        <td class="col">'.$this->_escaper->escapeHtml($blockInstance->getFormatedPrice($product->getFinalPrice())).'</td>
                        <td class="col">'.$this->_escaper->escapeHtml($product->getProducer()).'</td>
                        <td class="col">'.$this->_escaper->escapeHtml($_optionText).'</td>
                        <td class="col wk-ap-btn-col">
                            <a href="'.$blockInstance->escapeUrl($blockInstance->getAddProductPageUrl($product->getId())).'">
                                <button class="button wk-ap-btn">
                                <span><span>
                                Assign                                </span></span>
                                </button>
                            </a>
                        </td>
                    </tr>';
        }

        $html   =  '';
        $html   .=  '<fieldset class="fieldset wk-ap-fieldset">
                        <table class="data table wk-table-product-list ajax_result">';
        $html   .=          '<thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Barcode</th>
                                    <th>Price</th>
                                    <th>Producer</th>
                                    <th>Size</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>';
        if($collection->getSize() > 0){
            $html   .=      $html_content;
        }else{
            $html   .=      '<tr><td colspan="4">No Products found!!</td></tr>';
        }

        $html       .=       '</tbody>
                        </table>
                    </fieldset>';

        $result = $this->resultJsonFactory->create();
        return $result->setData(['html_content' => $html]);
    }
}

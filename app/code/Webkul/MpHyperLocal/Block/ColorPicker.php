<?php
/**
 * @category   Webkul
 * @package    Webkul_MpHyperLocal
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpHyperLocal\Block;

use Webkul\MpHyperLocal\Logger\Logger;

class ColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        Logger $logger,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->logger = $logger;
    }

    /**
     * add color picker in admin configuration fields
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string script
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        try {
            $html = $element->getElementHtml();
            $value = $element->getData('value');

            $html .= '<script type="text/javascript">
                    require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                        $(document).ready(function () {
                            var $el = $("#'.$element->getHtmlId().'");
                            $el.css("backgroundColor", "'.$value.'");

                            // Attach the color picker
                            $el.ColorPicker({
                                color: "'.$value.'",
                                onChange: function (hsb, hex, rgb) {
                                    $el.css("backgroundColor", "#" + hex).val("#" + hex);
                                }
                            });
                        });
                    });
                    </script>';

            return $html;
        } catch (\Exception $e) {
            $this->logger->addError("Block=ColorPicker function=_getElementHtml Error= ".$e->getMessage());
        }
    }
}

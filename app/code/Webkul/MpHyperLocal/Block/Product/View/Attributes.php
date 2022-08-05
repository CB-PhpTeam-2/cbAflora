<?php

namespace Webkul\MpHyperLocal\Block\Product\View;

use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Attributes attributes block
 *
 * @api
 * @since 100.0.2
 */
class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $priceCurrency,
            $data
        );
    }

    public function getAdditionalData(array $excludeAttr = [])
    {
        $data = [];
        $product = $this->getProduct();
        $attributes = $product->getAttributes();
        $thc_low_value = '';
        $cbd_low_value = '';
        $uom_value = '';
        foreach ($attributes as $attribute) {
            if($attribute->getAttributeCode() == 'thc_low'){
                $thc_low_value = $attribute->getFrontend()->getValue($product);
            }
            if($attribute->getAttributeCode() == 'cbd_low'){
                $cbd_low_value = $attribute->getFrontend()->getValue($product);
            }
            if($attribute->getAttributeCode() == 'uom'){
                $uom_value = $attribute->getFrontend()->getValue($product);
            }
            if ($this->isVisibleOnFrontend($attribute, $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if ($value instanceof Phrase) {
                    $value = (string)$value;
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value) && $attribute->getAttributeCode() != 'thc_high' && $attribute->getAttributeCode() != 'cbd_high') {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen(trim($value))) {
                    $label = '';
                    if($attribute->getAttributeCode() == 'thc_high'){
                        $label = 'THC';
                        $value = round($thc_low_value, 2).$uom_value.' - '.round($value, 2).$uom_value;
                    }else if($attribute->getAttributeCode() == 'cbd_high'){
                        $label = 'CBD';
                        $value = round($cbd_low_value, 2).$uom_value.' - '.round($value, 2).$uom_value;
                    }else{
                        $label = $attribute->getStoreLabel();
                    }
                    $data[$attribute->getAttributeCode()] = [
                        'label' => $label,
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }
        return $data;
    }

}

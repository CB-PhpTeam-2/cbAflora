<?php 
namespace Cb\CustomAdmin\Observer;

class LockProductAttributes implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Observer to catalog_product_save_before to lock attributes.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $product->lockAttribute('barcode');
        $product->lockAttribute('greenlineapi_product_id');
    }
}


?>
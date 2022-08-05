<?php
namespace Cb\Pickup\Plugin;

use Cb\Pickup\Helper\Data;

class Quote
{
  /** @var Data */
  protected $_helper;

  public function __construct(Data $helper)
  {
    $this->_helper = $helper;
  }

  public function beforeSetBillingAddress(
    \Magento\Quote\Model\Quote $subject,
    \Magento\Quote\Api\Data\AddressInterface $address = null
  ) {
    if ($this->_helper->allowDifferentShippingAddress() || $subject->isVirtual()) {
      return [$address];
    }

    return [$subject->getShippingAddress()];
  }
}
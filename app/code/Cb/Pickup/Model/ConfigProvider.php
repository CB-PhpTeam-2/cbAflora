<?php
namespace Cb\Pickup\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Cb\Pickup\Helper\Data;

class ConfigProvider implements ConfigProviderInterface
{
  /** @var CheckoutSession */
  protected $_checkoutSession;

  /** @var Data */
  protected $_helper;

  public function __construct(
    CheckoutSession $checkoutSession,
    Data $helper
  ) {
    $this->_checkoutSession = $checkoutSession;
    $this->_helper = $helper;
  }

  /** @return array */
  public function getConfig()
  {
    return [
      'cb_pickup' => [
        'allow' => $this->_allowDifferentShippingAddress()
      ]
    ];
  }

  /** @return bool */
  protected function _allowDifferentShippingAddress()
  {
    if ($this->_checkoutSession->getQuote()->isVirtual()) {
      return true;
    }

    return $this->_helper->allowDifferentShippingAddress();
  }
}
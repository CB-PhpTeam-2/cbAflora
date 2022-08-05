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

use Magento\Framework\Exception\StateException;
use Webkul\Marketplace\Model\SellerFactory;

class ShippingInformationManagement
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $hyperLocalHelper;

    /**
     * @var \Webkul\MpHyperLocal\Model\OutletFactory
     */
    protected $outletModel;

    public function __construct(
        \Magento\Directory\Model\Country $country,
        \Webkul\MpHyperLocal\Helper\Data $hyperLocalHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        SellerFactory $sellerModel,
        \Webkul\MpHyperLocal\Model\OutletFactory $outletModel
    ) {
        $this->country = $country;
        $this->hyperLocalHelper = $hyperLocalHelper;
        $this->quoteRepository = $quoteRepository;
        $this->sellerModel = $sellerModel;
        $this->mpHelper = $mpHelper;
        $this->outletModel = $outletModel;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $isEnabled = $this->hyperLocalHelper->isEnabled();
        if ($isEnabled) {
            $shipAddress = $this->hyperLocalHelper->getSavedAddress();
            if ($shipAddress) {
                $status = true;
                $quote = $this->quoteRepository->getActive($cartId);
                $quoteShipAdd = $addressInformation->getShippingAddress()->getData();
                unset($quoteShipAdd['extension_attributes']);
                $city = $quoteShipAdd['city'] ?? '';
                $postcode = $quoteShipAdd['postcode'] ?? '';
                $countryId = $quoteShipAdd['country_id'] ?? '';
                $destAddress = $city."+".$postcode."+".$countryId;
                $from = $this->hyperLocalHelper->getLocation($destAddress);
                if ($from['latitude'] != "" && $from['longitude'] != "") {
                    $radiusUnit = $this->hyperLocalHelper->getRadiusUnitValue();
                    list($sellerIds, $outletIds) = $this->getSellerData($quote);
                    if (!empty($sellerIds)) {
                        $sellerCollection = $this->sellerModel->create()
                                            ->getCollection()
                                            ->addFieldToFilter(
                                                'seller_id',
                                                ['in' => $sellerIds]
                                            )
                                            ->addFieldToFilter(
                                                ['latitude','longitude'],
                                                [
                                                    ['neq'=> 'NULL'],
                                                    ['neq'=> 'NULL']
                                                ]
                                            );
                        foreach ($sellerCollection as $sellerData) {
                            $to['latitude'] = $sellerData->getLatitude();
                            $to['longitude'] = $sellerData->getLongitude();
                            $radius = $sellerData->getRadius();
                            if ($to['latitude'] != "" && $to['longitude'] != "") {
                                $distance = $this->hyperLocalHelper->getDistanceFromTwoPoints($from, $to, $radiusUnit);
                                if ($radius < $distance) {
                                    $status = false;
                                    break;
                                }
                            } else {
                                $status = false;
                                break;
                            }
                        }
                    }
                    if ($status) {
                        foreach ($outletIds as $outletId => $sellerId) {
                            if (!$this->getOutletStatus($from, $radiusUnit, $sellerId, $outletId)) {
                                $status = false;
                                break;
                            }
                        }
                    }
                    if (!$status) {
                        throw new StateException(__('Store does not provide delivery to selected address..'));
                    }
                } else {
                    throw new StateException(__('Store does not provide delivery to selected address.'));
                }
            } else {
                throw new StateException(__('Please select your address.'));
            }
        }
    }

    private function getSellerData($cart)
    {
        $sellerIds = [];
        $outletIds = [];
        foreach ($cart->getAllItems() as $item) {
            $options = $item->getBuyRequest()->getData();
            /*checks for seller assign product*/
            if (array_key_exists("mpassignproduct_id", $options)) {
                if (array_key_exists("outlet_id", $options)) {
                    $sellerId = $this->hyperLocalHelper
                                ->getSellerIdFromMpassign(
                                    $options["mpassignproduct_id"]
                                );
                    $outletIds[$options["outlet_id"]] = $sellerId;
                } else {
                    $sellerIds[] = $this->hyperLocalHelper
                                    ->getSellerIdFromMpassign(
                                        $options["mpassignproduct_id"]
                                    );
                }
            } elseif (array_key_exists("outlet_id", $options)) {
                $sellerId = $this->mpHelper->getSellerIdByProductId(
                    $item->getProductId()
                );
                $outletIds[$options["outlet_id"]] = $sellerId;
            } else {
                $sellerIds[] = $this->mpHelper->getSellerIdByProductId(
                    $item->getProductId()
                );
            }
        }
        $sellerIds = array_unique($sellerIds);
        return [$sellerIds, $outletIds];
    }

    public function getOutletStatus($from, $radiusUnit, $sellerId = 0, $outlet = '')
    {
        $status = false;
        $outletModel = $this->outletModel->create()
                         ->getCollection()
                         ->addFieldToFilter('seller_id', $sellerId)
                         ->addFieldToFilter('status', 1)
                         ->addFieldToFilter('source_code', $outlet);
        if ($outletModel->getSize()) {
            $radious = $this->sellerModel->create()
                                 ->getCollection()
                                 ->addFieldToFilter('seller_id', $sellerId)
                                 ->addFieldToFilter(
                                     ['latitude','longitude'],
                                     [
                                         ['neq'=> 'NULL'],
                                         ['neq'=> 'NULL']
                                     ]
                                 )
                                 ->getFirstItem()
                                 ->getRadius();
            foreach ($outletModel as $outlet) {
                  $shipArea['latitude'] = $outlet->getLatitude();
                  $shipArea['longitude'] = $outlet->getLongitude();
                  $distance = $this->hyperLocalHelper->getDistanceFromTwoPoints($from, $shipArea, $radiusUnit);
                if ($radious >= $distance) {
                      $status = true;
                      break;
                }
            }
        }
        return $status;
    }
}

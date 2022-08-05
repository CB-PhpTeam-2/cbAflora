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
namespace Webkul\MpHyperLocal\Block\Adminhtml\Ship\Area\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Webkul\MpHyperLocal\Helper\Data;

class Main extends Generic
{
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $data
        );
        $this->helper = $helper;
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Webkul\MpHyperLocal\Model\ShipArea */
        $model = $this->_coreRegistry->registry('ship_area');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Ship Area Information')]);
        $data = [];
        if ($model->getEntityId()) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
            $data = [
                'entity_id' => $model->getEntityId(),
                'autocomplete' => $model->getAddress(),
                'mphyperlocal_general_settings_latitude' => $model->getLatitude(),
                'mphyperlocal_general_settings_longitude' => $model->getLongitude(),
                'address_type' => $model->getAddressType()
            ];
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $baseFieldset->addField(
            'autocomplete',
            'text',
            [
                'name' => 'address',
                'label' => __('Address'),
                'id' => 'autocomplete',
                'title' => __('Ship Area Address'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'address_type',
            'select',
            [
                'name' => 'address_type',
                'label' => __('Address Type'),
                'id' => 'address_type',
                'title' => __('Address Type'),
                'class' => 'required-entry',
                'required' => true,
                'options' => [
                    '' => __('--select--'),
                    'city' => __('City'),
                    'state' => __('State'),
                    'country' => __('Country')
                ]
            ]
        );

        $baseFieldset->addField(
            'mphyperlocal_general_settings_latitude',
            'text',
            [
                'name' => 'latitude',
                'label' => __('Latitude'),
                'id' => 'latitude',
                'title' => __('Latitude'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'mphyperlocal_general_settings_longitude',
            'text',
            [
                'name' => 'longitude',
                'label' => __('Longitude'),
                'id' => 'longitude',
                'title' => __('Longitude'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        
        $Lastfield = $form->getElement('mphyperlocal_general_settings_longitude');
        $Lastfield->setAfterElementHtml(
            '<script type="text/x-magento-init">
                {
                    "body": {
                        "autofilladdress": {
                            "googleApiKey":"'.$this->helper->getGoogleApiKey().'",
                            "savedAddress":"'. $this->getSavedAddress($data).'"
                        }
                    }
                }
            </script>'
        );
        
        $form->setValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * getSavedAddress.
     * @param array
     * @return string
     */
    public function getSavedAddress($data)
    {
        return isset($data['autocomplete']) ? $data['autocomplete'] : '';
    }
}

<?php

namespace Webkul\SellerSubAccount\Block\Widget;

class Dob extends \Magento\Customer\Block\Widget\Dob
{
    public function getFieldHtml()
    {
        $this->dateElement->setData(
            [
                'extra_params' => $this->getHtmlExtraParams(),
                'name' => $this->getHtmlId(),
                'id' => $this->getHtmlId(),
                'class' => $this->getHtmlClass(),
                'value' => $this->getValue(),
                'date_format' => $this->getDateFormat(),
                'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
                'years_range' => '-120y:c+nn',
                'max_date' => '-1d',
                'change_month' => 'true',
                'change_year' => 'true',
                'show_on' => 'both',
                'first_day' => $this->getFirstDay()
            ]
        );
        return $this->dateElement->getHtml();
    }

    public function getHtmlExtraParams()
    {
        /* NEW LINES */
        $firstDateLetter = substr(strtolower($this->getDateFormat()), 0, 1);
        if ($firstDateLetter == 'm') {
            $rule = 'validate-date'; /* Rule for mm/dd/yyyy date format */
        } else {
            $rule = 'validate-date-au'; /* Rule for dd/mm/yyyy date format */
        }
        /* END NEW LINES */
        $extraParams = [
            "'".$rule."':true" /* MODIFIED LINE */
        ];
      
        if ($this->isRequired()) {
            $extraParams[] = 'required:true';
        }

        $extraParams = implode(', ', $extraParams);

        return 'data-validate="{' . $extraParams . '}"';
    }
    
    public function getDateFormat()
    {
        $dateFormat = $this->_localeDate->getDateFormatWithLongYear();
        /** Escape RTL characters which are present in some locales and corrupt formatting */
        $escapedDateFormat = preg_replace('/[^MmDdYy\/\.\-]/', '', $dateFormat);

        return $escapedDateFormat;
    }
}

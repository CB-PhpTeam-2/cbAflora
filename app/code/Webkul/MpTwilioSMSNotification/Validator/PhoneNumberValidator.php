<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpTwilioSMSNotification\Validator;

class PhoneNumberValidator
{
    const PHONE_NUMBER_FORMAT = "/^\+\d{3,}+$/";
    const PHONE_NUMBER_MIN_LENGTH = 4;

    /**
     * Validate Phone number length
     *
     * @param string $phoneNumber
     * @param int $min
     * @return boolean
     */
    public function isLengthValid($phoneNumber, $min = self::PHONE_NUMBER_MIN_LENGTH): bool
    {
        return strlen($phoneNumber) >= $min;
    }

    /**
     * Validate phone number format
     *
     * @param string $phoneNumber
     * @param string $format
     * @return boolean
     */
    public function isFormatValid($phoneNumber, $format = self::PHONE_NUMBER_FORMAT): bool
    {
        return (bool)preg_match($format, $phoneNumber);
    }

    /**
     * Check whether a phone number is empty.
     *
     * @param string $phoneNumber
     * @return boolean
     */
    public function isEmptyNoTrim($phoneNumber): bool
    {
        return $phoneNumber === null || $phoneNumber === false || $phoneNumber === '';
    }
}

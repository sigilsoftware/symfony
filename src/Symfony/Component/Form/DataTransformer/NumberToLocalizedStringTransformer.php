<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\DataTransformer;

use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms between a number type and a localized number with grouping
 * (each thousand) and comma separators.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 */
class NumberToLocalizedStringTransformer implements DataTransformerInterface
{
    const ROUND_FLOOR    = \NumberFormatter::ROUND_FLOOR;
    const ROUND_DOWN     = \NumberFormatter::ROUND_DOWN;
    const ROUND_HALFDOWN = \NumberFormatter::ROUND_HALFDOWN;
    const ROUND_HALFEVEN = \NumberFormatter::ROUND_HALFEVEN;
    const ROUND_HALFUP   = \NumberFormatter::ROUND_HALFUP;
    const ROUND_UP       = \NumberFormatter::ROUND_UP;
    const ROUND_CEILING  = \NumberFormatter::ROUND_CEILING;

    protected $precision;

    protected $grouping;

    protected $roundingMode;

    public function __construct($precision = null, $grouping = null, $roundingMode = null)
    {
        if (is_null($grouping)) {
            $grouping = false;
        }

        if (is_null($roundingMode)) {
            $roundingMode = self::ROUND_HALFUP;
        }

        $this->precision = $precision;
        $this->grouping = $grouping;
        $this->roundingMode = $roundingMode;
    }

    /**
     * Transforms a number type into localized number.
     *
     * @param  number $value  Number value.
     * @return string         Localized value.
     */
    public function transform($value)
    {
        if (null === $value) {
            return '';
        }

        if (!is_numeric($value)) {
            throw new UnexpectedTypeException($value, 'numeric');
        }

        $formatter = $this->getNumberFormatter();
        $value = $formatter->format($value);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        return $value;
    }

    /**
     * Transforms a localized number into an integer or float
     *
     * @param string $value
     */
    public function reverseTransform($value)
    {
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if ('' === $value) {
            return null;
        }

        $formatter = $this->getNumberFormatter();
        $value = $formatter->parse($value);

        if (intl_is_failure($formatter->getErrorCode())) {
            throw new TransformationFailedException($formatter->getErrorMessage());
        }

        return $value;
    }

    /**
     * Returns a preconfigured \NumberFormatter instance
     *
     * @return \NumberFormatter
     */
    protected function getNumberFormatter()
    {
        $formatter = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);

        if ($this->precision !== null) {
            $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->precision);
            $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, $this->roundingMode);
        }

        $formatter->setAttribute(\NumberFormatter::GROUPING_USED, $this->grouping);

        return $formatter;
    }
}
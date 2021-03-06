<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\DataTransformer;

use Symfony\Component\Form\Util\FormUtil;

class ScalarToChoiceTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return FormUtil::toArrayKey($value);
    }

    public function reverseTransform($value)
    {
        return $value;
    }
}
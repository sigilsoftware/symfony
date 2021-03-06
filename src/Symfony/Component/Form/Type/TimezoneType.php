<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\ChoiceList\TimezoneChoiceList;

class TimezoneType extends AbstractType
{
    public function getDefaultOptions(array $options)
    {
        return array(
            'choice_list' => new TimezoneChoiceList(),
        );
    }

    public function getParent(array $options)
    {
        return 'choice';
    }

    public function getName()
    {
        return 'timezone';
    }
}
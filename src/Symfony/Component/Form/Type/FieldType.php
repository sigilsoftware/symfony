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

use Symfony\Component\Form\Util\PropertyPath;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\EventListener\TrimListener;
use Symfony\Component\Form\Validator\DefaultValidator;
use Symfony\Component\Form\Validator\DelegatingValidator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\ValidatorInterface;

class FieldType extends AbstractType
{
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if (null === $options['property_path']) {
            $options['property_path'] = $builder->getName();
        }

        if (false === $options['property_path'] || '' === $options['property_path']) {
            $options['property_path'] = null;
        } else {
            $options['property_path'] = new PropertyPath($options['property_path']);
        }

        $options['validation_groups'] = empty($options['validation_groups'])
            ? null
            : (array)$options['validation_groups'];

        $builder->setRequired($options['required'])
            ->setReadOnly($options['read_only'])
            ->setErrorBubbling($options['error_bubbling'])
            ->setEmptyData($options['empty_data'])
            ->setAttribute('by_reference', $options['by_reference'])
            ->setAttribute('property_path', $options['property_path'])
            ->setAttribute('validation_groups', $options['validation_groups'])
            ->setAttribute('error_mapping', $options['error_mapping'])
            ->setAttribute('max_length', $options['max_length'])
            ->setAttribute('label', $options['label'] ?: $this->humanize($builder->getName()))
            ->setAttribute('validation_constraint', $options['validation_constraint'])
            ->setData($options['data'])
            ->addValidator(new DefaultValidator())
            ->addValidator(new DelegatingValidator($this->validator));

        if ($options['trim']) {
            $builder->addEventSubscriber(new TrimListener());
        }
    }

    public function buildView(FormView $view, FormInterface $form)
    {
        if ($view->hasParent()) {
            $parentId = $view->getParent()->get('id');
            $parentName = $view->getParent()->get('name');
            $id = sprintf('%s_%s', $parentId, $form->getName());
            $name = sprintf('%s[%s]', $parentName, $form->getName());
        } else {
            $id = $form->getName();
            $name = $form->getName();
        }

        $view->set('form', $view);
        $view->set('id', $id);
        $view->set('name', $name);
        $view->set('errors', $form->getErrors());
        $view->set('value', $form->getClientData());
        $view->set('read_only', $form->isReadOnly());
        $view->set('required', $form->isRequired());
        $view->set('max_length', $form->getAttribute('max_length'));
        $view->set('size', null);
        $view->set('label', $form->getAttribute('label'));
        $view->set('multipart', false);
        $view->set('attr', array());

        $types = array();
        foreach (array_reverse((array) $form->getTypes()) as $type) {
            $types[] = $type->getName();
        }
        $view->set('types', $types);
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'data' => null,
            'data_class' => null,
            'trim' => true,
            'required' => true,
            'read_only' => false,
            'max_length' => null,
            'property_path' => null,
            'by_reference' => true,
            'validation_groups' => null,
            'error_bubbling' => false,
            'error_mapping' => array(),
            'label' => null,
            'validation_constraint' => null,
        );

        if (!empty($options['data_class'])) {
            $class = $options['data_class'];
            $defaultOptions['empty_data'] = function () use ($class) {
                return new $class();
            };
        } else {
            $defaultOptions['empty_data'] = '';
        }

        return $defaultOptions;
    }

    public function createBuilder($name, FormFactoryInterface $factory, array $options)
    {
        return new FormBuilder($name, $factory, new EventDispatcher(), $options['data_class']);
    }

    public function getParent(array $options)
    {
        return null;
    }

    public function getName()
    {
        return 'field';
    }

    private function humanize($text)
    {
        return ucfirst(strtolower(str_replace('_', ' ', $text)));
    }
}

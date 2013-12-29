<?php

/*
 * This file is part of the Ivory Ordered Form package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\OrderedForm;

use Ivory\OrderedForm\Builder\OrderedButtonBuilder;
use Ivory\OrderedForm\Builder\OrderedFormBuilder;
use Ivory\OrderedForm\Builder\OrderedSubmitButtonBuilder;
use Ivory\OrderedForm\Orderer\FormOrdererInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\ButtonTypeInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\ResolvedFormType;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\Form\SubmitButtonTypeInterface;

/**
 * Ordered resolved form type.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class OrderedResolvedFormType extends ResolvedFormType
{
    /** @var \Ivory\OrderedForm\Model\FormOrdererInterface */
    protected $orderer;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        FormOrdererInterface $orderer,
        FormTypeInterface $innerType,
        array $typeExtensions = array(),
        ResolvedFormTypeInterface $parent = null
    ) {
        parent::__construct($innerType, $typeExtensions, $parent);

        $this->orderer = $orderer;
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder(FormFactoryInterface $factory, $name, array $options = array())
    {
        $options = $this->getOptionsResolver()->resolve($options);
        $dataClass = isset($options['data_class']) ? $options['data_class'] : null;

        $builder = $this->newBuilder($name, $dataClass, $factory, $options);
        $builder->setType($this);

        $this->buildForm($builder, $options);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function createView(FormInterface $form, FormView $parent = null)
    {
        $options = $form->getConfig()->getOptions();
        $view = $this->newView($parent);
        $this->buildView($view, $form, $options);

        foreach ($this->orderer->order($form) as $name) {
            $view->children[$name] = $form[$name]->createView($view);
        }

        $this->finishView($view, $form, $options);

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    protected function newBuilder($name, $dataClass, FormFactoryInterface $factory, array $options)
    {
        $innerType = $this->getInnerType();

        if ($innerType instanceof ButtonTypeInterface) {
            return new OrderedButtonBuilder($name, $options);
        }

        if ($innerType instanceof SubmitButtonTypeInterface) {
            return new OrderedSubmitButtonBuilder($name, $options);
        }

        return new OrderedFormBuilder($name, $dataClass, new EventDispatcher(), $factory, $options);
    }
}
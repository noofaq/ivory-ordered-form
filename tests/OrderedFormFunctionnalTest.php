<?php

/*
 * This file is part of the Ivory Ordered Form package.
 *
 * (c) Eric GELOEN <geloen.eric@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Ivory\Tests\OrderedForm;

use Ivory\OrderedForm\Extension\OrderedExtension;
use Ivory\OrderedForm\OrderedResolvedFormTypeFactory;
use Ivory\Tests\OrderedForm\Fixtures\ExtraViewChildrenExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;

/**
 * Ordered form functionnal test.
 *
 * @author GeLo <geloen.eric@gmail.com>
 */
class OrderedFormFunctionnalTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Symfony\Component\Form\FormFactoryBuilderInterface */
    private $factoryBuilder;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->factoryBuilder = Forms::createFormFactoryBuilder()
            ->setResolvedTypeFactory(new OrderedResolvedFormTypeFactory())
            ->addExtension(new OrderedExtension());

        $this->factory = $this->factoryBuilder->getFormFactory();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->factory);
    }

    /**
     * @dataProvider getValidPositions
     */
    public function testValidPosition(array $config, array $expected)
    {
        $this->assertPositions($this->createForm($config)->createView(), $expected);
    }

    /**
     * @dataProvider getInvalidPositions
     */
    public function testInvalidPosition(array $config, $exceptionMessage = null)
    {
        $exceptionName = 'Ivory\OrderedForm\Exception\OrderedConfigurationException';

        if ($exceptionMessage !== null) {
            $this->setExpectedException($exceptionName, $exceptionMessage);
        } else {
            $this->setExpectedException($exceptionName);
        }

        $this->createForm($config)->createView();
    }

    public function testExtraViewChild()
    {
        $view = $this->factoryBuilder
            ->addTypeExtension(new ExtraViewChildrenExtension(array('extra1', 'extra2')))
            ->getFormFactory()
            ->createBuilder()
            ->add('foo', 'form', array('position' => 'last'))
            ->add('bar', 'form', array('position' => 'first'))
            ->getForm()
            ->createView();

        $this->assertPositions($view, array('bar', 'foo', 'extra1', 'extra2'));
    }

    /**
     * Gets the valid positions.
     *
     * @return array The valid positions.
     */
    public function getValidPositions()
    {
        return array(
            // No position
            array(
                array('foo', 'bar', 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // First position
            array(
                array('foo' => 'first', 'bar', 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar', 'baz', 'foo' => 'first', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar', 'baz', 'bat', 'foo' => 'first'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('baz', 'foo' => 'first', 'bat', 'bar' => 'first'),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // Last position
            array(
                array('foo', 'bar', 'baz', 'bat' => 'last'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('foo', 'bar', 'bat' => 'last', 'baz'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bat' => 'last', 'foo', 'bar', 'baz'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('baz' => 'last', 'foo', 'bat' => 'last', 'bar'),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // Before position
            array(
                array('foo' => array('before' => 'bar'), 'bar', 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar', 'foo' => array('before' => 'bar'), 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar', 'baz', 'bat', 'foo' => array('before' => 'bar')),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array(
                    'bar' => array('before' => 'baz'),
                    'foo' => array('before' => 'bar'),
                    'bat',
                    'baz' => array('before' => 'bat'),
                ),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array(
                    'bar' => array('before' => 'bat'),
                    'foo' => array('before' => 'bar'),
                    'bat',
                    'baz' => array('before' => 'bat'),
                ),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // After position
            array(
                array('foo', 'bar' => array('after' => 'foo'), 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar' => array('after' => 'foo'), 'foo', 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('foo', 'baz', 'bat', 'bar' => array('after' => 'foo')),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array(
                    'foo',
                    'baz' => array('after' => 'bar'),
                    'bat' => array('after' => 'baz'),
                    'bar' => array('after' => 'foo'),
                ),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array(
                    'foo',
                    'baz' => array('after' => 'bar'),
                    'bat' => array('after' => 'bar'),
                    'bar' => array('after' => 'foo'),
                ),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // First & last position
            array(
                array('foo' => 'first', 'bar', 'baz', 'bat' => 'last'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar', 'bat' => 'last', 'foo' => 'first', 'baz'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('baz' => 'last', 'foo' => 'first', 'bar' => 'first', 'bat' => 'last'),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // Before & after position
            array(
                array('foo', 'bar' => array('after' => 'foo', 'before' => 'baz'), 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('foo', 'bar' => array('before' => 'baz', 'after' => 'foo'), 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar' => array('after' => 'foo', 'before' => 'baz'), 'foo', 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar' => array('before' => 'baz', 'after' => 'foo'), 'foo', 'baz', 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('foo', 'baz', 'bat', 'bar' => array('after' => 'foo', 'before' => 'baz')),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('foo', 'baz', 'bat', 'bar' => array('before' => 'baz', 'after' => 'foo')),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('foo' => array('before' => 'bar'), 'bar', 'baz' => array('after' => 'bar'), 'bat'),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar', 'foo' => array('before' => 'bar'), 'bat', 'baz' => array('after' => 'bar')),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array('bar' => array('after' => 'foo'), 'foo', 'bat', 'baz' => array('before' => 'bat')),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array(
                    'bar' => array('after' => 'foo', 'before' => 'baz'),
                    'foo',
                    'bat',
                    'baz' => array('before' => 'bat', 'after' => 'bar'),
                ),
                array('foo', 'bar', 'baz', 'bat'),
            ),

            // First, last, before & after position
            array(
                array(
                    'bar' => array('after' => 'foo', 'before' => 'baz'),
                    'foo' => 'first',
                    'bat' => 'last',
                    'baz' => array('before' => 'bat', 'after' => 'bar'),
                ),
                array('foo', 'bar', 'baz', 'bat'),
            ),
            array(
                array(
                    'bar' => array('after' => 'foo', 'before' => 'baz'),
                    'foo' => 'first',
                    'bat',
                    'baz' => array('before' => 'bat'),
                    'nan' => 'last',
                    'pop' => array('after' => 'ban'),
                    'ban',
                    'biz' => array('before' => 'nan'),
                    'boz' => array('before' => 'biz', array('after' => 'pop')),

                ),
                array('foo', 'bar', 'baz', 'bat', 'ban', 'pop', 'boz', 'biz', 'nan'),
            ),
        );
    }

    /**
     * Gets the invalid positions.
     *
     * @return array The invalid positions.
     */
    public function getInvalidPositions()
    {
        return array(
            // Invalid before/after
            array(
                array('foo' => array('before' => 'bar')),
                'The "foo" form is configured to be placed just before the form "bar" but the form "bar" does not exist.',
            ),
            array(
                array('foo' => array('after' => 'bar')),
                'The "foo" form is configured to be placed just after the form "bar" but the form "bar" does not exist.',
            ),

            // Circular before
            array(
                array('foo' => array('before' => 'foo')),
                'The form ordering cannot be resolved due to conflict in before positions ("foo" => "foo")',
            ),
            array(
                array('foo' => array('before' => 'bar'), 'bar' => array('before' => 'foo')),
                'The form ordering cannot be resolved due to conflict in before positions ("bar" => "foo" => "bar").',
            ),
            array(
                array(
                    'foo' => array('before' => 'bar'),
                    'bar' => array('before' => 'baz'),
                    'baz' => array('before' => 'foo'),
                ),
                'The form ordering cannot be resolved due to conflict in before positions ("baz" => "bar" => "foo" => "baz").',
            ),

            // Circular after
            array(
                array('foo' => array('after' => 'foo')),
                'The form ordering cannot be resolved due to conflict in after positions ("foo" => "foo").',
            ),
            array(
                array('foo' => array('after' => 'bar'), 'bar' => array('after' => 'foo')),
                'The form ordering cannot be resolved due to conflict in after positions ("bar" => "foo" => "bar").',
            ),
            array(
                array(
                    'foo' => array('after' => 'bar'),
                    'bar' => array('after' => 'baz'),
                    'baz' => array('after' => 'foo'),
                ),
                'The form ordering cannot be resolved due to conflict in after positions ("baz" => "bar" => "foo" => "baz").',
            ),

            // Symetric before/after
            array(
                array('foo' => array('before' => 'bar'), 'bar' => array('after' => 'foo')),
                'The form ordering does not support symetrical before/after option ("bar" <=> "foo").',
            ),
            array(
                array(
                    'bat' => array('before' => 'baz'),
                    'baz' => array('after' => 'bar'),
                    'foo' => array('before' => 'bar'),
                    'bar' => array('after' => 'foo'),
                ),
                'The form ordering does not support symetrical before/after option ("bar" <=> "foo").',
            ),
        );
    }

    /**
     * Creates a form.
     *
     * @param array $config The form configuration.
     *
     * @return \Symfony\Component\Form\FormInterface The form.
     */
    private function createForm(array $config)
    {
        $builder = $this->factory->createBuilder();

        foreach ($config as $name => $value) {
            if ((is_string($value) && is_string($name)) || is_array($value)) {
                $builder->add($name, 'form', array('position' => $value));
            } else {
                $builder->add($value, 'form');
            }
        }

        return $builder->getForm();
    }

    /**
     * Asserts the positions.
     *
     * @param \Symfony\Component\Form\FormView $view     The form view.
     * @param array                            $expected The expected positions.
     */
    private function assertPositions(FormView $view, array $expected)
    {
        $children = array_values($view->children);

        foreach ($expected as $index => $value) {
            $this->assertArrayHasKey($index, $children);
            $this->assertArrayHasKey($value, $view->children);

            $this->assertSame($children[$index], $view->children[$value]);
        }
    }
}

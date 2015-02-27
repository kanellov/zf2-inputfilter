<?php
require __DIR__ . '/../vendor/autoload.php';

use FUnit as fu;
use Knlv\Zf2\InputFilter\CollectionUniqueInputFilter;
use Zend\InputFilter\BaseInputFilter;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator;
use Zend\Filter;

fu::setup(function () {
    $collectionFilter = new CollectionUniqueInputFilter();
    fu::fixture('collectionFilter', $collectionFilter);

    $foo = new Input();
    $foo->getFilterChain()->attach(new Filter\StringTrim());
    $foo->getValidatorChain()->attach(new Validator\StringLength(3, 6));
    $bar = new Input();
    $bar->getFilterChain()->attach(new Filter\StringTrim());
    $bar->getValidatorChain()->attach(new Validator\Digits());
    $baz = new Input();
    $baz->setRequired(false);
    $baz->getFilterChain()->attach(new Filter\StringTrim());
    $baz->getValidatorChain()->attach(new Validator\StringLength(1, 6));

    $inputFilter      = new BaseInputFilter();
    $inputFilter->add($foo, 'foo')
       ->add($bar, 'bar')
       ->add($baz, 'baz');

    fu::fixture('inputFilter', $inputFilter);
});

fu::test('Test collection inputfilter', function () {
    $collectionFilter = fu::fixture('collectionFilter');
    $inputFilter = fu::fixture('inputFilter');
    $collectionFilter->setInputFilter($inputFilter);
    $data = array(
       array(
            'foo' => 'bazbat',
            'bar' => '54321',
            'baz' => '',
        ),
        array(
            'foo' => 'bazbat',
            'bar' => '54321',
            'baz' => '',
        ),
        array(
            'foo' => 'bazbat',
            'bar' => '54321a',
            'baz' => '',
        ),
    );
    $collectionFilter->setUniqueFields(array('bar'));
    $collectionFilter->setData($data);
    fu::expect_fail('Not inplemented');
});

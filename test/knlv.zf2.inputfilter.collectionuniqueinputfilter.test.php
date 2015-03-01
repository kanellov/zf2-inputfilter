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
    $collectionFilter->setInputFilter($inputFilter);

    fu::fixture('collectionFilter', $collectionFilter);
    fu::fixture('inputFilter', $inputFilter);
});

fu::test('Test inputfilter validates on valid data', function () {
    $data = array(
        array(
            'foo' => ' bazbat ',
            'bar' => ' 12345 ',
            'baz' => '',
        ),
        array(
            'foo' => ' bazbar ',
            'bar' => ' 54321 ',
            'baz' => '',
        ),
    );
    $expected = array(
        array(
            'foo' => 'bazbat',
            'bar' => '12345',
            'baz' => '',
        ),
        array(
            'foo' => 'bazbar',
            'bar' => '54321',
            'baz' => '',
        ),
    );
    $collectionFilter = fu::fixture('collectionFilter');
    $collectionFilter->setData($data);
    fu::ok($collectionFilter->isValid(), 'Assert inputfilter is valid');
    fu::equal(
        $expected,
        $collectionFilter->getValues(),
        'Assert inputfilter returns expected values'
    );
    $messages = $collectionFilter->getMessages();
    fu::ok(
        empty($messages),
        'Assert inputfilter has no error messages'
    );
});

fu::test('Test inputfilter fails validation on invalid data', function () {
    $data = array(
        array(
            'foo' => ' bazbatfoo ',
            'bar' => ' 12345 ',
            'baz' => '',
        ),
        array(
            'foo' => ' bazbar ',
            'bar' => ' abc ',
            'baz' => '',
        ),
    );

    $expected = array(
        array(
            'foo' => 'bazbatfoo',
            'bar' => '12345',
            'baz' => '',
        ),
        array(
            'foo' => 'bazbar',
            'bar' => 'abc',
            'baz' => '',
        ),
    );

    $collectionFilter = fu::fixture('collectionFilter');
    $collectionFilter->setData($data);

    fu::not_ok($collectionFilter->isValid(), 'Assert inputfilter is invalid');
    fu::equal(
        $expected,
        $collectionFilter->getValues(),
        'Assert inputfilter returns expected values'
    );
    $messages = $collectionFilter->getMessages();
    fu::ok(
        isset($messages[0]['foo'][Validator\StringLength::TOO_LONG]) &&
        isset($messages[1]['bar'][Validator\Digits::NOT_DIGITS]),
        'Assert correct error messages'
    );
});

fu::test('Test inputfilter validate for unique values in fields on valid data', function () {
    $data = array(
        array(
            'foo' => ' bazbat ',
            'bar' => ' 12345 ',
            'baz' => '',
        ),
        array(
            'foo' => ' bazbar ',
            'bar' => ' 12345 ',
            'baz' => '',
        ),
    );
    $expected = array(
        array(
            'foo' => 'bazbat',
            'bar' => '12345',
            'baz' => '',
        ),
        array(
            'foo' => 'bazbar',
            'bar' => '12345',
            'baz' => '',
        ),
    );

    $collectionFilter = fu::fixture('collectionFilter');
    $collectionFilter->setData($data);
    $collectionFilter->setUniqueFields(array('foo'));
    fu::ok($collectionFilter->isValid(), 'Assert inputfilter is valid');
    $messages = $collectionFilter->getMessages();
    fu::ok(
        empty($messages),
        'Assert inputfilter has no error messages'
    );
    fu::equal(
        $expected,
        $collectionFilter->getValues(),
        'Assert inputfilter returns expected values'
    );

    $collectionFilter->setUniqueFields(array('bar'));
    fu::not_ok($collectionFilter->isValid(), 'Assert inputfilter is invalid');
    $messages = $collectionFilter->getMessages();
    fu::ok(
        isset($messages[1]['bar'][$collectionFilter::NOT_UNIQUE]),
        'Assert message for not unique isset'
    );
    fu::equal(
        $expected,
        $collectionFilter->getValues(),
        'Assert inputfilter returns expected values'
    );
});

fu::test('Test inputfilter validate for unique values in fields on invalid data', function () {
    $data = array(
        array(
            'foo' => ' bazbatfoo ',
            'bar' => ' 12345 ',
            'baz' => '',
        ),
        array(
            'foo' => ' bazbatfoo ',
            'bar' => ' 54321 ',
            'baz' => '',
        ),
    );
    $expected = array(
        array(
            'foo' => 'bazbatfoo',
            'bar' => '12345',
            'baz' => '',
        ),
        array(
            'foo' => 'bazbatfoo',
            'bar' => '54321',
            'baz' => '',
        ),
    );

    $collectionFilter = fu::fixture('collectionFilter');
    $collectionFilter->setData($data);
    $collectionFilter->setUniqueFields(array('bar'));
    fu::not_ok($collectionFilter->isValid(), 'Assert inputfilter is invalid');
    $messages = $collectionFilter->getMessages();
    fu::ok(
        isset($messages[0]['foo'][Validator\StringLength::TOO_LONG]),
        'Assert correct error messages'
    );
    fu::equal(
        $expected,
        $collectionFilter->getValues(),
        'Assert inputfilter returns expected values'
    );

    $collectionFilter->setUniqueFields(array('foo'));
    fu::not_ok($collectionFilter->isValid(), 'Assert inputfilter is invalid');
    $messages = $collectionFilter->getMessages();
    fu::ok(
        isset($messages[0]['foo'][Validator\StringLength::TOO_LONG]) &&
        isset($messages[1]['foo'][Validator\StringLength::TOO_LONG]) &&
        isset($messages[1]['foo'][$collectionFilter::NOT_UNIQUE]),
        'Assert message for not unique isset along with other'
    );
    fu::equal(
        $expected,
        $collectionFilter->getValues(),
        'Assert inputfilter returns expected values'
    );
});

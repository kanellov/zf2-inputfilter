<?php

/**
 * Knlv\Zf2\InputFilter\CollectionUniqueInputFilter
 *
 * @link https://github.com/kanellov/zf2-inputfilter
 * @copyright Copyright (c) 2015 Vassilis Kanellopoulos - contact@kanellov.com
 * @license https://raw.githubusercontent.com/kanellov/zf2-inputfilter/master/LICENSE
 */

namespace Knlv\Zf2\InputFilter;

use ArrayAccess;
use Traversable;
use Zend\InputFilter\CollectionInputFilter;
use Zend\InputFilter\Exception\InvalidArgumentException;
use Zend\Stdlib\ArrayUtils;

class CollectionUniqueInputFilter extends CollectionInputFilter
{
    const NOT_UNIQUE = 'collectionNotUnique';

    /**
     * @var array
     */
    protected $uniqueFields = array();

    /**
     * @var array
     */
    protected $collectionData = array();

    /**
     * @var array
     */
    protected $collectionMessages = array();

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_UNIQUE => 'Each input must be unique within the collection',
    );

    public function isValid()
    {
        $isValid = parent::isValid();

        foreach ($this->getUniqueFields() as $uniqueField) {
            $validatedValues = array();
            foreach ($this->collectionData as $key => $values) {
                if (!is_array($values)) {
                    $values = array();
                }

                if (array_key_exists($uniqueField, $values)) {
                    $validatedValues[$key] = $values[$uniqueField];
                }
            }
            unset($key);
            $uniqueValues = array_values(
                array_unique($validatedValues, SORT_REGULAR)
            );

            if (count($uniqueValues) < count($validatedValues)) {
                $isValid = false;
                $notUnique = array_keys(
                    array_diff_assoc($validatedValues, $uniqueValues)
                );
                foreach ($notUnique as $key) {
                    $this->collectionMessages[$key][$uniqueField][self::NOT_UNIQUE] =
                        $this->messageTemplates[self::NOT_UNIQUE];
                }
            }
        }

        return $isValid;

    }

    public function setData($data)
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            throw new InvalidArgumentException(sprintf(
                '%s expects an array or Traversable argument; received %s',
                __METHOD__,
                (is_object($data) ? get_class($data) : gettype($data))
            ));
        }
        if (is_object($data) && !$data instanceof ArrayAccess) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        $this->collectionData = $data;

        return parent::setData($data);
    }

    /**
     * Gets the value of uniqueFields.
     *
     * @return array
     */
    public function getUniqueFields()
    {
        return $this->uniqueFields;
    }

    /**
     * Sets the value of uniqueFields.
     *
     * @param array $uniqueFields the unique fields
     *
     * @return self
     */
    public function setUniqueFields(array $uniqueFields)
    {
        $this->uniqueFields = $uniqueFields;

        return $this;
    }

    public function getMessages()
    {
        $messages = parent::getMessages();
        return array_replace_recursive($messages, $this->collectionMessages);
    }
}

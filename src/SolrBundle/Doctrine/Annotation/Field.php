<?php

/*
 * Solr Bundle
 * This is a fork of the unmaintained solr bundle from Florian Semm.
 *
 * @author Daan Biesterbos     (fork maintainer)
 * @author Florian Semm (author original bundle)
 *
 * Issues can be submitted here:
 * https://github.com/daanbiesterbos/SolrBundle/issues
 */

namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Defines a field of a solr-document.
 *
 * @Annotation
 */
class Field extends Annotation
{
    /**
     * @var array
     */
    private static $TYP_MAPPING = [];

    /**
     * @var array
     */
    private static $TYP_SIMPLE_MAPPING = [
        'string' => '_s',
        'text' => '_t',
        'date' => '_dt',
        'boolean' => '_b',
        'integer' => '_i',
        'long' => '_l',
        'float' => '_f',
        'double' => '_d',
        'datetime' => '_dt',
        'point' => '_p',
    ];

    /**
     * @var array
     */
    private static $TYP_COMPLEX_MAPPING = [
        'doubles' => '_ds',
        'floats' => '_fs',
        'longs' => '_ls',
        'integers' => '_is',
        'booleans' => '_bs',
        'dates' => '_dts',
        'texts' => '_txt',
        'strings' => '_ss',
    ];

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $boost = 0;

    /**
     * @var string
     */
    public $getter;

    /**
     * @var string
     */
    public $fieldModifier;

    /**
     * @var string
     */
    public $nestedClass;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public static function getComplexFieldMapping()
    {
        return self::$TYP_COMPLEX_MAPPING;
    }

    /**
     * returns field name with type-suffix:.
     *
     * eg: title_s
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getNameWithAlias()
    {
        return $this->normalizeName($this->name).$this->getTypeSuffix($this->type);
    }

    /**
     * Related object getter name.
     *
     * @return string
     */
    public function getGetterName()
    {
        return $this->getter;
    }

    /**
     * @return string
     */
    public function getFieldModifier()
    {
        return $this->fieldModifier;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @throws \InvalidArgumentException if boost is not a number
     *
     * @return number
     */
    public function getBoost()
    {
        if (!is_numeric($this->boost)) {
            throw new \InvalidArgumentException(sprintf('Invalid boost value %s', $this->boost));
        }

        if (($boost = (float) ($this->boost)) > 0) {
            return $boost;
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getTypeSuffix($type)
    {
        self::$TYP_MAPPING = array_merge(self::$TYP_COMPLEX_MAPPING, self::$TYP_SIMPLE_MAPPING);

        if ('' === $type) {
            return '';
        }

        if (!isset(self::$TYP_MAPPING[$this->type])) {
            return '';
        }

        return self::$TYP_MAPPING[$this->type];
    }

    /**
     * normalize class attributes camelcased names to underscores
     * (according to solr specification, document field names should
     * contain only lowercase characters and underscores to maintain
     * retro compatibility with old components).
     *
     * @param $name The field name
     *
     * @return string normalized field name
     */
    private function normalizeName($name)
    {
        $words = preg_split('/(?=[A-Z])/', $name);
        $words = array_map(
            function ($value) {
                return mb_strtolower($value);
            },
            $words
        );

        return implode('_', $words);
    }
}

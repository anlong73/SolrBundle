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

namespace FS\SolrBundle\Tests\Doctrine\Annotation;

use FS\SolrBundle\Doctrine\Annotation\Field;

/**
 * @group annotation
 */
class FieldTest extends \PHPUnit\Framework\TestCase
{
    public function testGetNameWithAlias_String()
    {
        $field = new Field(['name' => 'test', 'type' => 'string']);
        $this->assertEquals('test_s', $field->getNameWithAlias());
    }

    public function testGetNameWithAlias_Text()
    {
        $field = new Field(['name' => 'test', 'type' => 'text']);
        $this->assertEquals('test_t', $field->getNameWithAlias());
    }

    public function testGetNameWithAlias_Date()
    {
        $field = new Field(['name' => 'test', 'type' => 'date']);
        $this->assertEquals('test_dt', $field->getNameWithAlias());
    }

    public function testGetNameWithAlias_Boolean()
    {
        $field = new Field(['name' => 'test', 'type' => 'boolean']);
        $this->assertEquals('test_b', $field->getNameWithAlias());
    }

    public function testGetNameWithAlias_NoFieldType()
    {
        $field = new Field(['name' => 'title']);
        $this->assertEquals('title', $field->getNameWithAlias());
    }

    public function testGetNameWithAlias_Integer()
    {
        $field = new Field(['name' => 'test', 'type' => 'integer']);
        $this->assertEquals('test_i', $field->getNameWithAlias());
    }

    public function testNormalizeName_CamelCase()
    {
        $field = new Field(['name' => 'testCamelCase', 'type' => 'string']);

        $meta = new \ReflectionClass($field);
        $method = $meta->getMethod('normalizeName');
        $method->setAccessible(true);
        $result = $method->invoke($field, $field->name);

        $this->assertEquals('test_camel_case', $result);
    }

    public function testNormalizeName_Underscore()
    {
        $field = new Field(['name' => 'test_underscore', 'type' => 'string']);

        $meta = new \ReflectionClass($field);
        $method = $meta->getMethod('normalizeName');
        $method->setAccessible(true);
        $result = $method->invoke($field, $field->name);

        $this->assertEquals('test_underscore', $result);
    }

    public function testCostomFieldType()
    {
        $field = new Field(['name' => 'costumtype', 'type' => 'my_special_type']);

        $this->assertEquals('costumtype', $field->getNameWithAlias());
    }
}

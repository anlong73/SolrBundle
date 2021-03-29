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

namespace FS\SolrBundle\Tests\Doctrine\Mapping\Mapper;

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Annotation as Solr;
use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\SolrMappingException;
use FS\SolrBundle\Tests\Fixtures\EntityWithRepository;
use FS\SolrBundle\Tests\Fixtures\NotIndexedEntity;
use FS\SolrBundle\Tests\Fixtures\ValidEntityRepository;
use FS\SolrBundle\Tests\Fixtures\ValidOdmTestDocument;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityFiltered;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityFloatBoost;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityIndexHandler;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityIndexProperty;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityNoBoost;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityNoTypes;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityNumericFields;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithInvalidBoost;

/**
 * @group annotation
 */
class AnnotationReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    public function testGetFields_NoFieldsDected()
    {
        $fields = $this->reader->getFields(new NotIndexedEntity());

        $this->assertEquals(0, count($fields));
    }

    public function testGetFields_ThreeFieldsDetected()
    {
        $fields = $this->reader->getFields(new ValidTestEntity());

        $this->assertEquals(5, count($fields), '5 fields are mapped');
    }

    public function testGetFields_OneFieldsOneTypes()
    {
        $fields = $this->reader->getFields(new ValidTestEntityNoTypes());
        $this->assertEquals(1, count($fields), '1 fields are mapped');
        $field = $fields[0];
        $this->assertTrue($field instanceof Field);
        $this->assertEquals('title', $field->getNameWithAlias());
    }

    /**
     * @test
     */
    public function shouldFailToGetUndefinedIdentifier(): void
    {
        $this->expectException(Solr\AnnotationReaderException::class);
        $this->expectExceptionMessage('no identifer declared in entity FS\SolrBundle\Tests\Fixtures\NotIndexedEntity');
        $this->reader->getIdentifier(new NotIndexedEntity());
    }

    /**
     * @test
     */
    public function shouldGetIdentifier(): void
    {
        $id = $this->reader->getIdentifier(new ValidTestEntity());

        $this->assertEquals('id', $id->name);
        $this->assertFalse($id->generateId);
    }

    public function testGetFieldMapping_ThreeMappingsAndId(): void
    {
        $fields = $this->reader->getFieldMapping(new ValidTestEntity());
        $this->assertEquals(6, count($fields), 'six fields are mapped');
        $this->assertTrue(array_key_exists('title', $fields));
        $this->assertTrue(array_key_exists('id', $fields));
    }

    public function testGetRepository_ValidRepositoryDeclared(): void
    {
        $repositoryClassname = $this->reader->getRepository(new EntityWithRepository());
        $this->assertEquals(ValidEntityRepository::class, $repositoryClassname, 'wrong declared repository');
    }

    public function testGetRepository_NoRepositoryAttributSet(): void
    {
        $repository = $this->reader->getRepository(new ValidTestEntity());

        $expected = '';
        $actual = $repository;
        $this->assertEquals($expected, $actual, 'no repository was declared');
    }

    public function testGetBoost(): void
    {
        $boost = $this->reader->getEntityBoost(new ValidTestEntity());

        $this->assertEquals(1, $boost);
    }

    /**
     * @test
     */
    public function shouldFailToGetNonNumericBoost(): void
    {
        $this->expectException(Solr\AnnotationReaderException::class);
        $this->expectExceptionMessage('Invalid boost value "aaaa" in class "FS\SolrBundle\Tests\Fixtures\ValidTestEntityWithInvalidBoost" configured');
        $this->reader->getEntityBoost(new ValidTestEntityWithInvalidBoost());
    }

    public function testGetBoost_BoostIsNumberic()
    {
        $boost = $this->reader->getEntityBoost(new ValidTestEntityFloatBoost());

        $this->assertEquals(1.4, $boost);
    }

    public function testGetBoost_BoostIsNull()
    {
        $boost = $this->reader->getEntityBoost(new ValidTestEntityNoBoost());

        $this->assertNull($boost);
    }

    public function testGetCallback_CallbackDefined()
    {
        $callback = $this->reader->getSynchronizationCallback(new ValidTestEntityFiltered());

        $this->assertEquals('shouldBeIndex', $callback);
    }

    public function testGetCallback_NoCallbackDefined()
    {
        $callback = $this->reader->getSynchronizationCallback(new ValidTestEntity());

        $this->assertEquals('', $callback);
    }

    /**
     * @test
     */
    public function numericFieldTypeAreSupported()
    {
        $fields = $this->reader->getFields(new ValidTestEntityNumericFields());

        $this->assertEquals(4, count($fields));

        $expectedFields = ['integer_i', 'double_d', 'float_f', 'long_l'];
        $actualFields = [];
        foreach ($fields as $field) {
            $actualFields[] = $field->getNameWithAlias();
        }

        $this->assertEquals($expectedFields, $actualFields);
    }

    /**
     * @test
     */
    public function getIndexFromAnnotationProperty()
    {
        $index = $this->reader->getDocumentIndex(new ValidTestEntityIndexProperty());

        $this->assertEquals('my_core', $index);
    }

    /**
     * @test
     */
    public function getIndexFromIndexHandler()
    {
        $index = $this->reader->getDocumentIndex(new ValidTestEntityIndexHandler());

        $this->assertEquals('my_core', $index);
    }

    /**
     * @test
     */
    public function readAnnotationsFromBaseClass()
    {
        $fields = $this->reader->getFields(new ChildEntity());

        $this->assertEquals(3, count($fields));
        $this->assertTrue($this->reader->hasDocumentDeclaration(new ChildEntity()));
    }

    /**
     * @test
     */
    public function readAnnotationsOfNestedObject()
    {
        $this->assertTrue($this->reader->hasDocumentDeclaration(new NestedObject()));
    }

    /**
     * @test
     */
    public function readAnnotationsFromMultipleClassHierarchy()
    {
        $fields = $this->reader->getFields(new ChildEntity2());

        $this->assertEquals(4, count($fields));
    }

    /**
     * @test
     */
    public function readGetterMethodWithParameters()
    {
        /** @var Field[] $fields */
        $fields = $this->reader->getFields(new EntityWithObject());

        $this->assertCount(1, $fields);
        $this->assertEquals('format(\'d.m.Y\')', $fields[0]->getGetterName());

        $this->assertEquals('object_dt', $fields[0]->getNameWithAlias());
    }

    /**
     * @test
     */
    public function checkIfPlainObjectIsNotDoctrineEntity()
    {
        $this->assertFalse($this->reader->isOrm(new ChildEntity()), 'is not a doctrine entity');
    }

    /**
     * @test
     */
    public function checkIfValidEntityIsDoctrineEntity()
    {
        $this->assertTrue($this->reader->isOrm(new ValidTestEntity()), 'is a doctrine entity');
    }

    /**
     * @test
     */
    public function checkIfPlainObjectIsNotDoctrineDocument()
    {
        $this->assertFalse($this->reader->isOdm(new ChildEntity()), 'is not a doctrine document');
    }

    /**
     * @test
     */
    public function checkIfValidDocumentIsDoctrineDocument()
    {
        $this->assertTrue($this->reader->isOdm(new ValidOdmTestDocument()), 'is a doctrine document');
    }

    /**
     * @test
     */
    public function methodWithAnnotationShouldHaveField(): void
    {
        $this->expectException(SolrMappingException::class);
        $this->reader->getMethods(new EntityMissingNameProperty());
    }

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());
    }
}

/**
 * @Solr\Document
 */
abstract class BaseEntity
{
    /**
     * @var mixed
     */
    protected $baseField1;

    /**
     * @Solr\Field(type="integer")
     */
    protected $baseField2;
}

class ChildEntity extends BaseEntity
{
    /**
     * @Solr\Field(type="integer")
     */
    protected $baseField1;

    /**
     * @Solr\Field(type="integer")
     */
    protected $childField1;
}

class ChildEntity2 extends ChildEntity
{
    /**
     * @Solr\Field(type="integer")
     */
    private $childField2;
}

class EntityWithObject
{
    /**
     * @Solr\Field(type="datetime", getter="format('d.m.Y')")
     */
    private $object;
}

/**
 * @Solr\Nested()
 */
class NestedObject
{
}

/** @Solr\Document() */
class EntityMissingNameProperty
{
    /** @Solr\Field(type="string") */
    public function getPropertyValue2()
    {
        return 1234;
    }
}

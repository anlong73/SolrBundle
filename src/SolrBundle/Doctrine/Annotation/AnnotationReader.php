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
use Doctrine\Common\Annotations\Reader;
use FS\SolrBundle\Doctrine\Mapper\SolrMappingException;

class AnnotationReader
{
    const DOCUMENT_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Document';
    const DOCUMENT_NESTED_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Nested';
    const FIELD_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Field';
    const FIELD_IDENTIFIER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Id';
    const SYNCHRONIZATION_FILTER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\SynchronizationFilter';
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $entityProperties;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    public function getFields($entity)
    {
        return $this->getPropertiesByType($entity, self::FIELD_CLASS);
    }

    /**
     * @param object $entity
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    public function getMethods($entity)
    {
        $reflectionClass = new \ReflectionClass($entity);

        $methods = [];
        foreach ($reflectionClass->getMethods() as $method) {
            /** @var Field $annotation */
            $annotation = $this->reader->getMethodAnnotation($method, self::FIELD_CLASS);

            if (null === $annotation) {
                continue;
            }

            $annotation->value = $method->invoke($entity);

            if (empty($annotation->name)) {
                throw new SolrMappingException(sprintf('Please configure a field-name for method "%s" with field-annotation in class "%s"', $method->getName(), get_class($entity)));
            }

            $methods[] = $annotation;
        }

        return $methods;
    }

    /**
     * @param object $entity
     *
     * @throws AnnotationReaderException if the boost value is not numeric
     *
     * @return number
     */
    public function getEntityBoost($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_CLASS);

        if (!$annotation instanceof Document) {
            return 0;
        }

        $boostValue = $annotation->getBoost();
        if (!is_numeric($boostValue)) {
            throw new AnnotationReaderException(sprintf('Invalid boost value "%s" in class "%s" configured', $boostValue, get_class($entity)));
        }

        if (0 === $boostValue) {
            return null;
        }

        return $boostValue;
    }

    /**
     * @param object $entity
     *
     * @return string
     */
    public function getDocumentIndex($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_CLASS);
        if (!$annotation instanceof Document) {
            return null;
        }

        $indexHandler = $annotation->indexHandler;
        if ('' !== $indexHandler && method_exists($entity, $indexHandler)) {
            return $entity->$indexHandler();
        }

        return $annotation->getIndex();
    }

    /**
     * @param object $entity
     *
     * @throws AnnotationReaderException if given $entity has no identifier
     *
     * @return Id
     */
    public function getIdentifier($entity)
    {
        $id = $this->getPropertiesByType($entity, self::FIELD_IDENTIFIER_CLASS);

        if (0 === count($id)) {
            throw new AnnotationReaderException('no identifer declared in entity '.get_class($entity));
        }

        return reset($id);
    }

    /**
     * @param object $entity
     *
     * @return string classname of repository
     */
    public function getRepository($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_CLASS);

        if ($annotation instanceof Document) {
            return $annotation->repository;
        }

        return '';
    }

    /**
     * returns all fields and field for idendification.
     *
     * @param object $entity
     *
     * @return array
     */
    public function getFieldMapping($entity)
    {
        $fields = $this->getPropertiesByType($entity, self::FIELD_CLASS);

        $mapping = [];
        foreach ($fields as $field) {
            $mapping[$field->getNameWithAlias()] = $field->name;
        }

        $id = $this->getIdentifier($entity);
        $mapping['id'] = $id->name;

        return $mapping;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function hasDocumentDeclaration($entity)
    {
        if ($rootDocument = $this->getClassAnnotation($entity, self::DOCUMENT_CLASS)) {
            return true;
        }

        if ($this->isNested($entity)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $entity
     *
     * @return string
     */
    public function getSynchronizationCallback($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::SYNCHRONIZATION_FILTER_CLASS);

        if (!$annotation) {
            return '';
        }

        return $annotation->callback;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isOrm($entity)
    {
        $annotation = $this->getClassAnnotation($entity, 'Doctrine\ORM\Mapping\Entity');

        if (null === $annotation) {
            return false;
        }

        return true;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isOdm($entity)
    {
        $annotation = $this->getClassAnnotation($entity, 'Doctrine\ODM\MongoDB\Mapping\Annotations\Document');

        if (null === $annotation) {
            return false;
        }

        return true;
    }

    /**
     * @param object $entity
     *
     * @return bool
     */
    public function isNested($entity)
    {
        if ($nestedDocument = $this->getClassAnnotation($entity, self::DOCUMENT_NESTED_CLASS)) {
            return true;
        }

        return false;
    }

    /**
     * reads the entity and returns a set of annotations.
     *
     * @param object $entity
     * @param string $type
     *
     * @return Annotation[]
     */
    private function getPropertiesByType($entity, $type)
    {
        $properties = $this->readClassProperties($entity);

        $fields = [];
        foreach ($properties as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, $type);

            if (null === $annotation) {
                continue;
            }

            $property->setAccessible(true);
            $annotation->value = $property->getValue($entity);
            $annotation->name = $property->getName();

            $fields[] = $annotation;
        }

        return $fields;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     *
     * @return \ReflectionProperty[]
     */
    private function getParentProperties(\ReflectionClass $reflectionClass)
    {
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            return array_merge($reflectionClass->getProperties(), $this->getParentProperties($parent));
        }

        return $reflectionClass->getProperties();
    }

    /**
     * @param string $entity
     * @param string $annotationName
     *
     * @return Annotation|null
     */
    private function getClassAnnotation($entity, $annotationName)
    {
        $reflectionClass = new \ReflectionClass($entity);

        $annotation = $this->reader->getClassAnnotation($reflectionClass, $annotationName);

        if (null === $annotation && $reflectionClass->getParentClass()) {
            $annotation = $this->reader->getClassAnnotation($reflectionClass->getParentClass(), $annotationName);
        }

        return $annotation;
    }

    /**
     * @param object $entity
     *
     * @return \ReflectionProperty[]
     */
    private function readClassProperties($entity)
    {
        $className = get_class($entity);
        if (isset($this->entityProperties[$className])) {
            return $this->entityProperties[$className];
        }

        $reflectionClass = new \ReflectionClass($entity);
        $inheritedProperties = array_merge($this->getParentProperties($reflectionClass), $reflectionClass->getProperties());

        $properties = [];
        foreach ($inheritedProperties as $property) {
            $properties[$property->getName()] = $property;
        }

        $this->entityProperties[$className] = $properties;

        return $properties;
    }
}

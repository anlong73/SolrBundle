<?php

namespace FS\SolrBundle\Doctrine\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Reader;

class AnnotationReader
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $entityProperties;

    const DOCUMENT_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Document';
    const FIELD_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Field';
    const FIELD_IDENTIFIER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Id';
    const DOCUMENT_INDEX_CLASS = 'FS\SolrBundle\Doctrine\Annotation\Document';
    const SYNCHRONIZATION_FILTER_CLASS = 'FS\SolrBundle\Doctrine\Annotation\SynchronizationFilter';

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * reads the entity and returns a set of annotations
     *
     * @param object $entity
     * @param string $type
     *
     * @return Annotation[]
     */
    private function getPropertiesByType($entity, $type)
    {
        $properties = $this->readClassProperties($entity);

        $fields = array();
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
        if ($parent != null) {
            return array_merge($reflectionClass->getProperties(), $this->getParentProperties($parent));
        }

        return $reflectionClass->getProperties();
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
     * @return number
     *
     * @throws \InvalidArgumentException if the boost value is not numeric
     */
    public function getEntityBoost($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_INDEX_CLASS);

        if (!$annotation instanceof Document) {
            return 0;
        }

        try {
            $boostValue = $annotation->getBoost();
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf($e->getMessage() . ' for entity %s', get_class($entity)));
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
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_INDEX_CLASS);
        if (!$annotation instanceof Document) {
            return '';
        }

        $indexHandler = $annotation->indexHandler;
        if ($indexHandler != '' && method_exists($entity, $indexHandler)) {
            return $entity->$indexHandler();
        }

        return $annotation->getIndex();
    }

    /**
     * @param object $entity
     *
     * @return Id
     *
     * @throws \RuntimeException
     */
    public function getIdentifier($entity)
    {
        $id = $this->getPropertiesByType($entity, self::FIELD_IDENTIFIER_CLASS);

        if (count($id) == 0) {
            throw new \RuntimeException('no identifer declared in entity ' . get_class($entity));
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
     * returns all fields and field for idendification
     *
     * @param object $entity
     *
     * @return array
     */
    public function getFieldMapping($entity)
    {
        $fields = $this->getPropertiesByType($entity, self::FIELD_CLASS);

        $mapping = array();
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                $mapping[$field->getNameWithAlias()] = $field->name;
            }
        }

        $id = $this->getIdentifier($entity);
        $mapping['id'] = $id->name;

        return $mapping;
    }

    /**
     * @param object $entity
     *
     * @return boolean
     */
    public function hasDocumentDeclaration($entity)
    {
        $annotation = $this->getClassAnnotation($entity, self::DOCUMENT_INDEX_CLASS);

        return $annotation !== null;
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

        if ($annotation === null) {
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

        if ($annotation === null) {
            return false;
        }

        return true;
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

        if ($annotation === null && $reflectionClass->getParentClass()) {
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

        $properties = array();
        foreach ($inheritedProperties as $property) {
            $properties[$property->getName()] = $property;
        }

        $this->entityProperties[$className] = $properties;

        return $properties;
    }
}

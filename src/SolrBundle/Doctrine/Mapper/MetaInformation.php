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

namespace FS\SolrBundle\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Annotation\Id;

/**
 * Holds meta-information about an entity.
 */
class MetaInformation implements MetaInformationInterface
{
    /**
     * @var Id
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $className = '';

    /**
     * @var string
     */
    private $documentName = '';

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var array
     */
    private $fieldMapping = [];

    /**
     * @var string
     */
    private $repository = '';

    /**
     * @var object
     */
    private $entity = null;

    /**
     * @var number
     */
    private $boost = 0;

    /**
     * @var string
     */
    private $synchronizationCallback = '';

    /**
     * @var string
     */
    private $index = '';

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var bool
     */
    private $isDoctrineEntity;

    /**
     * @var string
     */
    private $doctrineMapperType;

    /**
     * @var bool
     */
    private $nested;

    /**
     * {@inheritdoc}
     */
    public function getEntityId()
    {
        if (null !== $this->entity && $this->entity->getId()) {
            return $this->entity->getId();
        }

        return $this->entityId;
    }

    /**
     * @param int $entityId
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentName()
    {
        return $this->documentName;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param Id $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @param string $documentName
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }

    /**
     * @param Field[] $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField($fieldName)
    {
        $fields = array_filter($this->fields, function (Field $field) use ($fieldName) {
            return $field->name === $fieldName || $field->getNameWithAlias() === $fieldName;
        });

        if (0 === count($fields)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $fieldName
     * @param string $value
     *
     * @throws SolrMappingException if $fieldName does not exist
     */
    public function setFieldValue($fieldName, $value)
    {
        if (false === $this->hasField($fieldName)) {
            throw new SolrMappingException(sprintf('Field %s does not exist', $fieldName));
        }

        $field = $this->getField($fieldName);
        $field->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getField($fieldName)
    {
        if ('' === $fieldName) {
            throw new SolrMappingException('$fieldName must not be empty');
        }

        if (!$this->hasField($fieldName)) {
            return null;
        }

        $fields = array_filter($this->fields, function (Field $field) use ($fieldName) {
            return $field->name === $fieldName || $field->getNameWithAlias() === $fieldName;
        });

        return array_pop($fields);
    }

    /**
     * @param string $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param object $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldMapping()
    {
        return $this->fieldMapping;
    }

    /**
     * @param array $fieldMapping
     */
    public function setFieldMapping($fieldMapping)
    {
        $this->fieldMapping = $fieldMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getBoost()
    {
        return $this->boost;
    }

    /**
     * @param number $boost
     */
    public function setBoost($boost)
    {
        $this->boost = $boost;
    }

    /**
     * @return bool
     */
    public function hasSynchronizationFilter()
    {
        if ('' === $this->synchronizationCallback) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSynchronizationCallback()
    {
        return $this->synchronizationCallback;
    }

    /**
     * @param string $synchronizationCallback
     */
    public function setSynchronizationCallback($synchronizationCallback)
    {
        $this->synchronizationCallback = $synchronizationCallback;
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentKey()
    {
        return $this->documentName.'_'.$this->getEntityId();
    }

    /**
     * @return bool
     */
    public function isDoctrineEntity()
    {
        return $this->isDoctrineEntity;
    }

    /**
     * @param bool $isDoctrineEntity
     */
    public function setIsDoctrineEntity($isDoctrineEntity)
    {
        $this->isDoctrineEntity = $isDoctrineEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldName()
    {
        return $this->identifier->name;
    }

    /**
     * @return string
     */
    public function getDoctrineMapperType()
    {
        return $this->doctrineMapperType;
    }

    /**
     * @param string $doctrineMapperType
     */
    public function setDoctrineMapperType($doctrineMapperType)
    {
        $this->doctrineMapperType = $doctrineMapperType;
    }

    /**
     * {@inheritdoc}
     */
    public function generateDocumentId()
    {
        if (null === $this->identifier) {
            throw new SolrMappingException('No identifier is set');
        }

        return $this->identifier->generateId;
    }

    /**
     * {@inheritdoc}
     */
    public function isNested()
    {
        return $this->nested;
    }

    /**
     * @param bool $nested
     */
    public function setNested(bool $nested)
    {
        $this->nested = $nested;
    }
}

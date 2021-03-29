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

namespace FS\SolrBundle\Doctrine\Hydration;

use Doctrine\Persistence\ManagerRegistry;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;

/**
 * A doctrine-hydrator finds the entity for a given solr-document. This entity is updated with the solr-document values.
 *
 * The hydration is necessary because fields, which are not declared as solr-field, will not populate in the result.
 */
class DoctrineHydrator implements HydratorInterface
{
    /**
     * @var ManagerRegistry
     */
    private $ormManager;

    /**
     * @var ManagerRegistry
     */
    private $odmManager;

    /**
     * @var HydratorInterface
     */
    private $valueHydrator;

    /**
     * @param HydratorInterface $valueHydrator
     */
    public function __construct(HydratorInterface $valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
    }

    /**
     * @param ManagerRegistry $ormManager
     */
    public function setOrmManager($ormManager)
    {
        $this->ormManager = $ormManager;
    }

    /**
     * @param ManagerRegistry $odmManager
     */
    public function setOdmManager($odmManager)
    {
        $this->odmManager = $odmManager;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($document, MetaInformationInterface $metaInformation)
    {
        $entityId = $this->valueHydrator->removePrefixedKeyValues($document['id']);

        $doctrineEntity = null;
        if (MetaInformationInterface::DOCTRINE_MAPPER_TYPE_RELATIONAL === $metaInformation->getDoctrineMapperType()) {
            $doctrineEntity = $this->ormManager
                ->getManager()
                ->getRepository($metaInformation->getClassName())
                ->find($entityId);
        } elseif (MetaInformationInterface::DOCTRINE_MAPPER_TYPE_DOCUMENT === $metaInformation->getDoctrineMapperType()) {
            $doctrineEntity = $this->odmManager
                ->getManager()
                ->getRepository($metaInformation->getClassName())
                ->find($entityId);
        }

        if (null !== $doctrineEntity) {
            $metaInformation->setEntity($doctrineEntity);
        }

        return $this->valueHydrator->hydrate($document, $metaInformation);
    }
}

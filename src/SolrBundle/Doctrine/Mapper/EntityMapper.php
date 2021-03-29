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

use FS\SolrBundle\Doctrine\Hydration\HydrationModes;
use FS\SolrBundle\Doctrine\Hydration\HydratorInterface;
use FS\SolrBundle\Doctrine\Mapper\Factory\DocumentFactory;

class EntityMapper implements EntityMapperInterface
{
    /**
     * @var HydratorInterface
     */
    private $doctrineHydrator;

    /**
     * @var HydratorInterface
     */
    private $indexHydrator;

    /**
     * @var string
     */
    private $hydrationMode = '';

    /**
     * @var MetaInformationFactory
     */
    private $metaInformationFactory;

    /**
     * @var DocumentFactory
     */
    private $documentFactory;

    /**
     * @param HydratorInterface      $dbHydrator
     * @param HydratorInterface      $indexHydrator
     * @param MetaInformationFactory $metaInformationFactory
     */
    public function __construct(
        HydratorInterface $dbHydrator,
        HydratorInterface $indexHydrator,
        MetaInformationFactory $metaInformationFactory
    ) {
        $this->doctrineHydrator = $dbHydrator;
        $this->indexHydrator = $indexHydrator;
        $this->metaInformationFactory = $metaInformationFactory;
        $this->documentFactory = new DocumentFactory($metaInformationFactory);
        $this->hydrationMode = HydrationModes::HYDRATE_DOCTRINE;
    }

    /**
     * {@inheritdoc}
     */
    public function toDocument(MetaInformationInterface $metaInformation)
    {
        return $this->documentFactory->createDocument($metaInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function toEntity(\ArrayAccess $document, $sourceTargetEntity)
    {
        if (null === $sourceTargetEntity) {
            throw new SolrMappingException('$sourceTargetEntity should not be null');
        }

        $metaInformation = $this->metaInformationFactory->loadInformation($sourceTargetEntity);

        if (false === $metaInformation->isDoctrineEntity() && HydrationModes::HYDRATE_DOCTRINE === $this->hydrationMode) {
            throw new SolrMappingException(sprintf('Please check your config. Given entity is not a Doctrine entity, but Doctrine hydration is enabled. Use setHydrationMode(HydrationModes::HYDRATE_DOCTRINE) to fix this.'));
        }

        if (HydrationModes::HYDRATE_INDEX === $this->hydrationMode) {
            return $this->indexHydrator->hydrate($document, $metaInformation);
        }

        if (HydrationModes::HYDRATE_DOCTRINE === $this->hydrationMode) {
            return $this->doctrineHydrator->hydrate($document, $metaInformation);
        }
    }

    /**
     * @param string $mode
     */
    public function setHydrationMode($mode)
    {
        $this->hydrationMode = $mode;
    }
}

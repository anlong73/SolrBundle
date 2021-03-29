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

use Solarium\QueryType\Update\Query\Document\Document;

interface EntityMapperInterface
{
    /**
     * @param MetaInformationInterface $metaInformation
     *
     * @return Document
     */
    public function toDocument(MetaInformationInterface $metaInformation);

    /**
     * @param \ArrayAccess  $document
     * @param object|string $sourceTargetEntity entity, entity-alias or classname
     *
     * @throws SolrMappingException( if $sourceTargetEntity is null
     *
     * @return object
     */
    public function toEntity(\ArrayAccess $document, $sourceTargetEntity);

    /**
     * @param string $mode
     */
    public function setHydrationMode($mode);
}

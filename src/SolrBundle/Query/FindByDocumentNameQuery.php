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

namespace FS\SolrBundle\Query;

use FS\SolrBundle\Query\Exception\QueryException;

/**
 * Builds a wildcard query to find all documents.
 *
 * Query: id:documentname_*
 */
class FindByDocumentNameQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $documentName;

    /**
     * @param string $documentName
     */
    public function setDocumentName($documentName)
    {
        $this->documentName = $documentName;
    }

    /**
     * @throws QueryException if documentName is null
     *
     * @return string
     */
    public function getQuery()
    {
        $documentName = $this->documentName;

        if (null === $documentName) {
            throw new QueryException('documentName should not be null');
        }

        $documentLimitation = $this->createFilterQuery('id')->setQuery(sprintf('id:%s_*', $documentName));
        $this->addFilterQuery($documentLimitation);

        $this->setQuery('*:*');

        return parent::getQuery();
    }
}

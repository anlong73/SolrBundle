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

class DeleteDocumentQuery extends AbstractQuery
{
    /**
     * @var string
     */
    private $documentKey;

    /**
     * @param string $documentKey
     */
    public function setDocumentKey($documentKey)
    {
        $this->documentKey = $documentKey;
    }

    /**
     * @throws QueryException when id or document_name is null
     *
     * @return string
     */
    public function getQuery()
    {
        $idField = $this->documentKey;

        if (null === $idField) {
            throw new QueryException('id should not be null');
        }

        $this->setQuery(sprintf('id:%s', $idField));

        return parent::getQuery();
    }
}

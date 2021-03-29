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

namespace FS\SolrBundle\Tests;

class DocumentStub implements \Solarium\QueryType\Update\Query\Document\DocumentInterface
{
    public $id = 1;
    public $document_name_s = 'stub_document';

    /**
     * Constructor.
     */
    public function __construct(array $fields = [], array $boosts = [], array $modifiers = [])
    {
    }

    /**
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getField($fieldName)
    {
        $fields = ['id' => $this->id, 'document_name' => $this->document_name_s];

        return $fields[$fieldName];
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return ['id' => $this->id, 'document_name' => $this->document_name_s];
    }
}

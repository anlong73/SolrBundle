<?php

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

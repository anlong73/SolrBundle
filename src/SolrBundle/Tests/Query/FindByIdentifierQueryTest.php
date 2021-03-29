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

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Query\Exception\QueryException;
use FS\SolrBundle\Query\FindByIdentifierQuery;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group query
 */
class FindByIdentifierQueryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery_SearchInAllFields()
    {
        $document = new Document();
        $document->setKey('id', 'validtestentity_1');

        $query = new FindByIdentifierQuery();
        $query->setDocumentKey('validtestentity_1');
        $query->setDocument($document);

        $this->assertEquals('*:*', $query->getQuery());
        $this->assertEquals('id:validtestentity_1', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function expectExceptionWhenIdIsNull()
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('id should not be null');
        $query = new FindByIdentifierQuery();
        $query->setDocument(new Document());
        $query->getQuery();
    }
}

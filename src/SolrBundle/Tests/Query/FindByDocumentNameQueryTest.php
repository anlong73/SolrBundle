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
use FS\SolrBundle\Query\FindByDocumentNameQuery;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group query
 */
class FindByDocumentNameQueryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group query1
     */
    public function testGetQuery_SearchInAllFields()
    {
        $document = new Document();
        $document->addField('id', 'validtestentity_1');

        $query = new FindByDocumentNameQuery();
        $query->setDocumentName('validtestentity');
        $query->setDocument($document);

        $this->assertEquals('*:*', $query->getQuery(), 'filter query');
        $this->assertEquals('id:validtestentity_*', $query->getFilterQuery('id')->getQuery());
    }

    /**
     * @test
     */
    public function shouldNotGetQueryWhenDocumentNameIsMissing(): void
    {
        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('documentName should not be null');
        $query = new FindByDocumentNameQuery();
        $query->setDocument(new Document());
        $query->getQuery();
    }
}

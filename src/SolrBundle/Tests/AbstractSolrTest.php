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

use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Mapper\EntityMapperInterface;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Solr;
use FS\SolrBundle\Tests\Util\MetaTestInformationFactory;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Solarium\QueryType\Update\Query\Document\DocumentInterface;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractSolrTest extends TestCase
{
    /**
     * @var MetaInformationFactory
     */
    protected $metaFactory = null;
    protected $eventDispatcher = null;
    protected $mapper = null;
    protected $solrClientFake = null;

    /**
     * @var Solr
     */
    protected $solr;

    protected function setUp(): void
    {
        $this->metaFactory = new MetaInformationFactory(new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader()));
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->mapper = $this->getMockBuilder(EntityMapperInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['setMappingCommand', 'toDocument', 'toEntity', 'setHydrationMode'])
            ->getMock();

        $this->solrClientFake = $this->createMock(Client::class);

        $this->solr = new Solr($this->solrClientFake, $this->eventDispatcher, $this->metaFactory, $this->mapper);
    }

    protected function assertUpdateQueryExecuted($index = null)
    {
        $updateQuery = $this->createMock(UpdateQuery::class);
        $updateQuery->expects($this->once())
            ->method('addDocument');

        $updateQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
            ->willReturn($updateQuery);

        $this->solrClientFake
            ->expects($this->once())
            ->method('update')
            ->with($updateQuery, $index);

        return $updateQuery;
    }

    protected function assertUpdateQueryWasNotExecuted()
    {
        $updateQuery = $this->createMock(UpdateQuery::class);
        $updateQuery->expects($this->never())
            ->method('addDocument');

        $updateQuery->expects($this->never())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->never())
            ->method('createUpdate');
    }

    protected function assertDeleteQueryWasExecuted()
    {
        $deleteQuery = $this->createMock(UpdateQuery::class);
        $deleteQuery->expects($this->once())
            ->method('addDeleteQuery')
            ->with($this->isType('string'));

        $deleteQuery->expects($this->once())
            ->method('addCommit');

        $this->solrClientFake
            ->expects($this->once())
            ->method('createUpdate')
            ->willReturn($deleteQuery);

        $this->solrClientFake
            ->expects($this->once())
            ->method('update')
            ->with($deleteQuery);
    }

    protected function setupMetaFactoryLoadOneCompleteInformation($metaInformation = null)
    {
        if (null === $metaInformation) {
            $metaInformation = MetaTestInformationFactory::getMetaInformation();
        }

        $this->metaFactory->expects($this->once())
            ->method('loadInformation')
            ->willReturn($metaInformation);
    }

    protected function assertQueryWasExecuted($data, $index)
    {
        $selectQuery = $this->createMock(SelectQuery::class);
        $selectQuery->expects($this->once())
            ->method('setQuery');

        $queryResult = new ResultFake($data);

        $this->solrClientFake
            ->expects($this->once())
            ->method('createSelect')
            ->willReturn($selectQuery);

        $this->solrClientFake
            ->expects($this->once())
            ->method('select')
            ->willReturn($queryResult);
    }

    protected function mapOneDocument()
    {
        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->willReturn($this->createMock(DocumentInterface::class));
    }
}

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

use FS\SolrBundle\Query\FindByDocumentNameQuery;
use FS\SolrBundle\Query\QueryBuilderInterface;
use FS\SolrBundle\Query\SolrQuery;
use FS\SolrBundle\SolrException;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Fixtures\EntityCore0;
use FS\SolrBundle\Tests\Fixtures\EntityCore1;
use FS\SolrBundle\Tests\Fixtures\EntityWithInvalidRepository;
use FS\SolrBundle\Tests\Fixtures\EntityWithRepository;
use FS\SolrBundle\Tests\Fixtures\InvalidTestEntityFiltered;
use FS\SolrBundle\Tests\Fixtures\ValidEntityRepository;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntityFiltered;
use Solarium\Plugin\BufferedAdd\BufferedAdd;
use Solarium\QueryType\Update\Query\Document\Document;

/**
 * @group facade
 */
class SolrTest extends AbstractSolrTest
{
    /**
     * @test
     */
    public function shouldCreateValidEntity(): void
    {
        $query = $this->solr->createQuery(ValidTestEntity::class);
        $this->assertTrue($query instanceof SolrQuery);
        $this->assertEquals(6, count($query->getMappedFields()));
    }

    /**
     * @test
     */
    public function shouldGetUserDefinedRepository(): void
    {
        $actual = $this->solr->getRepository(EntityWithRepository::class);

        $this->assertTrue($actual instanceof ValidEntityRepository);
    }

    /**
     * @test
     */
    public function shouldNotGetInvalidUserDefinedRepository(): void
    {
        $this->expectException(SolrException::class);
        $this->expectExceptionMessage('FS\SolrBundle\Tests\Fixtures\InvalidEntityRepository must extends the FS\SolrBundle\Repository\Repository');
        $this->solr->getRepository(EntityWithInvalidRepository::class);
    }

    /**
     * @test
     */
    public function shouldAddDocument(): void
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->willReturn(new DocumentStub());

        $entity = new ValidTestEntity();
        $entity->setTitle('title');

        $this->solr->addDocument($entity);
    }

    /**
     * @test
     */
    public function shouldUpdateDocument(): void
    {
        $this->assertUpdateQueryExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->willReturn(new DocumentStub());

        $entity = new ValidTestEntity();
        $entity->setTitle('title');

        $this->solr->updateDocument($entity);
    }

    /**
     * @test
     */
    public function shouldNotUpdateDocumentIfDocumentCallbackAvoidIt(): void
    {
        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->assertUpdateQueryWasNotExecuted();

        $filteredEntity = new ValidTestEntityFiltered();
        $filteredEntity->shouldIndex = false;

        $this->solr->updateDocument($filteredEntity);
    }

    /**
     * @test
     */
    public function shouldRemoveDocument(): void
    {
        $this->assertDeleteQueryWasExecuted();

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->willReturn(new DocumentStub());

        $this->solr->removeDocument(new ValidTestEntity());
    }

    /**
     * @test
     */
    public function shouldClearIndex(): void
    {
        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch');

        $this->solrClientFake->expects($this->once())
            ->method('getEndpoints')
            ->willReturn(['core0' => []]);

        $this->assertDeleteQueryWasExecuted();

        $this->solr->clearIndex();
    }

    /**
     * @test
     */
    public function shouldHaveNoResponseKeyInResponseSet(): void
    {
        $document = new Document();
        $document->addField('document_name_s', 'name');

        $query = new FindByDocumentNameQuery();
        $query->setDocumentName('name');
        $query->setDocument($document);
        $query->setIndex('index0');

        $this->assertQueryWasExecuted([], 'index0');

        $entities = $this->solr->query($query);
        $this->assertEquals(0, count($entities));
    }

    /**
     * @test
     */
    public function shouldFindOneDocument(): void
    {
        $arrayObj = new SolrDocumentStub(['title_s' => 'title']);

        $document = new Document();
        $document->addField('document_name_s', 'name');

        $query = new FindByDocumentNameQuery();
        $query->setDocumentName('name');
        $query->setDocument($document);
        $query->setEntity(new ValidTestEntity());
        $query->setIndex('index0');

        $this->assertQueryWasExecuted([$arrayObj], 'index0');

        $entities = $this->solr->query($query);
        $this->assertEquals(1, count($entities));
    }

    /**
     * @test
     */
    public function shouldNotIndexAddedEntity(): void
    {
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();

        $this->solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    /**
     * @test
     */
    public function shouldIndexAddedEntity(): void
    {
        $this->assertUpdateQueryExecuted('index0');

        $this->eventDispatcher->expects($this->any())
            ->method('dispatch');

        $entity = new ValidTestEntityFiltered();
        $entity->shouldIndex = true;

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->willReturn(new DocumentStub());

        $this->solr->addDocument($entity);

        $this->assertTrue($entity->getShouldBeIndexedWasCalled(), 'filter method was not called');
    }

    /**
     * @test
     */
    public function addFilteredEntityWithUnknownCallback(): void
    {
        $this->expectException(SolrException::class);
        $this->assertUpdateQueryWasNotExecuted();

        $this->eventDispatcher->expects($this->never())
            ->method('dispatch');

        $this->solr->addDocument(new InvalidTestEntityFiltered());
    }

    /**
     * @test
     */
    public function indexDocumentsGroupedByCore(): void
    {
        $entity = new ValidTestEntity();
        $entity->setTitle('title field');

        $bufferPlugin = $this->createMock(BufferedAdd::class);

        $bufferPlugin->expects($this->once())
            ->method('setEndpoint')
            ->with(null);

        $bufferPlugin->expects($this->once())
            ->method('commit');

        $this->solrClientFake->expects($this->once())
            ->method('getPlugin')
            ->with('bufferedadd')
            ->willReturn($bufferPlugin);

        $this->mapper->expects($this->once())
            ->method('toDocument')
            ->willReturn(new DocumentStub());

        $this->solr->synchronizeIndex([$entity]);
    }

    /**
     * @test
     */
    public function setCoreToNullIfNoIndexExists(): void
    {
        $entity1 = new EntityCore0();
        $entity1->setText('a text');

        $entity2 = new EntityCore1();
        $entity2->setText('a text');

        $bufferPlugin = $this->createMock(BufferedAdd::class);
        $bufferPlugin->expects(self::exactly(2))
            ->method('setEndpoint')
            ->withConsecutive(
                ['core0'],
                ['core1']
            );

        $bufferPlugin->expects($this->exactly(2))
            ->method('commit');

        $this->solrClientFake->expects($this->once())
            ->method('getPlugin')
            ->with('bufferedadd')
            ->willReturn($bufferPlugin);

        $this->mapper->expects($this->exactly(2))
            ->method('toDocument')
            ->willReturn(new DocumentStub());

        $this->solr->synchronizeIndex([$entity1, $entity2]);
    }

    /**
     * @test
     */
    public function createQueryBuilder(): void
    {
        $queryBuilder = $this->solr->createQueryBuilder(ValidTestEntity::class);

        $this->assertTrue($queryBuilder instanceof QueryBuilderInterface);
    }
}

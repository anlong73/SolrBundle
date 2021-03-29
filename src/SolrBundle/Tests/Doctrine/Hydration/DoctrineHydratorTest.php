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

namespace FS\SolrBundle\Tests\Doctrine\Hydration;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use FS\SolrBundle\Doctrine\Annotation\AnnotationReader;
use FS\SolrBundle\Doctrine\Hydration\DoctrineHydrator;
use FS\SolrBundle\Doctrine\Hydration\DoctrineValueHydrator;
use FS\SolrBundle\Doctrine\Hydration\ValueHydrator;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationFactory;
use FS\SolrBundle\Doctrine\Mapper\MetaInformationInterface;
use FS\SolrBundle\Tests\Doctrine\Mapper\SolrDocumentStub;
use FS\SolrBundle\Tests\Fixtures\ValidOdmTestDocument;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @group hydration
 */
class DoctrineHydratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @test
     */
    public function foundEntityInDbReplacesEntityOldTargetEntity()
    {
        $fetchedFromDoctrine = new ValidTestEntity();

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($fetchedFromDoctrine);

        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $ormManager = $this->setupManager($metainformations, $repository);

        $obj = new SolrDocumentStub(['id' => 'document_1']);

        $doctrine = new DoctrineHydrator(new ValueHydrator());
        $doctrine->setOrmManager($ormManager);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEntityFromDBReplcesTargetEntity($metainformations, $fetchedFromDoctrine, $hydratedDocument);
    }

    /**
     * @test
     */
    public function useOdmManagerIfObjectIsOdmDocument()
    {
        $fetchedFromDoctrine = new ValidOdmTestDocument();

        $odmRepository = $this->createMock(ObjectRepository::class);
        $odmRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($fetchedFromDoctrine);

        $entity = new ValidOdmTestDocument();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $ormManager = $this->createMock(ObjectManager::class);
        $ormManager->expects($this->never())
            ->method('getRepository');
        $odmManager = $this->setupManager($metainformations, $odmRepository);

        $obj = new SolrDocumentStub(['id' => 'document_1']);

        $doctrine = new DoctrineHydrator(new ValueHydrator());
        $doctrine->setOdmManager($odmManager);
        $doctrine->setOrmManager($ormManager);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEntityFromDBReplcesTargetEntity($metainformations, $fetchedFromDoctrine, $hydratedDocument);
    }

    /**
     * @test
     */
    public function hydrationShouldOverwriteComplexTypes()
    {
        $entity1 = new ValidTestEntity();
        $entity1->setTitle('title 1');

        $entity2 = new ValidTestEntity();
        $entity2->setTitle('title 2');

        $relations = [$entity1, $entity2];

        $targetEntity = new ValidTestEntity();
        $targetEntity->setId(1);
        $targetEntity->setPosts($relations);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($targetEntity);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($targetEntity);

        $ormManager = $this->setupManager($metainformations, $repository);

        $obj = new SolrDocumentStub([
            'id' => 'document_1',
            'posts_ss' => ['title 1', 'title 2'],
        ]);

        $doctrineHydrator = new DoctrineHydrator(new DoctrineValueHydrator());
        $doctrineHydrator->setOrmManager($ormManager);

        /** @var ValidTestEntity $hydratedEntity */
        $hydratedEntity = $doctrineHydrator->hydrate($obj, $metainformations);

        $this->assertEquals($relations, $hydratedEntity->getPosts());
    }

    /**
     * @test
     */
    public function entityFromDbNotFoundShouldNotModifyMetainformations()
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(null);

        $entity = new ValidTestEntity();
        $entity->setId(1);

        $metainformations = new MetaInformationFactory($this->reader);
        $metainformations = $metainformations->loadInformation($entity);

        $ormManager = $this->setupManager($metainformations, $repository);

        $obj = new SolrDocumentStub(['id' => 'document_1']);

        $hydrator = new ValueHydrator();

        $doctrine = new DoctrineHydrator($hydrator);
        $doctrine->setOrmManager($ormManager);
        $hydratedDocument = $doctrine->hydrate($obj, $metainformations);

        $this->assertEquals($metainformations->getEntity(), $entity);
        $this->assertEquals($entity, $hydratedDocument);
    }

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader(new \Doctrine\Common\Annotations\AnnotationReader());
    }

    /**
     * @param MetaInformation $metainformations
     * @param object          $fetchedFromDoctrine
     * @param object          $hydratedDocument
     */
    private function assertEntityFromDBReplcesTargetEntity($metainformations, $fetchedFromDoctrine, $hydratedDocument)
    {
        $this->assertEquals($metainformations->getEntity(), $fetchedFromDoctrine);
        $this->assertEquals($fetchedFromDoctrine, $hydratedDocument);
    }

    /**
     * @param MetaInformationInterface $metainformations
     * @param $repository
     *
     * @return MockObject
     */
    private function setupManager($metainformations, $repository)
    {
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->once())
            ->method('getRepository')
            ->with($metainformations->getClassName())
            ->willReturn($repository);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManager')
            ->willReturn($manager);

        return $managerRegistry;
    }
}

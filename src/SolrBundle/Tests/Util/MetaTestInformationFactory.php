<?php

namespace FS\SolrBundle\Tests\Util;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Annotation\Id;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Tests\Fixtures\ValidTestEntity;

class MetaTestInformationFactory
{
    /**
     * @param object $entity
     *
     * @return MetaInformation
     */
    public static function getMetaInformation($entity = null)
    {
        if (null === $entity) {
            $entity = new ValidTestEntity();
        }
        $entity->setId(2);

        $metaInformation = new MetaInformation();

        $title = new Field(['name' => 'title', 'boost' => '1.8', 'value' => 'A title']);
        $text = new Field(['name' => 'text', 'type' => 'text', 'value' => 'A text']);
        $createdAt = new Field(['name' => 'created_at', 'type' => 'date', 'boost' => '1', 'value' => 'A created at']);

        $metaInformation->setFields([$title, $text, $createdAt]);

        $fieldMapping = [
            'id' => 'id',
            'title_s' => 'title',
            'text_t' => 'text',
            'created_at_dt' => 'created_at',
        ];
        $metaInformation->setIdentifier(new Id([]));
        $metaInformation->setBoost(1);
        $metaInformation->setFieldMapping($fieldMapping);
        $metaInformation->setEntity($entity);
        $metaInformation->setDocumentName('validtestentity');
        $metaInformation->setClassName(get_class($entity));
        $metaInformation->setIndex(null);

        return $metaInformation;
    }
}

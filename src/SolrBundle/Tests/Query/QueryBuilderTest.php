<?php

namespace FS\SolrBundle\Tests\Query;

use FS\SolrBundle\Doctrine\Annotation\Field;
use FS\SolrBundle\Doctrine\Mapper\MetaInformation;
use FS\SolrBundle\Query\QueryBuilder;
use FS\SolrBundle\SolrInterface;

class QueryBuilderTest extends \PHPUnit\Framework\TestCase
{
    private $solr;

    protected function setUp(): void
    {
        $this->solr = $this->createMock(SolrInterface::class);
    }

    /**
     * @test
     */
    public function christmasReadme()
    {
        $metaInformation = $this->setupMetainformation();

        $builder = new QueryBuilder($this->solr, $metaInformation);

        $nearNorthPole = $builder->where('position')->nearCircle(38.116181, -86.929463, 100.5);
        self::assertEquals('{!bbox pt=38.116181,-86.929463 sfield=position_s d=100.5}', $nearNorthPole->getQuery()->getCustomQuery());

        $builder = new QueryBuilder($this->solr, $metaInformation);
        $santaClaus = $builder->where('santa-name')->contains(['Noel', 'Claus', 'Natale', 'Baba', 'Nicolas'])
            ->andWhere('santa-beard-exists')->is(true)
            ->andWhere('santa-beard-lenght')->between(5.5, 10.0)
            ->andWhere('santa-beard-color')->startsWith('whi')->endsWith('te')
            ->andWhere($nearNorthPole);

        self::assertEquals('santa-name_ss:(*Noel* *Claus* *Natale* *Baba* *Nicolas*) AND santa-beard-exists_b:true AND santa-beard-lenght_f:[5.5 TO 10] AND santa-beard-color_s:(whi* *te) AND {!bbox pt=38.116181,-86.929463 sfield=position_s d=100.5}', $santaClaus->getQuery()->getCustomQuery());

        $builder = new QueryBuilder($this->solr, $metaInformation);
        $goodPeople = $builder->where('good-actions')->greaterThanEqual(10)
            ->orWhere('bad-actions')->lessThanEqual(5);

        self::assertEquals('good-actions_i:[10 TO *] OR bad-actions_i:[* TO 5]', $goodPeople->getQuery()->getCustomQuery());

        $builder = new QueryBuilder($this->solr, $metaInformation);
        $gifts = $builder->where('gift-name')->sloppy('LED TV GoPro Oculus Tablet Laptop', 2)
            ->andWhere('gift-type')->fuzzy('information', 0.4)->startsWith('tech')
            ->andWhere('__query__')->expression('{!dismax qf=myfield}how now brown cow');

        self::assertEquals('gift-name_s:"LED TV GoPro Oculus Tablet Laptop"~2 AND gift-type_s:(information~0.4 tech*) AND __query___s:{!dismax qf=myfield}how now brown cow', $gifts->getQuery()->getCustomQuery());

        $builder1 = new QueryBuilder($this->solr, $metaInformation);

        $builder2 = new QueryBuilder($this->solr, $metaInformation);

        $christmas = new \DateTime('2016-12-25');
        $contributors = ['Christoph', 'Philipp', 'Francisco', 'Fabio'];
        $giftReceivers = $builder1->where('gift-received')->is(null)
            ->andWhere('chimney')->isNotNull()
            ->andWhere('date')->is($christmas)->greaterThanEqual(new \Datetime('1970-01-01'))
            ->andWhere($santaClaus)
            ->andWhere($gifts)
            ->andWhere(
                $builder2->where('name')->in($contributors)->boost(2.0)
                    ->orWhere($goodPeople)
            );

        self::assertEquals('-gift-received_s:[* TO *] AND chimney_s:[* TO *] AND date_dt:(2016\\-12\\-25T00\\:00\\:00Z [1970\\-01\\-01T00\\:00\\:00Z TO *]) AND (santa-name_ss:(*Noel* *Claus* *Natale* *Baba* *Nicolas*) AND santa-beard-exists_b:true AND santa-beard-lenght_f:[5.5 TO 10] AND santa-beard-color_s:(whi* *te) AND {!bbox pt=38.116181,-86.929463 sfield=position_s d=100.5}) AND (gift-name_s:"LED TV GoPro Oculus Tablet Laptop"~2 AND gift-type_s:(information~0.4 tech*) AND __query___s:{!dismax qf=myfield}how now brown cow) AND (name_s:(Christoph Philipp Francisco Fabio)^2.0 OR (good-actions_i:[10 TO *] OR bad-actions_i:[* TO 5]))', $giftReceivers->getQuery()->getCustomQuery());
    }

    /**
     * @test
     */
    public function doNotAddIdFieldTwice()
    {
        $builder = new QueryBuilder($this->solr, $this->setupMetainformation());

        $query = $builder
            ->where('santa-beard-exists')->is(true)
            ->andWhere('santa-beard-lenght')->between(5.5, 10.0)
            ->andWhere('santa-beard-color')->startsWith('whi')->endsWith('te')
            ->andWhere('id')->is('post_1')
            ->getQuery()->getQuery();

        $this->assertEquals('santa-beard-exists_b:true AND santa-beard-lenght_f:[5.5 TO 10] AND santa-beard-color_s:(whi* *te) AND id:post_1', $query);
    }

    /**
     * @test
     * @expectedException \FS\SolrBundle\Doctrine\Mapper\SolrMappingException
     * @expectedExceptionMessage $fieldName must not be empty
     */
    public function setEmpty()
    {
        $builder = new QueryBuilder($this->solr, $this->setupMetainformation());
        $query = $builder
            ->where('')
            ->getQuery()->getQuery();
    }

    /**
     * @return MetaInformation
     */
    private function setupMetainformation()
    {
        $metaInformation = new MetaInformation();

        $field1 = new Field([]);
        $field1->name = 'position';
        $field1->type = 'string';

        $field2 = new Field([]);
        $field2->name = 'santa-beard-exists';
        $field2->type = 'boolean';

        $field3 = new Field([]);
        $field3->name = 'santa-beard-lenght';
        $field3->type = 'float';

        $field4 = new Field([]);
        $field4->name = 'santa-beard-color';
        $field4->type = 'string';

        $field5 = new Field([]);
        $field5->name = 'good-actions';
        $field5->type = 'integer';

        $field6 = new Field([]);
        $field6->name = 'gift-name';
        $field6->type = 'string';

        $field7 = new Field([]);
        $field7->name = 'gift-type';
        $field7->type = 'string';

        $field8 = new Field([]);
        $field8->name = 'gift-received';
        $field8->type = 'string';

        $field9 = new Field([]);
        $field9->name = 'chimney';
        $field9->type = 'string';

        $field10 = new Field([]);
        $field10->name = 'date';
        $field10->type = 'datetime';

        $field11 = new Field([]);
        $field11->name = 'santa-name';
        $field11->type = 'strings';

        $field12 = new Field([]);
        $field12->name = 'bad-actions';
        $field12->type = 'integer';

        $field13 = new Field([]);
        $field13->name = '__query__';
        $field13->type = 'string';

        $field14 = new Field([]);
        $field14->name = 'name';
        $field14->type = 'string';

        $field15 = new Field([]);
        $field15->name = 'id';

        $metaInformation->setFields([$field1, $field2, $field3, $field4, $field5, $field6, $field7, $field8, $field9, $field10, $field11, $field12, $field13, $field14, $field15]);

        return $metaInformation;
    }
}

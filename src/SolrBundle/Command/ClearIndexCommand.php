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

namespace FS\SolrBundle\Command;

use FS\SolrBundle\SolrException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command clears the whole index.
 */
class ClearIndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('solr:index:clear')
            ->setDescription('Clear the whole index');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $solr = $this->getContainer()->get('solr.client');

        try {
            $solr->clearIndex();
        } catch (SolrException $e) {
            $output->writeln(sprintf('A error occurs: %s', $e->getMessage()));
        }

        $output->writeln('<info>Index successful cleared.</info>');
    }
}

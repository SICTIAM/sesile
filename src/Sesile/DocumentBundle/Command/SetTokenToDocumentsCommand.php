<?php

namespace Sesile\DocumentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetTokenToDocumentsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sesile_document:set_token_to_documents_command')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $documents = $em->getRepository('SesileDocumentBundle:Document')->findAll();

        foreach ($documents as $document) {
            $document->setToken(hash('md5', random_bytes(10)));
        }

        $em->flush();
    }
}

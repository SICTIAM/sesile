<?php

namespace Sesile\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class updateCollectiviteUserNotificationCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sesile_main:collectivite:update_user_notification')
            ->setDescription('Update Collectivite User copy notifications');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $collectivites = $em->getRepository('SesileMainBundle:Collectivite')->findAll();

        foreach ($collectivites as $collectivite) {
            if ($collectivite->getTextcopymailnew() === null) {
                $collectivite->setTextcopymailnew("<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d'être déposé par {{ deposant }}, pour validation à {{ validant }}, à la date du <strong>{{ date_limite | date('d/m/Y') }}.</strong></p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>");
            }

            if ($collectivite->getTextcopymailwalid() === null) {
                $collectivite->setTextcopymailwalid("<p>Bonjour {{ en_copie }},</p><p>Un nouveau classeur pour lequel vous êtes en copie {{ titre_classeur }} vient d'être validé par {{ validant }}.</p><p>Vous pouvez visionner le classeur {{lien|raw}}</p><p>**logo_coll** {{ qualite }}<br>{{ validant }}</p>");
            }
        }

        $em->flush();

        $output->writeln('Les collectivités ont été modifiées');

    }
}

<?php
namespace Sesile\UserBundle\Command;

use Sesile\UserBundle\Entity\EtapeGroupe;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class old2newSOCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:so:migrate')
            ->setDescription('Migration des services organisationnels')
//            ->addArgument('nomPort', InputArgument::REQUIRED, 'A quel port voulez-vous affecter les utilisateurs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $servOrgs = $em->getRepository('SesileUserBundle:Groupe')->findAll();
        foreach($servOrgs as $service)
        {
            if($service->getJson() != null)
            {

                $json = $service->getJson();
                //var_dump($json);
                $tabservice = array_reverse(self::recursive(json_decode($json)));
                $ordre = 0;
                $array2implode = array();
                var_dump(json_decode($json),$tabservice);
                foreach($tabservice as $newserv)
                {
                    $etapeGroupe = new EtapeGroupe();
                    $userIds = explode('-',$newserv);
                    foreach($userIds as $userId)
                    {
                        $user = $em->getRepository('SesileUserBundle:User')->findOneById($userId);
                        $etapeGroupe->addUser($user);
                    }
                    $etapeGroupe->setGroupe($service);
                    $etapeGroupe->setOrdre($ordre);
                    $ordre++;
                    $em->persist($etapeGroupe);
                    $em->flush();
                    $array2implode[] = $etapeGroupe->getId();
                }

                $service->setOrdreEtape(implode(',',$array2implode));
                $em->persist($service);
                $em->flush();
            }

        }

        $output->writeln('Services organisationnels migrés');
    }

    public function recursive($object,$curLevel=0,$tab=array())
    {

        if(!array_key_exists($curLevel,$tab))
        {
            $tab[$curLevel] = $object->id;
        }
        else{
            $tab[$curLevel] .= '-'.$object->id;
        }

        $level = $curLevel+1;

        if(isset($object->children))
        {
            foreach($object->children as $child)
            {
                $tab =  self::recursive($child,$level,$tab);
            }
        }

        return $tab;
    }
}


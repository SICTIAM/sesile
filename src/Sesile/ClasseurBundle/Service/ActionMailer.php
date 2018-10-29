<?php

namespace Sesile\ClasseurBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Sesile\ClasseurBundle\Entity\Action;
use Sesile\ClasseurBundle\Entity\Classeur;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActionMailer
{

    private $entityManager;
    private $mailer;
    private $fromEmail;
    private $user;
    private $paths;
    private $router;
    private $twig;
    private $domain;

    public function __construct(EntityManagerInterface $entityManager, \Swift_Mailer $mailer, \Twig_Environment $twig, TokenStorageInterface $tokenStorage, UrlGeneratorInterface $router, $fromEmail, $paths, $domain)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        $this->fromEmail = $fromEmail;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->paths = $paths;
        $this->router = $router;
        $this->twig = $twig;
        $this->domain = $domain;
    }

    public function sendNotification(Classeur $classeur, Action $action)
    {

        $subject = 'Nouveau commentaire sur le classeur ' . $classeur->getNom();

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->fromEmail)
            ->setBody(
                $this->twig
                    ->render('SesileClasseurBundle:Notifications:CommentClasseur.html.twig',
                        array(
                            "classeur" => $classeur,
                            "comment" => $action->getAction(),
                            "user" => $action->getUserAction()
                        )),
                    'text/html');

        $users = $this->entityManager->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);

        foreach ($users as $user) {
            $message->setTo($user->getEmail());
            $this->mailer->send($message);
        }

    }

    public function sendNotificationClasseur(Classeur $classeur) {
        if ($classeur->getStatus() === 2) {
            $this->sendValidationMail($classeur);
        } else if ($classeur->getStatus() === 1) {
            $this->sendCreationMail($classeur);
        } else if ($classeur->getStatus() === 0) {
            $this->sendRefusMail($classeur);
        } else {
            return false;
        }
    }

    private function sendCreationMail(Classeur $classeur) {
        $d_user = $classeur->getUser();
        $collectivite = $classeur->getCollectivite();
        $validants = $this->entityManager->getRepository('SesileClasseurBundle:Classeur')->getValidant($classeur);
        $subject = "SESILE - Nouveau classeur déposé";

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $template = $env->createTemplate($collectivite->getTextmailnew());
        $template_html = [
            'deposant' => $d_user->getPrenom() . " " . $d_user->getNom(),
            'role' => $d_user->getRole(),
            'qualite' => $d_user->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            'lien' => '<a href="' . $this->buildAbsoluteUrl($collectivite) .'classeur/'. $classeur->getId() .'">valider le classeur</a>'
        ];

        foreach($validants as $validant) {
            if ($validant != null) {
                $this->sendMail(
                    $subject,
                    $validant->getEmail(),
                    $template->render(
                        array_merge($template_html, ['validant' => $validant->getPrenom() . " " . $validant->getNom()])
                    ),
                    $collectivite
                );
            }
        }

        // notification des users en copy
        $usersCopy = $classeur->getCopy();
        if ($usersCopy && $collectivite->getTextcopymailnew()) {
            $template_copy = $env->createTemplate($collectivite->getTextcopymailnew());
            foreach ($usersCopy as $userCopy) {
                if($userCopy != null && !in_array($userCopy, $validants)) {

                    $usersValidant = "";
                    foreach($validants as $validant) {
                        $usersValidant .= $validant->getPrenom() . " " . $validant->getNom() . " ";
                    }

                    $this->sendMail(
                        $subject,
                        $userCopy->getEmail(),
                        $template_copy->render(
                            array_merge($template_html, ['validant' => $usersValidant, "en_copie" => $userCopy->getPrenom() . " " . $userCopy->getNom()])
                        ),
                        $collectivite
                    );
                }
            }
        }
    }

    private function sendValidationMail(Classeur $classeur)
    {
        $currentUser = $this->user;
        $validant = $this->entityManager->getRepository('SesileUserBundle:EtapeClasseur')->getLastValidant($classeur);
        $subject = "SESILE - Classeur validé";

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $collectivite = $classeur->getCollectivite();
        $template = $env->createTemplate($collectivite->getTextMailwalid());
        $template_html = [
            'validant' => $validant->getPrenom() . " " . $validant->getNom(),
            'role' => $validant->getRole(),
            'qualite' => $validant->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type' => strtolower($classeur->getType()->getNom()),
            'lien' => '<a href="' . $this->buildAbsoluteUrl($collectivite) .'classeur/'. $classeur->getId() .'">voir le classeur</a>'
        ];

        // notification des users en copy
        $usersCopy = $classeur->getCopy();
        if ($usersCopy && $collectivite->getTextcopymailwalid()) {
            $template_copy = $env->createTemplate($collectivite->getTextcopymailwalid());
            foreach ($usersCopy as $userCopy) {
                if ($userCopy != null && $userCopy != $classeur->getUser()) {
                    $this->sendMail(
                        $subject,
                        $userCopy->getEmail(),
                        $template_copy->render(
                            array_merge($template_html, ['deposant' => $userCopy->getPrenom() . " " . $userCopy->getNom()])
                        ),
                        $collectivite
                    );
                }
            }
        }

        $users = $this->entityManager->getRepository('SesileUserBundle:EtapeClasseur')->findAllUsers($classeur);
        foreach ($users as $user) {
            $this->sendMail(
                $subject,
                $user->getEmail(),
                $template->render(
                    array_merge($template_html, ["en_copie" => $user->getPrenom() . " " . $user->getNom()])
                ),
                $collectivite
            );
        }
    }

    private function sendRefusMail(Classeur $classeur) {
        $currentUser = $this->user;
        $deposant = $classeur->getUser();
        $subject = "SESILE - Classeur refusé";

        $env = new \Twig_Environment(new \Twig_Loader_Array(array()));
        $collectivite = $classeur->getCollectivite();
        $template = $env->createTemplate($collectivite->getTextmailrefuse());
        $template_html = [
            'validant'  => $currentUser->getPrenom() . " " . $currentUser->getNom(),
            'role'      => $currentUser->getRole(),
            'qualite'   => $currentUser->getQualite(),
            'titre_classeur' => $classeur->getNom(),
            'date_limite' => $classeur->getValidation(),
            'type'      => strtolower($classeur->getType()->getNom()),
            'lien'      => '<a href="' . $this->buildAbsoluteUrl($collectivite) .'classeur/'. $classeur->getId() .'">voir le classeur</a>',
            'motif'     => $classeur->getMotifRefus()
        ];

        if ($deposant != null) {
            $this->sendMail(
                $subject,
                $deposant->getEmail(),
                $template->render(
                    array_merge($template_html, ['deposant' => $deposant->getPrenom()." ".$deposant->getNom()])
                ),
                $collectivite
            );
        }

        $usersCopy = $classeur->getCopy();
        if ($usersCopy) {
            foreach ($usersCopy as $userCopy) {
                if ($userCopy != null && $userCopy != $deposant) {
                    $this->sendMail(
                        $subject,
                        $userCopy->getEmail(),
                        $template->render(
                            array_merge($template_html, ['deposant' => $userCopy->getPrenom() . " " . $userCopy->getNom()])
                        ),
                        $collectivite
                    );
                }
            }
        }
    }

    private function sendMail($sujet, $to, $body, $collectivite) {
        $html = null;
        $message = \Swift_Message::newInstance();
        $fileSystem = new Filesystem();
        $logoExists = $fileSystem->exists($this->paths['logo_coll'] . $collectivite->getImage());
        // Pour l integration de l image du logo dans le mail
        if($logoExists) $html = explode("**logo_coll**", $body);
        if($collectivite->getImage() !== null && $this->paths['logo_coll'] !== null && count($html) > 1 && $logoExists) {
            $htmlBody = $html[0] . '<img src="' . $message->embed(\Swift_Image::fromPath($this->paths['logo_coll'] . $collectivite->getImage())) . '" width="75" alt="Sesile">' . $html[1];
        } else {
            $htmlBody = $body;
        }

        // On rajoute les balises manquantes
        $html_brkts_start = "<html><head></head><body>";
        $html_brkts_end = "</body></html>";
        $htmlBodyFinish = $html_brkts_start . $htmlBody . $html_brkts_end;

        $message->setSubject($sujet)
            ->setFrom($this->fromEmail)
            ->setTo($to)
            ->setBody($htmlBodyFinish)
            ->setContentType('text/html');

        $this->mailer->send($message);
    }

    private function buildAbsoluteUrl($collectivite) {
        $absoluteUrl = $this->router->generate('sesile_main_default_app', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $parsedUrl = parse_url($absoluteUrl);

        if((isset($parsedUrl['host']) && isset($this->domain)) && $parsedUrl['host'] == $this->domain) {
            $absoluteUrl = parse_url($absoluteUrl)['scheme'] . "://" . $collectivite->getDomain() . "." . parse_url($absoluteUrl)['host'];
        }

        return $absoluteUrl;
    }

}
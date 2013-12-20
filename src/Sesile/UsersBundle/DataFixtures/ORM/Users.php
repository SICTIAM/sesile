<?php
/**
 * Created by PhpStorm.
 * User: r.lasry
 * Date: 15/11/13
 * Time: 09:47
 */

namespace Sesile\UsersBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;

class Users implements FixtureInterface {
    public function load(ObjectManager $manager)
    {
        // Les noms d'utilisateurs à créer
        $noms = array('regis', 'sandrine', 'manon');
        foreach ($noms as $i => $nom) {
            // On crée l'utilisateur
            $users[$i] = new User;
            // Le nom d'utilisateur et le mot de passe sont identiques
            $users[$i]->setUsername($nom);
            $users[$i]->setPassword($nom);
            // Le sel et les rôles sont vides pour l'instant
            $users[$i]->setSalt('');
            $users[$i]->setRoles(array());
            // On le persiste
            $manager->persist($users[$i]);
        }
        // On déclenche l'enregistrement
        $manager->flush();
    }
} 
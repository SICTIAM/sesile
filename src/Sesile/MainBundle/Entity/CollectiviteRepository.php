<?php

namespace Sesile\MainBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CollectiviteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CollectiviteRepository extends EntityRepository {

    public function uploadImage($avatar, $collectivite, $dirPath) {
        if ($avatar) {
            if ($collectivite->getImage()) {
                $collectivite->removeUpload($dirPath);
            }
            $imageName = sha1(uniqid(mt_rand(), true)) . '.' . $avatar->guessExtension();
            $collectivite->setImage($imageName);
            $avatar->move(
                $dirPath,
                $imageName
            );
        }
        return $collectivite;
    }
}

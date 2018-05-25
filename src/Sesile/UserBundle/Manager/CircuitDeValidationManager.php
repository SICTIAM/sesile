<?php


namespace Sesile\UserBundle\Manager;


use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Sesile\MainBundle\Domain\Message;
use Sesile\MainBundle\Entity\Collectivite;
use Sesile\UserBundle\Entity\Groupe;

/**
 * Class CircuitDeValidationManager
 * @package Sesile\UserBundle\Manager
 */
class CircuitDeValidationManager
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CollectiviteManager constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @param string $userEmail
     * @param Collectivite $collectivite
     *
     * @return Message
     */
    public function getCircuitDataByUserAndCollectivite($userEmail, Collectivite $collectivite)
    {
        try {
            $dataCollection = $this->em->getRepository(Groupe::class)->getCircuitDataByUserAndCollectivite(
                $userEmail,
                $collectivite->getId()
            );
            $result = [];
            foreach ($dataCollection as $data) {
                if (isset($result[$data['circuitId']])) {
                    $result[$data['circuitId']]['type_classeur'][] = $data['typeId'];
                    continue;
                }
                $result[$data['circuitId']] = ['id' => $data['circuitId'], 'nom' => $data['circuitName'], 'type_classeur' =>[$data['typeId']]];
            }

            return new Message(true, $result);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[CircuitDeValidationManager]/getCircuitDataByUserAndCollectivite error: %s', $e->getMessage())
            );

            return new Message(false, null, [$e->getMessage()]);
        }
    }


}
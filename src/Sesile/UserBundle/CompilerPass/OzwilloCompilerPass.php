<?php

namespace Sesile\UserBundle\CompilerPass;

use Sesile\MainBundle\Entity\Collectivite;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

class OzwilloCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $request = Request::createFromGlobals();
        $host = $request->server->get('HTTP_HOST');
        $subdomain = str_replace('.'. $container->getParameter('domain'), '' , $host);
//        $subdomain = str_replace('.'. $container->getParameter('domain'), '' , $_SERVER['HTTP_HOST']);
        $collectivite = $container->get('doctrine.orm.entity_manager')->getRepository(Collectivite::class)->findOneByDomain($subdomain);
        if ($collectivite) {
            $clientId = $collectivite->getOzwillo()->getClientId();
            $client_secret = $collectivite->getOzwillo()->getClientSecret();
            $definition = $container->getDefinition('hwi_oauth.resource_owner.ozwillo');
            $ozwilloOption = $definition->getArgument(2);
            if (isset($ozwilloOption['client_id'])) {
                $ozwilloOption['client_id'] = $clientId;
                $ozwilloOption['client_secret'] = $client_secret;

            }
            $definition->replaceArgument(2, $ozwilloOption);
        }
    }
}
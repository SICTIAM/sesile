<?php

namespace Sesile\UserBundle;

use Sesile\UserBundle\CompilerPass\OzwilloCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SesileUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
//    public function build(ContainerBuilder $container)
//    {
//        parent::build($container);
//
//        $container->addCompilerPass(new OzwilloCompilerPass(), PassConfig::TYPE_AFTER_REMOVING, 0);
//    }

}

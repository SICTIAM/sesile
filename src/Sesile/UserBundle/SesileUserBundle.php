<?php

namespace Sesile\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SesileUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }

}

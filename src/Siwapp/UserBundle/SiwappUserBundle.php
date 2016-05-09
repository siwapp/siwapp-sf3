<?php

namespace Siwapp\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SiwappUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}

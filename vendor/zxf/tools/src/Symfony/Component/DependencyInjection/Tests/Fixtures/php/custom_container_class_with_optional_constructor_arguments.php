<?php

namespace zxf\Symfony\Component\DependencyInjection\Tests\Fixtures\Container;

use zxf\Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use zxf\Symfony\Component\DependencyInjection\ContainerInterface;
use zxf\Symfony\Component\DependencyInjection\Container;
use zxf\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use zxf\Symfony\Component\DependencyInjection\Exception\LogicException;
use zxf\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use zxf\Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends \Symfony\Component\DependencyInjection\Tests\Fixtures\Container\ConstructorWithOptionalArgumentsContainer
{
    private $parameters;
    private $targetDirs = array();

    public function __construct()
    {
        parent::__construct();
        $this->parameterBag = null;

        $this->services = array();

        $this->aliases = array();
    }

    public function getRemovedIds()
    {
        return array(
            'Psr\\Container\\ContainerInterface' => true,
            'Symfony\\Component\\DependencyInjection\\ContainerInterface' => true,
        );
    }

    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled()
    {
        return true;
    }

    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }
}

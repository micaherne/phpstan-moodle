<?php

namespace PhpstanMoodle\Type;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class GetAuthPluginTypeSpecifyingExtension implements DynamicFunctionReturnTypeExtension
{

    #[\Override] public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'get_auth_plugin';
    }

    #[\Override] public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?Type {
        if ($functionCall->getArgs() === []) {
            return null;
        }
        $arg1 = $functionCall->getArgs()[0]->value;
        if (!$arg1 instanceof String_) {
            return null;
        }

        // The function throws an exception if the plugin type is not found so it is never null.
        return new ObjectType('\auth_plugin_' . $arg1->value);
    }
}
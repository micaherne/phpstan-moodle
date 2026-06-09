<?php

namespace PhpstanMoodle\Type;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class GetAuthPluginDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'get_auth_plugin';
    }

    public function getTypeFromFunctionCall(
        FunctionReflection $functionReflection,
        FuncCall $functionCall,
        Scope $scope
    ): ?Type {
        $args = $functionCall->getArgs();
        if ($args === []) {
            return null;
        }

        $types = [];
        foreach ($scope->getType($args[0]->value)->getConstantStrings() as $constantString) {
            $types[] = new ObjectType('\auth_plugin_' . $constantString->getValue());
        }
        if ($types === []) {
            return null;
        }

        // The function throws an exception if the plugin type is not found so it is never null.
        return TypeCombinator::union(...$types);
    }
}

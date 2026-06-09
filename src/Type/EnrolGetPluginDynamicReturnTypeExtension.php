<?php

namespace PhpstanMoodle\Type;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class EnrolGetPluginDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'enrol_get_plugin';
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
            $types[] = new ObjectType('\enrol_' . $constantString->getValue() . '_plugin');
        }
        if ($types === []) {
            return null;
        }

        // The function returns null if the plugin is not found.
        return TypeCombinator::addNull(TypeCombinator::union(...$types));
    }
}

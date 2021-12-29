<?php

declare(strict_types=1);

namespace staabm\PHPStanDba\Extensions;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use staabm\PHPStanDba\QueryReflection\QueryReflection;

final class DeployerRunMysqlQueryDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return 'Deployer\runMysqlQuery' === $functionReflection->getName();
    }

    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
    {
        $args = $functionCall->getArgs();

        if (\count($args) < 2) {
            return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
        }

        $queryReflection = new QueryReflection();
        $resultType = $queryReflection->getResultType($args[0]->value, $scope, QueryReflection::FETCH_TYPE_NUMERIC);
        if ($resultType) {
            if ($resultType instanceof ConstantArrayType) {
                $builder = ConstantArrayTypeBuilder::createEmpty();
                foreach ($resultType->getKeyTypes() as $keyType) {
                    $builder->setOffsetValueType($keyType, new StringType());
                }

                return TypeCombinator::addNull(new ArrayType(new IntegerType(), $builder->getArray()));
            }
        }

        return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
    }
}

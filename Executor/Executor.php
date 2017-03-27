<?php

/*
 * This file is part of the OverblogGraphQLBundle package.
 *
 * (c) Overblog <http://github.com/overblog/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Overblog\GraphQLBundle\Executor;

use GraphQL\Executor\ExecutionResult;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Schema;
use Overblog\GraphQLBundle\Executor\Promise\PromiseAdapterInterface;

class Executor implements ExecutorInterface
{
    /**
     * @param Schema      $schema
     * @param string      $requestString
     * @param null|array  $rootValue
     * @param null|array  $contextValue
     * @param null|array  $variableValues
     * @param null|string $operationName
     *
     * @return ExecutionResult|Promise
     */
    public function execute(Schema $schema, $requestString, $rootValue = null, $contextValue = null, $variableValues = null, $operationName = null)
    {
        //var_dump($requestString);

        //var_dump($schema->getType('Movie')->config['fields']); // Closure returns an array

        foreach ($schema->getTypeMap() as $typeName => $type) {
            if ($type instanceof \Overblog\GraphQLBundle\__DEFINITIONS__\MovieType) {
                if (is_callable($type->config['fields'])) {
                    $fields = call_user_func($type->config['fields']);
                    $newConfig = $type->config;
                    $hasChanged = false;
                    foreach ($fields as $fieldName => $field) {
                        $expose = $field['expose'] ?? true;
                        if (is_callable($expose)) {
                            $expose = call_user_func($expose);
                        }
                        if (!$expose) {
                            $hasChanged = true;
                            unset($newConfig['fields'][$fieldName]);
                            $className = get_class($type);
                        }
                    }
                    if ($hasChanged) {
                        $newType = new $className($newConfig);
                    }
                }
            }
        }

        //var_dump(array_keys(get_object_vars($schema->getType('Movie'))));
        $result = call_user_func_array('GraphQL\GraphQL::executeAndReturnResult', func_get_args());
        return $result;
        //var_dump($result); // \GraphQL\Executor\Promise\Promise
    }

    /**
     * @param PromiseAdapterInterface|null $promiseAdapter
     */
    public function setPromiseAdapter(PromiseAdapterInterface $promiseAdapter = null)
    {
        call_user_func_array('GraphQL\GraphQL::setPromiseAdapter', func_get_args());
    }
}

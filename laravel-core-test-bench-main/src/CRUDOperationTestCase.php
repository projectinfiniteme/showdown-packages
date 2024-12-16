<?php

namespace AttractCores\LaravelCoreTestBench;

use Illuminate\Testing\TestResponse;

/**
 * Trait CRUDOperationTestCase
 *
 * @version 1.0.0
 * @date    2019-03-12
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait CRUDOperationTestCase
{

    /**
     * Test read cases.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testOperationCases()
    {
        $this->alert(sprintf("CRUD operation cases for %s", $this->getCRUDClassUnderTest()));
        $tableValues = [];
        $isFailed = false;

        foreach ( $this->getSetUpMethodsPull() as $index => $parameters ) {
            // Setup test if we start new one.
            if ( ! $this->setUpHasRun ) {
                self::setUp();
            }

            if ( $parameters instanceof \Closure ) {
                $parameters = $parameters();
            } elseif ( $parameters instanceof \ReflectionMethod ) {
                $fnName = $parameters->getName();
                $parameters = $this->$fnName();
            }

            $this->tryCatchIfVerbose(function () use (&$tableValues, $index, $parameters) {

                if (
                    empty($parameters[ 'withoutAuth' ]) && ! isset($this->defaultHeaders[ 'authorization' ]) &&
                    ! isset($this->defaultHeaders[ 'Authorization' ])
                ) {
                    $this->withAuthorizationToken();
                }

                /** @var TestResponse $response */
                $response = $this->json(
                    $parameters[ 'method' ] ?? 'POST',
                    route($parameters[ 'route' ], $parameters[ 'params' ] ?? []),
                    $parameters[ 'request' ] ?? [],
                    $parameters[ 'headers' ] ?? [],
                );

                $this->freshRequest();
                $status = $parameters[ 'status' ] ?? 200;

                if ( $response->getStatusCode() != $status && $this->ddOnErrors() ) {
                    dd($response->decodeResponseJson()->json());
                }

                $response->assertStatus($status);

                if ( method_exists($this, $method = sprintf('do%sTestAssertions', $index)) ) {
                    $this->$method($response, $parameters);
                } else {
                    $this->doTestAssertions($response, $index, $parameters);
                }

                $tableValues[] = [
                    $this->getRequestParametersForResultTable($parameters[ 'name' ] ?? $index, 20),
                    $parameters[ 'method' ] ?? 'POST',
                    $this->getRequestParametersForResultTable($parameters[ 'params' ] ?? [], 20),
                    $this->getRequestParametersForResultTable($parameters[ 'request' ] ?? [], 50),
                    $this->isSucceededAsString(),
                ];

            }, function ($exception) use (&$tableValues, $index, &$isFailed, $parameters) {
                $tableValues[] = [
                    $this->getRequestParametersForResultTable($parameters[ 'name' ] ?? $index, 20),
                    $parameters[ 'method' ] ?? 'POST',
                    $this->getRequestParametersForResultTable($parameters[ 'params' ] ?? [], 20),
                    $this->getRequestParametersForResultTable($parameters[ 'request' ] ?? [], 50),
                    chunk_split(sprintf("%s\n%s", $exception->getMessage(), $exception->getTraceAsString()), 100, "\n"),
                ];
                $isFailed = true;
            });

            // Tear down test iteration.
            $this->tearDown();
        }

        $this->table([ 'Index', 'METHOD', 'ROUTE', 'REQUEST', 'STATUS' ], $tableValues, 'box-double');

        if ( $isFailed && $this->isVerboseOutput() ) {
            $this->throwCrudException(sprintf('%s cases ends with errors. Please read table information.',
                $this->getCRUDClassUnderTest()));
        }
    }

    /**
     * Return chunked string for table.
     *
     * @param     $parameters
     *
     * @param int $length
     *
     * @return string
     */
    protected function getRequestParametersForResultTable($parameters, $length = 100)
    {
        return chunk_split(json_encode($parameters), $length, "\n");
    }

    /**
     * Make assertions for operation.
     *
     * @param TestResponse $response
     * @param              $routeName
     * @param array        $parameters
     */
    protected function doTestAssertions(TestResponse $response, $routeName, array $parameters)
    {
        //
    }

    /**
     * Return tests for iterator.
     *
     * @return array
     */
    protected function getTestRoutes()
    {
        return [];
    }

    /**
     * Return reflection methods if they exists, or return default test routes.
     *
     * @return array|\Illuminate\Support\Collection
     * @throws \ReflectionException
     */
    protected function getSetUpMethodsPull()
    {
        $reflectionClass = new \ReflectionClass(get_class($this));

        $magicMethods = collect($reflectionClass->getMethods())->filter(function (\ReflectionMethod $method) {
            return preg_match('/^get[0-9]{1,}TestData$/', $method->getName());
        })->sort(function (\ReflectionMethod $method) {
            return $method->getName();
        })->values();

        return $magicMethods->count() ? $magicMethods : $this->getTestRoutes();
    }

    /**
     * DD on errors.
     *
     * @return false
     */
    protected function ddOnErrors()
    {
        return false;
    }

}

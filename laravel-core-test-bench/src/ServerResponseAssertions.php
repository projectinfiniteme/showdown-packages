<?php

namespace AttractCores\LaravelCoreTestBench;

use Illuminate\Testing\TestResponse;

/**
 * Trait ServerResponseAssertions
 *
 * @version 1.0.0
 * @date    2019-03-12
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait ServerResponseAssertions
{

    /**
     * Check that response succeeded and has base structure.
     *
     * @param TestResponse $response
     *
     * @return mixed
     */
    protected function assertSuccessResponse(TestResponse $response)
    {
        $responseArray = $response->decodeResponseJson()->json();
        $this->assertBaseResponse($responseArray);
        $this->assertEmpty($responseArray['errors'], collect($responseArray['errors'])->pluck('errors')->flatten()->implode(";\n"));

        return $responseArray;
    }

    /**
     * Check base response structure.
     *
     * @param array $responseArray
     */
    protected function assertBaseResponse(array $responseArray)
    {
        $this->assertArrayHasKey('code', $responseArray);
        $this->assertArrayHasKey('success', $responseArray);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertArrayHasKey('data', $responseArray);
        $this->assertArrayHasKey('meta', $responseArray);
        $this->assertArrayHasKey('links', $responseArray);
        $this->assertArrayHasKey('errors', $responseArray);
    }

    /**
     * Check that response has errors and has base structure.
     *
     * @param TestResponse $response
     */
    protected function assertErrorResponse(TestResponse $response)
    {
        $responseArray = $response->decodeResponseJson()->json();
        $this->assertBaseResponse($responseArray);
        $this->assertNotEmpty($responseArray['errors']);
    }
}

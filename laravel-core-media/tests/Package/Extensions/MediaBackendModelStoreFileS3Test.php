<?php

namespace Tests\Package\Extensions;

use AttractCores\LaravelCoreMedia\MediaStorage;
use AttractCores\LaravelCoreMedia\Models\Media;
use Tests\Feature\Media\CoreMediaTestCase;

/**
 * Class MediaBackendModelStoreFileS3Test
 *
 * @version 1.0.0
 * @date    2021-01-21
 * @author  Yure Nery <yurenery@gmail.com>
 */
class MediaBackendModelStoreFileS3Test extends CoreMediaTestCase
{

    /**
     * @var string
     */
    protected $apiS3StoreFile = 'backend.v1.s3.upload';

    /**
     * Check that we can store file and move it physically for model.
     *
     * @return void
     * @throws \Throwable
     */
    public function testBackendModelStoreFileOnS3()
    {
        // Upload file to s3.
        $response = $this->uploadFileToS3(route($this->apiS3PreSignedRoute));
        $this->assertEquals(200, $response->getStatusCode());

        // Store file in db.
        $response = $this->postJson(route($this->apiS3StoreFile), [
            'original_name' => $this->defaultFileName,
            'path'          => $path = MediaStorage::getTmpFileName($this->defaultFileName),
        ]);

        $data = $response->decodeResponseJson()->json('data');

        $this->withAuthorizationToken();

        // Attach file to user model.
        $response = $this->putJson(route('backend.update'), [
            'avatar_id' => $data[ 'id' ],
        ]);

        $data = $response->decodeResponseJson()->json('data');

        $this->assertEquals('s3', $data['disk']);
        $this->assertTrue(MediaStorage::adapterByDisk('s3')->storage()->exists($data[ 'path' ]));
    }

    /**
     * Check that we can store file and move it physically for model.
     *
     * @return void
     * @throws \Throwable
     */
    public function testBackendModelStoreMultipleFilesToTheModel()
    {
        $files = $this->mockSeveralUploadedMedia(4);

        $this->withAuthorizationToken();

        // Attach file to user model.
        $response = $this->putJson(route('backend.update-files'), [
            'files_ids' => $ids = $files->pluck('id')->shuffle(),
        ]);

        $data = $response->decodeResponseJson()->json('data');

        $this->assertCount(4, $data);
        $this->assertEquals($ids->first(), $data[0]['id']);
        $this->assertEquals(1, $data[0]['order']);
        $this->assertEquals($ids->last(), $data[3]['id']);
        $this->assertEquals(4, $data[3]['order']);

        $filesNew = $this->mockSeveralUploadedMedia(2);

        // Attach file to user model.
        $response = $this->putJson(route('backend.update-files'), [
            'files_ids' => $ids = $files->merge($filesNew)->pluck('id')->shuffle(),
        ]);

        $data = $response->decodeResponseJson()->json('data');

        $this->assertCount(6, $data);
        $this->assertEquals($ids->first(), $data[0]['id']);
        $this->assertEquals(1, $data[0]['order']);
        $this->assertEquals($ids->last(), $data[5]['id']);
        $this->assertEquals(6, $data[5]['order']);
    }

    /**
     * Set up each test.
     *
     * @throws \Throwable
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->apiS3PreSignedRoute = 'backend.v1.s3.pre-signed';
    }

}

<?php

namespace Tests\Feature\Media;

use AttractCores\LaravelCoreMedia\MediaStorage;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class MediaLocalUploadTest
 *
 * @version 1.0.0
 * @date    2021-05-21
 * @author  Yure Nery <yurenery@gmail.com>
 */
class MediaLocalUploadTest extends CoreMediaTestCase
{

    /**
     * @var string
     */
    protected $apiStoreFile = 'backend.v1.local.upload';

    protected $uploadPath = __DIR__ . '/../../local_upload';

    /**
     * Setup each test
     *
     * @throws \Throwable
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->beforeApplicationDestroyed(function () {
            MediaStorage::adapterByDisk('public')->getAdapter()->setPathPrefix(__DIR__ . '/../..');
            MediaStorage::adapterByDisk('public')->deleteDirectory('local_upload');
        });

        $this->afterApplicationCreated(function(){
            MediaStorage::adapterByDisk('public')->getAdapter()->setPathPrefix(__DIR__ . '/../..');
            MediaStorage::adapterByDisk('public')->deleteDirectory('local_upload');
        });

        config([ 'filesystems.default' => 'public' ]);
        MediaStorage::adapterByDisk('public')->getAdapter()->setPathPrefix($this->uploadPath);
    }

    /**
     * Check that we can get backend pre signed url for default file.
     *
     * @return void
     * @throws \Throwable
     */
    public function testBackendRouteLocalUpload()
    {
        $this->withAuthorizationToken();
        $response = $this->postJson(route($this->apiStoreFile), [
            'file' => UploadedFile::fake()->image('default.png')->size(10000),
        ]);

        MediaStorage::adapterByDisk('public')->exists($this->uploadPath);
        $data = $response->decodeResponseJson()->json('data');

        $this->assertNotNull($data['url']);
        $this->assertNotNull($data['id']);
    }


}

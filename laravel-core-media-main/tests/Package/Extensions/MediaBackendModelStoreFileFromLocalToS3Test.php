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
class MediaBackendModelStoreFileFromLocalToS3Test extends CoreMediaTestCase
{

    protected $path = __DIR__ . '/../../resources';

    /**
     * Setup each test
     *
     * @throws \Throwable
     */
    public function setUp() : void
    {
        parent::setUp();
        config([ 'filesystems.disks.public.root' => $this->path ]);
    }

    /**
     * Check that we can store file and move it physically for model.
     *
     * @return void
     * @throws \Throwable
     */
    public function testBackendModelStoreFileOnLocalThenOnS3()
    {

        $model = Media::factory()->createOne([
            'disk' => 'public',
            'path' => 'default.png',
            'is_mocked' => false,
        ]);

        $this->withAuthorizationToken();

        // Attach file to user model.
        $response = $this->putJson(route('backend.update'), [
            'avatar_id' => $model->getKey(),
        ]);

        $model = $model->fresh();

        $data = $response->decodeResponseJson()->json('data');

        $this->assertEquals('s3', $model->disk);
        $this->assertTrue(MediaStorage::adapterByDisk($model->disk)->storage()->exists($model->path));
    }

}

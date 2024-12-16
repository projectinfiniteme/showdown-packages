<?php

namespace Tests\Unit;

use AttractCores\LaravelCoreMedia\MediaStorage;
use AttractCores\LaravelCoreMedia\Models\Media;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Media\CoreMediaTestCase;

/**
 * Class UrgentMediaStorageMethodsTest
 *
 * @version 1.0.0
 * @date    2021-05-21
 * @author  Yure Nery <yurenery@gmail.com>
 */
class UrgentMediaStorageMethodsTest extends CoreMediaTestCase
{

    protected $path = __DIR__ . '/../resources';

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
     * Check that we can get backend pre signed url for default file.
     *
     * @return void
     * @throws \Throwable
     */
    public function testMediaStorageAdapterCrossDiskMoveWorksAsExpected()
    {
        $model = Media::factory()->createOne([
            'disk' => 'public',
            'path' => 'default.png',
        ]);

        try {
            if (
            MediaStorage::adapterByDisk($model->disk)
                        ->move($model->path, $path = 'tmp/' . $model->name, $disk = config('filesystems.default'))
            ) {
                $model->disk = $disk;
                $model->path = $path;
                $model->saveQuietly();
            }
            $this->assertTrue(true);
        } catch ( \Exception $e ) {
            $this->assertTrue(false, $e->getMessage());
        }
    }


}

<?php

namespace AttractCores\LaravelCoreMedia\Testing;

use AttractCores\LaravelCoreMedia\MediaStorage;
use AttractCores\LaravelCoreMedia\Models\Media;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

/**
 * Trait WithMediaUploads
 *
 * @version 1.0.0
 * @date    2021-01-20
 * @author  Yure Nery <yurenery@gmail.com>
 */
trait WithMediaUploads
{

    /**
     * Route for pre-signed url.
     *
     * @var string
     */
    protected string $apiS3PreSignedRoute = 's3.pre-signed';

    /**
     * Name for default file for real testing.
     *
     * @var string
     */
    protected string $defaultFileName = 'default.png';

    /**
     * NAme of default testing disk.
     *
     * @var string
     */
    protected string $defaultTestingDisk = 'test-resources';

    /**
     * Tear down the test.
     */
    public function setUpMediaUploads() : void
    {
        config()->set('filesystems.disks', array_merge(config('filesystems.disks'), [
            $this->defaultTestingDisk => [
                'driver'     => 'local',
                'root'       => $this->getTestResourcesPath(),
                'url'        => env('APP_URL') . '/test-resources',
                'visibility' => 'public',
            ],
        ]));

        $this->beforeApplicationDestroyed(function () {
            // Remove old files from storage.
            Media::chunk(10, function (Collection $collection) {
                /** @var Media $item */
                foreach ( $collection as $item ) {
                    $item->removeFromStorage();
                }
            });

        });
    }

    /**
     * Return path to testing resources.
     *
     * @return string
     */
    protected function getTestResourcesPath()
    {
        return __DIR__ . '/../../tests/resources';
    }

    /**
     * Mock one uploaded file for test.
     *
     * @param int   $count
     * @param array $options
     *
     * @return Collection
     */
    protected function mockSeveralUploadedMedia($count = 2, $options = [])
    {
        $collection = collect([]);

        for ( $i = 0; $i < $count; $i++ ) {
            $file = $this->mockOneUploadedMedia(sprintf('default%s.png', $i), $options);
            $collection->push($file);
        }

        return $collection;
    }

    /**
     * Mock one uploaded file for test.
     *
     * @param string $defaultName
     * @param array  $options
     *
     * @return Media
     */
    protected function mockOneUploadedMedia($defaultName = 'default.png', $options = [])
    {
        return Media::factory()->create(array_merge($options, [
            'is_mocked'     => true,
            'original_name' => $defaultName,
            'name'          => $defaultName,
            'path'          => MediaStorage::getTmpFileName($defaultName),
        ]));
    }

    /**
     * Upload default file to s3.
     *
     * @param        $apiPerSignedUrl
     *
     * @param string $defaultName
     * @param string $defaultPath
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    protected function uploadFileToS3($apiPerSignedUrl, $defaultName = 'default.png', $defaultPath = 'default.png')
    {
        $url = $this->getPreSignedUrl($apiPerSignedUrl, $defaultName, $defaultPath)
                    ->decodeResponseJson()
                    ->json('data.pre-signed-url');


        $client = new Client();

        return $client->put($url, [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => Storage::disk($this->defaultTestingDisk)->get($defaultPath),
                ],
            ],
        ]);
    }

    /**
     * Return response with pre-signed url.
     *
     * @param        $url
     *
     * @param string $defaultName
     * @param string $defaultPath
     *
     * @return TestResponse
     */
    protected function getPreSignedUrl($url, $defaultName = 'default.png', $defaultPath = 'default.png')
    {
        $this->withAuthorizationToken();

        return $this->postJson($url, [
            'Key'           => MediaStorage::getTmpFileName($defaultName),
            'ContentType'   => Storage::disk($this->defaultTestingDisk)->mimeType($defaultPath),
            'ContentLength' => Storage::disk($this->defaultTestingDisk)->size($defaultPath),
        ]);
    }

}

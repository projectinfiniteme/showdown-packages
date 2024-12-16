<?php
namespace Tests\Feature\Media;

use AttractCores\LaravelCoreMedia\MediaStorage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

/**
 * Class MediaBackendS3Test
 *
 * @version 1.0.0
 * @date    2019-08-27
 * @author  Yure Nery <yurenery@gmail.com>
 */
class MediaBackendS3Test extends CoreMediaTestCase
{

    /**
     * @var string
     */
    protected $apiS3PreSignedSourceRoute = 'backend.v1.s3.source-link';

    /**
     * @var string
     */
    protected $apiS3StoreFile = 'backend.v1.s3.upload';

    /**
     * Check that we can get backend pre signed url for default file.
     *
     * @return void
     * @throws \Throwable
     */
    public function testBackendS3PreSignedRoute()
    {
        $response = $this->getPreSignedUrl(route($this->apiS3PreSignedRoute));

        $response->assertSuccessful();
        $url = $response->decodeResponseJson()->json('data.pre-signed-url');
        $this->assertNotNull($url);
    }

    /**
     * Check that we can upload file to S3 via given pre-signed url.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function testBackendS3UploadFileToS3()
    {
        $response = $this->uploadFileToS3(route($this->apiS3PreSignedRoute));

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Check that we can fetch sources file via pre signed url.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function testBackendS3SourcesPreSignedFetchFile()
    {
        $response = $this->uploadFileToS3(route($this->apiS3PreSignedRoute));
        $this->assertEquals(200, $response->getStatusCode());

        $response = $this->getJson(route($this->apiS3PreSignedSourceRoute, [ 's3_path' => MediaStorage::getTmpFileName($this->defaultFileName) ]));

        $this->assertNotNull($url = $response->decodeResponseJson()->json('data.link'));
        $response = ( new Client() )->get($url);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Check that we can store uploaded to s3 file in DB.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function testBackendS3StoreUploadedFile()
    {
        $response = $this->uploadFileToS3(route($this->apiS3PreSignedRoute));
        $this->assertEquals(200, $response->getStatusCode());

        $response = $this->postJson(route($this->apiS3StoreFile), [
            'original_name' => $this->defaultFileName,
            'path'          => $path = MediaStorage::getTmpFileName($this->defaultFileName),
        ]);

        $data = $response->decodeResponseJson()->json('data');
        $response->assertSuccessful();
        $this->assertEquals($this->defaultFileName, $data[ 'original_name' ]);
        $this->assertEquals($this->defaultFileName, $data[ 'name' ]);
        $this->assertEquals($path, $data[ 'path' ]);
        $this->assertEquals(1, $data[ 'user_id' ]);
        $response = ( new Client() )->get(Storage::disk($data['disk'])
                                                 ->temporaryUrl($data[ 'path' ], now()->addMinute()));
        $this->assertEquals(200, $response->getStatusCode());
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

<?php
namespace Tests\Feature\Media;

use AttractCores\LaravelCoreMedia\MediaStorage;
use AttractCores\LaravelCoreMedia\Testing\WithMediaUploads;
use Tests\TestCase;

/**
 * Class CoreMediaTestCase
 *
 * @package Feature\Media
 * Date: 20.01.2021
 * Version: 1.0
 * Author: Yure Nery <yurenery@gmail.com>
 */
class CoreMediaTestCase extends TestCase
{
    use WithMediaUploads;

    /**
     * Return testing resources path.
     *
     * @return string
     */
    protected function getTestResourcesPath()
    {
        return __DIR__ . '/../../resources';
    }

    /**
     * Set up before each test.
     *
     * @throws \Throwable
     */
    public function setUp() : void
    {
        $this->beforeApplicationDestroyed(function () {
            if ( MediaStorage::storage()->exists($path = MediaStorage::getTmpFileName($this->defaultFileName)) ) {
                MediaStorage::storage()->delete($path);
            }
        });

        parent::setUp();

        config()->set('filesystems.default', 's3');
    }

    /**
     * Set up traits.
     *
     * @return array|void
     */
    protected function setUpTraits()
    {
        $uses = parent::setUpTraits();

        if ( isset($uses[ WithMediaUploads::class ]) ) {
            $this->setUpMediaUploads();
        }
    }

}
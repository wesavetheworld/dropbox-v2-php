<?php
    /**
     * Copyright (c) 2016 Alorel, https://github.com/Alorel
     * Licenced under MIT: https://github.com/Alorel/dropbox-v2-php/blob/master/LICENSE
     */

    namespace Alorel\Dropbox\Operations\Files;

    use Alorel\Dropbox\Operation\Files\Delete;
    use Alorel\Dropbox\Operation\Files\Download;
    use Alorel\Dropbox\Operation\Files\Restore;
    use Alorel\Dropbox\Operation\Files\Upload;
    use Alorel\Dropbox\Options\Builder\UploadOptions;
    use Alorel\Dropbox\Parameters\WriteMode;
    use Alorel\Dropbox\Test\DBTestCase;
    use Alorel\Dropbox\Test\NameGenerator;

    /**
     * @sleepTime  5
     * @retryCount 10
     */
    class RestoreTest extends DBTestCase {

        use NameGenerator;

        private static $r1;

        private static $r2;

        private static $n;

        const SLEEP_TIME = 5;

        static function setUpBeforeClass() {
            $opts = (new UploadOptions())->setWriteMode(WriteMode::overwrite());
            $up = new Upload();
            for ($i = 0; $i < 10; $i++) {
                try {
                    self::$n = self::genFileName();
                    self::$r1 = json_decode($up->raw(self::$n, '.', $opts)->getBody()->getContents(), true)['rev'];
                    self::$r2 = json_decode($up->raw(self::$n, '..', $opts)->getBody()->getContents(), true)['rev'];

                    return;
                } catch (\Exception $e) {
                    sleep(5);
                }
            }
        }

        function testRestoreNonDeleted() {
            $this->assertEquals(2, strlen((new Download())->raw(self::$n)->getBody()));
            $this->assertEquals(
                1,
                json_decode((new Restore())->raw(self::$n, self::$r1)->getBody()->getContents(), true)['size']
            );
        }

        /** @depends testRestoreNonDeleted */
        function testRestoreDeleted() {
            (new Delete())->raw(self::$n);
            $this->assertEquals(
                2,
                json_decode((new Restore())->raw(self::$n, self::$r2)->getBody()->getContents(), true)['size']
            );
        }
    }

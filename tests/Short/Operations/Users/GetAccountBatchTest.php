<?php
    /**
     * Copyright (c) 2016 Alorel, https://github.com/Alorel
     * Licenced under MIT: https://github.com/Alorel/dropbox-v2-php/blob/master/LICENSE
     */

    namespace Alorel\Dropbox\Short\Operations\Users;

    use Alorel\Dropbox\Operation\Users\GetAccountBatch;
    use Alorel\Dropbox\Operation\Users\GetCurrentAccount;
    use Alorel\Dropbox\Test\DBTestCase;

    /**
     * @sleepTime  5
     * @retryCount 10
     */
    class GetAccountBatchTest extends DBTestCase {

        private static $accountID;

        private static $curr;

        private static $batch;

        static function setUpBeforeClass() {
            for ($i = 0; $i < 10; $i++) {
                try {
                    self::$curr = json_decode((new GetCurrentAccount())->raw()->getBody()->getContents(), true);
                    self::$accountID = self::$curr['account_id'];
                    self::$batch = json_decode(
                        (new GetAccountBatch())->raw(self::$accountID)->getBody()->getContents(),
                        true
                    );

                    return;
                } catch (\Exception $e) {
                    sleep(5);
                }
            }
        }

        function testCount() {
            $this->assertTrue(is_array(self::$batch));
            $this->assertEquals(1, count(self::$batch));
        }

        /** @dataProvider providerFields */
        function testFields($f) {
            $this->assertEquals(self::$curr[$f], self::$batch[0][$f]);
        }

        function testWithArray() {
            $b = json_decode(
                (new GetAccountBatch())->raw(...[self::$accountID])->getBody()->getContents(),
                true
            );
            $this->assertEquals(self::$batch[0], $b[0]);
        }

        function providerFields() {
            yield ['account_id'];
            yield ['name'];
            yield ['email'];
            yield ['email_verified'];
            yield ['disabled'];
        }
    }

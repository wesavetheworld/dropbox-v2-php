<?php
    /**
     * Copyright (c) 2016 Alorel, https://github.com/Alorel
     * Licenced under MIT: https://github.com/Alorel/dropbox-v2-php/blob/master/LICENSE
     */

    namespace Alorel\Dropbox\Operations\Files;

    use Alorel\Dropbox\Operation\Files\Delete;
    use Alorel\Dropbox\Operation\Files\GetPreview;
    use Alorel\Dropbox\Operation\Files\Upload;
    use Alorel\Dropbox\Test\DBTestCase;
    use Alorel\Dropbox\Test\NameGenerator;
    use Alorel\Dropbox\Test\TestUtil;

    /**
     * @sleepTime  5
     * @retryCount 10
     */
    class GetPreviewTest extends DBTestCase {

        use NameGenerator;

        function testGetPreview() {
            $filename = self::genFileName('docx');
            try {
                (new Upload())->raw($filename, fopen(TestUtil::INC_DIR . 'forPreview.docx', 'r'));
                $response = (new GetPreview())->raw($filename);

                $this->assertNotFalse(array_search($response->getHeaderLine('content-type'),
                                                   TestUtil::$PREVIEW_CONTENT_TYPES,
                                                   true));
            } finally {
                try {
                    (new Delete())->raw($filename);
                } catch (\Exception $e) {
                    fwrite(STDERR, $e->getMessage());
                }
            }
        }
    }

<?php
    /**
     * Copyright (c) 2016 Alorel, https://github.com/Alorel
     * Licenced under MIT: https://github.com/Alorel/dropbox-v2-php/blob/master/LICENSE
     */

    namespace Alorel\Dropbox;

    use Alorel\Dropbox\Operation\AbstractOperation;
    use Alorel\Dropbox\OperationKind\RPCOperation;
    use Alorel\Dropbox\Options\Builder\GetMetadataOptions;
    use Alorel\Dropbox\Options\Mixins\AutoRenameTrait;
    use Alorel\Dropbox\Options\Options;
    use Alorel\Dropbox\Test\DBTestCase;
    use Alorel\Dropbox\Test\TestUtil;
    use ReflectionClass as RC;

    if (1 != getenv('TRAVISCI')) {

        class AntiClumsinessDBTest extends DBTestCase {

            private static $BASE_NAMESPACE;

            static function setUpBeforeClass() {
                self::$BASE_NAMESPACE = (new RC(Util::class))->getNamespaceName();
            }

            private function abstractionSubclass($baseClass, $testClass, ...$constructorArgs) {
                $rc = new RC($testClass);

                if ($rc->isInstantiable()) {
                    $this->assertInstanceOf($baseClass, $rc->newInstanceArgs($constructorArgs));
                } else {
                    $parents = [$testClass];
                    while ($rc = $rc->getParentClass()) {
                        $parents[] = $rc->getName();
                    }
                    $this->assertContains($baseClass, $parents);
                }
            }

            /** @dataProvider providerOptionBuilder */
            function testOptionBuilder($class) {
                $this->abstractionSubclass(Options::class, $class);
            }

            /** @dataProvider providerFilePaths */
            function testFilePaths($class) {
                $rf = new RC($class);
                $trim = trim(str_replace(self::$BASE_NAMESPACE, '', $rf->getNamespaceName()), '\\');
                $ns = 'src\\' . $trim . ($trim ? '\\' : '') . $rf->getShortName() . '.php';

                $this->assertNotFalse(stripos($rf->getFileName(), $ns));
            }

            function providerFilePaths() {
                return TestUtil::allClassesInClassDirectory(Util::class, true);
            }

            function providerOptionBuilder() {
                return TestUtil::allClassesInClassDirectory(GetMetadataOptions::class, true);
            }

            /** @dataProvider providerOptionMixins */
            function testOptionMixins($mixin) {
                $this->assertTrue((new RC($mixin))->isTrait());
            }

            function providerOptionMixins() {
                return TestUtil::allClassesInClassDirectory(AutoRenameTrait::class, true);
            }

            /** @dataProvider providerOperationKind */
            function testOperationKind($class) {
                $this->abstractionSubclass(AbstractOperation::class, $class);
            }

            function providerOperationKind() {
                foreach ([RPCOperation::class, AbstractOperation::class] as $srcClass) {
                    foreach (TestUtil::allClassesInClassDirectory($srcClass, true) as $k => $v) {
                        yield $k => $v;
                    }
                }
            }
        }
    }
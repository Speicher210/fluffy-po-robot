<?php

declare(strict_types = 1);

namespace Wingu\FluffyPoRobot\Tests\Command\DownloadCommand;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Console\Tester\CommandTester;
use Wingu\FluffyPoRobot\Command\DownloadCommand;
use Wingu\FluffyPoRobot\POEditor\Client;

/**
 * Test case for download command
 */
class DownloadCommandTest extends \PHPUnit_Framework_TestCase
{
    private $projectId = '123';

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    public function setUp()
    {
        $this->root = vfsStream::setup();
    }


    public static function dataProviderTestDownload()
    {
        return array(
            array(__DIR__ . '/poeditor.po.yml', 'po'),
            array(__DIR__ . '/poeditor.xml.yml', 'xml'),
            array(__DIR__ . '/poeditor.json.yml', 'json'),
            array(__DIR__ . '/poeditor.strings.yml', 'strings'),
        );
    }

    /**
     * @dataProvider dataProviderTestDownload
     * @param string $configFile
     * @param string $format
     */
    public function testDownload(string $configFile, string $format)
    {
        $allTranslations = array(
            $this->root->url() . '/source_1.en.' . $format => array(
                'tag' => 'tag_1',
                'language' => array(
                    'en' => array(
                        'terms' => array(
                            self::createTranslation('some_term', 'some definition English'),
                            self::createTranslation('some_term_2', 'some definition English 2'),
                            self::createTranslation('some_term_3', array(
                                'one' => 'One English',
                                'other' => 'Other English'
                            )),

                        ),
                        'translationFile' => $this->root->url() . '/source_1.en.' . $format
                    ),
                    'de' => array(
                        'terms' => array(
                            self::createTranslation('some_term', 'some definition German')
                        ),
                        'translationFile' => $this->root->url() . '/tmp/source_1/translation_de.' . $format
                    )
                )
            ),
            $this->root->url() . '/source_2.en.' . $format => array(
                'tag' => 'tag_2',
                'language' => array(
                    'en' => array(
                        'terms' => array(
                            self::createTranslation('some_other_term', 'some other definition English'),
                            self::createTranslation('some_other_term_2', 'some other definition English 2')
                        ),
                        'translationFile' => $this->root->url() . '/source_2.en.' . $format
                    ),
                    'de' => array(
                        'terms' => array(
                            self::createTranslation('some_other_term', 'some other definition German'),
                            self::createTranslation('some_other_term_2', array(
                                'one' => 'One German',
                                'few' => 'Few German',
                                'other' => 'Other German'
                            )),
                        ),
                        'translationFile' => $this->root->url() . '/tmp/source_2/translation_de.' . $format
                    )
                )
            )
        );

        $this->assertDownload($configFile, $allTranslations, $format);
    }

    /**
     * @param string $term
     * @param string|array $translation
     * @return array
     */
    private static function createTranslation(string $term, $translation) : array
    {
        return array(
            'term' => $term,
            'definition' => $translation,
            'term_plural' => is_array($translation) ? $term : ''
        );
    }

    /**
     * @param string $configFile
     * @param array $allTranslations
     * @param string $format
     */
    private function assertDownload(string $configFile, array $allTranslations, string $format)
    {
        $apiClientMock = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(array('export'))
            ->getMock();

        $i = 0;
        $translationFiles = array();
        foreach ($allTranslations as $source => $translationSuite) {
            touch($source);

            foreach ($translationSuite['language'] as $language => $translations) {
                $key = __DIR__ . '/Expected/' . $format . '/' . $translationSuite['tag'] . '/' . $language . '.txt';
                $translationFiles[$key] = $translations['translationFile'];

                $apiClientMock
                    ->expects(static::at($i))
                    ->method('export')
                    ->with(
                        $this->projectId,
                        $language,
                        $translationSuite['tag']
                    )
                    ->willReturn($translations['terms']);

                $i++;
            }
        }

        $command = new class ($apiClientMock) extends DownloadCommand
        {
            /**
             * @var Client
             */
            private $apiClientMock;

            public function __construct($apiClientMock)
            {
                $this->apiClientMock = $apiClientMock;

                parent::__construct();
            }

            protected function initializeApiClient(string $apiToken) : Client
            {
                return $this->apiClientMock;
            }
        };

        $commandTester = new CommandTester($command);
        $commandTester->execute(array('config-file' => $configFile));

        foreach ($translationFiles as $expected => $translationFile) {
            $this->assertFileExists($translationFile);
            $this->assertFileEquals($expected, $translationFile);
        }
    }
}

<?php

namespace Piwik\Plugins\ExtraTools\tests\Integration;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Common;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group ExtraTools
 * @group Plugins
 * @group Console
 */
class CommandsTest extends ConsoleCommandTestCase
{

    public function testSiteAddWithoutWebsiteNameShouldFail()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'site:add',
            '-vvv' => true,
        ));
        $this->assertEquals(1, $code);
        $this->assertStringContainsStringIgnoringCase("The website name can't be empty", $this->applicationTester->getDisplay());
    }
    public function testSiteAddWithWebsiteNameShouldSuceed()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'site:add',
            '--name' => 'Foo',
            '-vvv' => true,
        ));
        $this->assertEquals(0, $code);
        $this->assertStringContainsStringIgnoringCase("Site Foo added", $this->applicationTester->getDisplay());
    }

    public function testSiteAddWithWebsiteNameAndUrlShouldSuceed()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'site:add',
            '--name' => 'Foo',
            '--urls' => 'https://foo.bar',
            '-vvv' => true,
        ));
        $this->assertEquals(0, $code);
        $this->assertStringContainsStringIgnoringCase("Site Foo added", $this->applicationTester->getDisplay());
    }

    public function testSiteListShouldSuceedAndShowUrl()
    {
        $code = $this->applicationTester->run(array(
            'command' => 'site:list',
            '-vvv' => true,
        ));
        $this->assertEquals(0, $code);
        $this->assertStringContainsStringIgnoringCase("main-url: https://foo.bar", $this->applicationTester->getDisplay());
    }

    public function testSiteDeleteShouldFailWithoutId()
    {
       // $this->applicationTester->setInputs(['yes']);
        $code = $this->applicationTester->run(array(
            'command' => 'site:delete',
            '-vvv' => true,
        ));
        $this->assertEquals(1, $code);
        $this->assertStringContainsStringIgnoringCase("You must provide an id for the site to delete", $this->applicationTester->getDisplay());
    }


    public function testSiteDeleteWithIdShouldSucceed()
    {
        $this->applicationTester->setInputs(['yes']);
        $code = $this->applicationTester->run(array(
            'command' => 'site:delete',
            '--id' => '1',
            '-vvv' => true,
        ));
        $this->assertEquals(0, $code);
        $this->assertStringContainsStringIgnoringCase("Are you really sure you would like to delete site", $this->applicationTester->getDisplay());
    }

}


<?php
/**
 * Copyright (c) 2018 Damien Clark
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 *
 */

/**
 * User: clarkd
 * Date: 23/7/18
 * Time: 4:27 PM
 */

namespace PdoFactory\Test;

use PHPUnit\Framework\TestCase;
use PdoFactory\Source\{
  ConfigSourceFactory, ConfigSourceException
};

final class ConfigSourceFactoryTest extends TestCase
{

  public static function setUpBeforeClass()
  {
    // Remove read permissions before tests
    chmod(__DIR__.'/etc/noperms.ini', 0044);
  }

  public static function tearDownAfterClass()
  {
    // Return read permissions after test
    chmod(__DIR__.'/etc/noperms.ini', 0444);
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillThrowOnConfigFileNotFound()
  {
    $this->expectException(ConfigSourceException::class);
    $this->expectExceptionCode(ConfigSourceException::FILE_NOT_FOUND);
    ConfigSourceFactory::create('/tmp/bla');
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillThrowOnUnsupportedSourceType()
  {
    $this->expectException(ConfigSourceException::class);
    $this->expectExceptionCode(ConfigSourceException::UNSUPPORTED_SOURCE_TYPE);
    ConfigSourceFactory::create('tests/etc/pdofactory.meh');
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillThrowOnNoReadPermissions()
  {
    $this->expectException(ConfigSourceException::class);
    $this->expectExceptionCode(ConfigSourceException::FILE_PERMS);
    ConfigSourceFactory::create('tests/etc/noperms.ini');
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillLoadOnSupportedSource()
  {
    $this->assertInstanceOf(
      'PdoFactory\Source\IniConfigSource',
      ConfigSourceFactory::create('tests/etc/pdofactory.ini')
    );
  }
}

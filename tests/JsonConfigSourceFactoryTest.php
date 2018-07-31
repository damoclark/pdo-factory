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

final class JsonConfigSourceFactoryTest extends TestCase
{

  /**
   * @throws ConfigSourceException
   */
  public function testWillLoadIniConfigFile()
  {
    $c = ConfigSourceFactory::create('tests/etc/valid.json');
    $this->assertArraySubset(
      [
        'driver' => 'pgsql',
        'username' => 'user',
        'password' => 'pass',
        'attribute' => [
          'PDO::ATTR_TIMEOUT' => 30,
          'PDO::ATTR_ERRMODE' => 'PDO::ERRMODE_EXCEPTION'
        ],
        'dsn' => [
          'host' => 'localhost',
          'dbname' => 'db',
        ]
      ],
      $c->getConfig('server')
    );
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillReturnCorrectDSN()
  {
    $c = ConfigSourceFactory::create('tests/etc/valid.json');
    $this->assertEquals('pgsql:host=localhost;dbname=db', $c->getDSN('server'));
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillThrowOnInvalidPDOParameters()
  {
    $this->expectException(\Respect\Validation\Exceptions\ValidationException::class);
    ConfigSourceFactory::create('tests/etc/invalid.json');
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillThrowOnInvalidIniFormat()
  {
    $this->expectException(ConfigSourceException::class);
    ConfigSourceFactory::create('tests/etc/invalid2.json');
  }

}

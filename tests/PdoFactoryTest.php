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
 * Date: 26/7/18
 * Time: 4:05 PM
 */

namespace PdoFactory\Test;

use PdoFactory\PdoFactory;
use PHPUnit\Framework\TestCase;
use PdoFactory\Source\ConfigSourceException;

class PdoFactoryTest extends TestCase
{
  /**
   * @throws ConfigSourceException
   */
  public function testGetIniDSN()
  {
    $pdoFactory = new PdoFactory('tests/etc/valid.ini');

    $this->assertEquals('pgsql:host=localhost;dbname=db', $pdoFactory->getDSN('server'));
  }

  /**
   * @throws ConfigSourceException
   */
  public function testGetJsonDSN()
  {
    $pdoFactory = new PdoFactory('tests/etc/valid.json');

    $this->assertEquals('pgsql:host=localhost;dbname=db', $pdoFactory->getDSN('server'));
  }

  /**
   * @throws ConfigSourceException
   */
  public function testWillThrowOnConfigFileNotFound()
  {
    $this->expectException(ConfigSourceException::class);
    $this->expectExceptionCode(ConfigSourceException::FILE_NOT_FOUND);
    new PdoFactory('/tmp/bla');
  }

  /**
   * @throws ConfigSourceException
   */
  public function testGetAttribute()
  {
    $pdoFactory = new PdoFactory('tests/etc/valid.json');
    $this->assertArraySubset(
      [
        '2' => 30,
        '3' => '2' // These are the constant values from valid.json
      ],$pdoFactory->getAttributes('server')
    ) ;
  }

  /**
   * @throws ConfigSourceException
   */
  public function testCreatePDO()
  {
    $pdoFactory = new PdoFactory('tests/etc/pdofactory.ini');
    $pdo = $pdoFactory->createPDO('server');
    $this->assertTrue($pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER));
  }

  /**
   * @throws ConfigSourceException
   */
  public function testGetConfig()
  {
    $pdoFactory = new PdoFactory('tests/etc/valid.ini');
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
      $pdoFactory->getConfig('server')
    );
  }
}

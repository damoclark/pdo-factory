<?php /** @noinspection PhpParamsInspection */
/** @noinspection PhpMethodParametersCountMismatchInspection */
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
 * Time: 11:01 AM
 */

namespace PdoFactory\Source;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator as v;

/**
 * Class AbstractConfigSource that loads and parses PdoFactory config files
 *
 * @package PdoFactory\Source
 */
abstract class AbstractConfigSource
{

  /**
   * @var string Path to input file
   */
  protected $input = null;

  /**
   * @var array Parsed data from $input as PHP data structure but not yet processed (normalised)
   */
  protected $source = null;

  /**
   * @var array Processed $source data normalised to a predetermined data structure for PdoFactory
   */
  protected $config = [];

  /**
   * AbstractConfigSource constructor.
   *
   * @param string $input Path to input file
   *
   * @throws ConfigSourceException
   * @throws ValidationException if config input is invalid
   */
  public function __construct(string $input)
  {
    // store input path
    $this->input = $input;

    // parse input
    $this->parse();

    // validate input
    $this->validate();

    // process and sanitise input
    $this->process();
  }

  /**
   * Implement parse to read from $this->input and convert into a php datastructure
   *
   * @throws ConfigSourceException
   * @return void
   */
  abstract protected function parse();

  /**
   * @todo Refactor this into separate classes
   * @throws ConfigSourceException
   * @throws ValidationException
   */
  protected function validate()
  {

    // Make sure we have an array
    v::arrayType()->check($this->config);
    // Now validate each connection of the array
    foreach ($this->source as $value) {
      // Make sure a driver is provided
      v::key('driver', v::stringType())->check($value);
      // Based on the driver, check the parameters valid
      switch ($value['driver']) {
        case 'pgsql':
          v::keySet(
            v::key('driver', v::stringType()), // Have to test again (keySet test is holistic)
            v::key('host', v::stringType(), false),
            v::key('port', v::stringType(), false),
            v::key('dbname', v::stringType(), false),
            v::key('username', v::stringType(), false),
            v::key('password', v::stringType(), false),
            v::key('attribute', v::oneOf(v::arrayType(), v::stringType()), false)
          )->check($value);
          break;
        case 'sqlite':
          v::keySet(
            v::key('driver', v::stringType()), // Have to test again (keySet test is holistic)
            v::key('path', v::stringType()),
            v::key('attribute', v::oneOf(v::arrayType(), v::stringType()), false)
          )->check($value);
          break;
        case 'sqlsrv':
          v::keySet(
            v::key('driver', v::stringType()),
            v::key('server', v::stringType()),
            v::key('Database', v::stringType()),
            v::key('username', v::stringType()),
            v::key('password', v::stringType(), false),
            v::key('attribute', v::oneOf(v::arrayType(), v::stringType()), false)
          )->check($value);
          break;
        default:
          throw new ConfigSourceException(ConfigSourceException::DRIVER_NOT_SUPPORTED);
        // todo Implement other PDO drivers here
      }
    }

  }

  /**
   * Generate DSN string for given connection
   *
   * @example
   * So go from:
   * [server]
   * driver=pgsql
   * host=localhost
   * dbname=db
   * username=user
   * password=pass
   *
   * To:
   * 'pgsql:host=localhost;dbname=db'
   *
   * @param string $connection The name of the connection profile
   *
   * @return string The DSN string for the given connection profile
   * @throws \PdoFactory\Source\ConfigSourceException
   */
  public function getDSN($connection)
  {
    $conf = $this->config[$connection] ?? null;
    if (is_null($conf)) {
      throw new ConfigSourceException();
    } // todo finalise exception thrown

    switch ($conf['driver']) {
      case 'pgsql':
        // Set the driver name first
        $dsn = "{$conf['driver']}:";
        // Add parameters
        $dsn .= join( // Go from ('dbname'=>'db','port'=>5432,'host'=>'localhost')
          ';',  // to 'dbname=db;port=5432;host=localhost'
          array_map(
            function ($a, $b) {
              return join('=', [$a, $b]);
            },
            array_keys($conf['dsn']),
            array_values($conf['dsn'])
          )
        );
        break;
      case 'sqlite':
        // Set the driver name first
        $dsn = "{$conf['driver']}:{$conf['dsn']['path']}";
        break;
      default:
        throw new ConfigSourceException(ConfigSourceException::STANZA_NOT_FOUND);
      // todo Implement other PDO drivers here
    }

    return $dsn;
  }

  /**
   * Retrieve any PDO attributes to be set for the given connection
   * @param string $connection The name of the connection profile
   * @return array Key is attribute name and value is the value to be set
   * @throws ConfigSourceException
   */
  public function getAttributes($connection)
  {
    $conf = $this->config[$connection] ?? null;
    if (is_null($conf)) {
      throw new ConfigSourceException();
    } // todo finalise exception thrown

    $attr = [];

    foreach ($conf['attribute'] as $name => $value) {
      // If numeric, then store numerically. If a string, convert to numeric
      $n = (is_numeric($name)) ? $name : constant($name);
      $v = (is_numeric($value)) ? $value : constant($value);

      $attr[$n] = $v;
    }

    return $attr;
  }

  /**
   * Return the username for the given connection
   * @param string $connection The name of the connection profile
   * @return string Username
   */
  public function getUsername($connection)
  {
    return $this->config[$connection]['username'];
  }

  /**
   * Return the password for the given connection
   * @param string $connection The name of the connection profile
   * @return string Password
   */
  public function getPassword($connection)
  {
    return $this->config[$connection]['password'];
  }

  public function getConfig($connection)
  {
    return $this->config[$connection];
  }

  /**
   * Take parsed input from $this->source and normalise into a data structure for PdoFactory
   */
  protected function process()
  {
    foreach ($this->source as $connection => $value) {
      $conn = array('driver' => $value['driver'], 'username' => null, 'password' => null, 'attribute' => []);
      unset($value['driver']);
      if (isset($value['username'])) {
        $conn['username'] = $value['username'];
        unset($value['username']);
      }
      if (isset($value['password'])) {
        $conn['password'] = $value['password'];
        unset($value['password']);
      }
      if (isset($value['attribute'])) {
        $conn['attribute'] = (is_array($value['attribute'])) ? $value['attribute'] : [$value['attribute']];
        unset($value['attribute']);
      }
      $conn['dsn'] = $value; // Whatever is left is the dsn
      $this->config[$connection] = $conn; // Store record with new sanitised structure
    }
  }
}
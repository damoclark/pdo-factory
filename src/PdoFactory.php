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
 * Date: 22/7/18
 * Time: 2:40 PM
 */

namespace PdoFactory;

use PdoFactory\Source\AbstractConfigSource;
use PdoFactory\Source\ConfigSourceFactory;

/**
 * Class PdoFactory
 * @package PdoFactory
 */
class PdoFactory
{
  /**
   * @var string Path to the configuration file
   */
  protected $source = 'etc/pdofactory.ini';

  /**
   * @var array Holds data structure representing the PDO configurations
   */
  protected $config = null;

  /**
   * PdoFactory constructor.
   *
   * @param string $source Location of the config file (defaults to etc/pdofactory.ini
   * @throws Source\ConfigSourceException
   */
  public function __construct(string $source = 'etc/pdofactory.ini')
  {
    $this->source = $source;
    $this->config = $this->loadConfig($source);
  }

  /**
   * Given the connection name, return an instance of a connected PDO object
   *
   * @param string $connection Name of config connection in ini file to retrieve connection parameters
   *
   * @return \PDO A new instance of PDO connected using credentials provided by connection
   * @throws \PDOException
   * @throws Source\ConfigSourceException
   */
  public function createPDO(string $connection)
  {
    $attr = $this->config->getAttributes($connection);

    $pdo = new \PDO($this->config->getDSN($connection),$this->getUsername($connection),$this->getPassword($connection));
    foreach ($attr as $name => $value) {
      $pdo->setAttribute($name,$value);
    }
    return $pdo;
  }

  /**
   * Given the connection name, return the PDO connection DSN to be used for connecting via PDO
   *
   * @param string $connection Name of config connection in ini file to retrieve connection parameters
   *
   * @return string Connection DSN for this connection
   * @throws Source\ConfigSourceException
   */
  public function getDSN(string $connection)
  {
    return $this->config->getDSN($connection);
  }

  /**
   * Retrieve any PDO attributes to be set for the given connection
   * @param string $connection The name of the connection profile
   * @return array Key is attribute name and value is the value to be set
   * @throws ConfigSourceException
   */
  public function getAttributes($connection)
  {
    return $this->config->getAttributes($connection);
  }

  /**
   * Return the username for the given connection
   * @param $connection
   * @return string Usernane
   */
  public function getUsername(string $connection)
  {
    return $this->config->getUsername($connection);
  }

  /**
   * Return the password for the given connection
   * @param $connection
   * @return string Password
   */
  public function getPassword(string $connection)
  {
    return $this->config->getPassword($connection);
  }

  /**
   * Given the connection name, return a PHP data structure representing the config for PDO connection
   *
   * @param string $connection Name of config connection in ini file to retrieve connection parameters
   *
   * @return array An array containing the basic configuration items from the config file
   */
  public function getConfig(string $connection)
  {
    return $this->config->getConfig($connection);
  }

  /**
   * Load configuration file containing connection parameters for PDO
   *
   * @param string $source Path to the configuration file
   * @return AbstractConfigSource
   * @throws Source\ConfigSourceException
   */
  protected function loadConfig($source)
  {
    return ConfigSourceFactory::create($source);
  }
}

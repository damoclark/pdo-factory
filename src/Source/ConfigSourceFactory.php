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
 * Time: 11:33 AM
 */

namespace PdoFactory\Source;

use Webmozart\PathUtil\Path;

class ConfigSourceFactory
{
  public const CONFIG_SOURCE_TYPE_NAMES = array
  (
    'ini' => 'PdoFactory\Source\IniConfigSource',
    'json' => 'PdoFactory\Source\JsonConfigSource',
  );

  protected const CONFIG_SOURCE_TYPES = array
  (
    '/\.ini$/' => 'PdoFactory\Source\IniConfigSource',
    '/\.json$/' => 'PdoFactory\Source\JsonConfigSource',
  );

  /**
   * @param $source
   * @param string $type Explicit input format type (e.g. ini, yaml, json)
   * @return AbstractConfigSource
   * @throws ConfigSourceException
   */
  static public function create($source, $type = null)
  {
    $configSourceName = null;

    list($script_path) = get_included_files();

    // Validate $input exists
    if (Path::isRelative($source)) {
      $d = Path::getDirectory($script_path);
      do { // Loop through until find the config file
        $s = Path::join(array($d, $source));
        if (file_exists($s)) {
          if (!is_file($s)) {
            continue;
          } // Try next parent

          if (!is_readable($s)) {
            throw new ConfigSourceException(
              "Unable to read config file '$s''",
              ConfigSourceException::FILE_PERMS
            );
          }

          // Otherwise, we have found a viable config file
          $validatedSource = $s;
          break;
        }
      } while (($d !== Path::getDirectory($d)) and ($d = Path::getDirectory($d)));
      // If we didn't locate the relative config file
      if (!isset($validatedSource)) {
        throw new ConfigSourceException(
          "Cannot find config file '$source'",
          ConfigSourceException::FILE_NOT_FOUND
        );
      }
      unset($s, $d);
    } else {
      $validatedSource = $source;

      if (!file_exists($source)) {
        throw new ConfigSourceException(
          "Cannot find config file '$source'",
          ConfigSourceException::FILE_NOT_FOUND
        );
      }

      if (!is_readable($source)) {
        throw new ConfigSourceException(
          "Unable to read config file '$source''",
          ConfigSourceException::FILE_PERMS
        );
      }
    }

    // Determine type by $type
    if (!is_null($type) and array_key_exists($type, self::CONFIG_SOURCE_TYPE_NAMES)) {
      $configSourceName = self::CONFIG_SOURCE_TYPE_NAMES[$type];
    } else // Otherwise, determine type by $input
    {
      foreach (self::CONFIG_SOURCE_TYPES as $re => $t) {
        if (preg_match($re, $source)) {
          // Found the input, so set it and break out of loop
          $configSourceName = $t;
          break;
        }
      }
    }

    // Determine if $input is supported and if not, throw ConfigSourceError Exception with code indicating invalid input
    if (is_null($configSourceName)) {
      throw new ConfigSourceException(
        "No supported ConfigSource for input '$source'",
        ConfigSourceException::UNSUPPORTED_SOURCE_TYPE
      );
    }

    // Load the input
    $configSource = new $configSourceName($validatedSource);

    // Return the input
    return $configSource;
  }
}

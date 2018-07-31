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
 * Time: 11:00 AM
 */

namespace PdoFactory\Source;

class JsonConfigSource extends AbstractConfigSource
{

  /**
   * Parse JSON config file format into PHP data structure
   * @return void
   * @throws ConfigSourceException
   */
  protected function parse()
  {
    $json = file_get_contents($this->input);
    $this->source = json_decode($json, true);
    if (is_null($this->source)) {
      throw new ConfigSourceException(
        "Error parsing config file '{$this->input}'",
        ConfigSourceException::PARSE_ERROR
      );
    }
  }

}

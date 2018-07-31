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
 * Time: 11:50 AM
 */

namespace PdoFactory\Source;


class ConfigSourceException extends \Exception
{
  public const UNSUPPORTED_SOURCE_TYPE = 1;
  public const FILE_PERMS = 2;
  public const FILE_NOT_FOUND = 4;
  public const PARSE_ERROR = 8;
  public const VALIDATION_ERROR = 16;
  public const DRIVER_NOT_SUPPORTED = 32;
  public const STANZA_NOT_FOUND = 64;
}
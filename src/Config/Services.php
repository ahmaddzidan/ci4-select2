<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Asanusi007\Select2\Config;

use Asanusi007\Select2\Select2;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    /**
     * Select2Engine
     *
     * Select2 Services
     *
     * @param bool $getShared Shared instance
     *
     * @return object
     */
    public static function Select2Engine($config = [], $getShared = true)
    {
        return $getShared === true ? static::getSharedInstance('Select2Engine') : new Select2($config);
    }
}

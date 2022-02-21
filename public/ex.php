<?php

function dd($var, $var1 = null)
{
    echo "<pre>";
    print_r($var);
    if (isset($var1)) {
        print_r($var1);
    }
    echo "</pre>";
}

class LRUCache
{
    public $capacity;
    public $bucket;
    private $leastUsed;
    private $cacheKeys;

    function __construct($capacity)
    {
        $this->capacity = $capacity;
    }

    function put($key, $value)
    {
        $bucketCap = @count($this->bucket) + 1;

        if ($bucketCap < $this->capacity) {
            $this->bucket[$key] = $value;
            $this->cacheKeys = array_keys($this->bucket);
        } else if ($bucketCap == $this->capacity) {
            $this->bucket[$key] = $value;
            $this->cacheKeys = array_keys($this->bucket);
        } else if ($bucketCap > $this->capacity) {
            $this->cacheKeys = array_keys($this->bucket);
            if (isset($this->leastUsed) && !empty($this->leastUsed)) {
                $leastUsedKey = $this->leastUsed;
            } else {
                $leastUsedKey = $this->cacheKeys[0];
                unset($this->cacheKeys[0]);
            }
            if (isset($leastUsedKey) && !empty($leastUsedKey)) {
                if (isset($this->bucket[$leastUsedKey])) {
                    unset($this->bucket[$leastUsedKey]);
                } else {
                    $leastUsedKey = $this->cacheKeys[0];
                    unset($this->cacheKeys[0]);
                    unset($this->bucket[$leastUsedKey]);
                }
            }
            $this->bucket[$key] = $value;
            $this->cacheKeys = array_keys($this->bucket);
        }
    }

    function get($key)
    {
        if (isset($this->bucket[$key]) && !empty($this->bucket[$key])) {
            return $this->bucket[$key];
        } else {
            throw new Exception("Key not found", 1);
        }
    }

    function has($key)
    {
        if (isset($this->bucket[$key]) && !empty($this->bucket[$key])) {
            $this->leastUsed = $key;
            return true;
        } else {
            return false;
        }
    }

    function remove($key)
    {
        if (isset($this->bucket[$key]) && !empty($this->bucket[$key])) {
            unset($this->bucket[$key]);
        }
    }

    function size($key)
    {
        if (isset($this->bucket) && !empty($this->bucket) && is_array($this->bucket)) {
            $count = count($this->bucket);
            if ($count > 0) {
                return $count;
            } else {
                return 0;
            }
        } else {
            return false;
        }
    }
}

$cache = new LRUCache(2);

$cache->put('user1', 'data1');
$cache->get('user1'); // 'data1'
$cache->put('user3', 'data3');
$cache->put('user2', 'data2');
dd($cache->bucket);

var_dump($cache->has('user1')); // false
echo '<br>';
var_dump($cache->has('user2')); // true
echo '<br>';
var_dump($cache->has('user3')); // true
echo '<br>';

$cache->get('user2'); // data2
$cache->put('user4', 'data4');

echo '<br>';
echo '<br>';
var_dump($cache->has('user3')); // false
echo '<br>';
var_dump($cache->has('user2')); // true
echo '<br>';
var_dump($cache->has('user4')); // true
echo '<br>';
dd($cache->bucket);

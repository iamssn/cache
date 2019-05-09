<?php
namespace iamssn\cache;
/**
 * Class Cache
 * @package cache
 * @mixin Driver
 * @mixin Redis
 */
class Cache
{
    /**
     * 缓存实例
     * @var array
     */
    protected $instance = [];

    /**
     * 缓存配置
     * @var array
     */
    protected $config = [
        // 缓存配置为复合类型
        'type'  =>  'complex',
        'default'	=>	[
            'type'   => 'Redis', //缓存类型
            'host'       => '',
            'port'       => 6379,
            'password'   => '',
            'select'     => 5,
            'timeout'    => 0,
            'expire'     => 0,//全局缓存有效期（0为永久有效）
            'persistent' => true,
            'prefix'     => '',// 缓存前缀
            'serialize'  => true,
        ],
        'user'	=>	[
            'type'   => 'Redis', //缓存类型
            'host'       => '',
            'port'       => 6379,
            'password'   => '',
            'select'     => 6,
            'timeout'    => 0,
            'expire'     => 0,//全局缓存有效期（0为永久有效）
            'persistent' => true,
            'prefix'     => '',// 缓存前缀
            'serialize'  => true,
        ],
    ];
    /**
     * 操作句柄
     * @var object
     */
    protected $handler;

    public function __construct()
    {
        $this->init($this->config);
    }

    /**
     * 连接缓存
     * @access public
     * @param  array         $options  配置数组
     * @param  bool|string   $name 缓存连接标识 true 强制重新连接
     * @return Driver
     */
    public function connect(array $options = [], $name = false)
    {
        if (false === $name) {
            $name = md5(serialize($options));
        }
        if (true === $name || !isset($this->instance[$name])) {
            if (true === $name) {
                $name = md5(serialize($options));
            }
            $this->instance[$name] = new Redis($options);
        }
        return $this->instance[$name];
    }

    /**
     * 自动初始化缓存
     * @access public
     * @param  array         $options  配置数组
     * @param  bool          $force    强制更新
     * @return Driver
     */
    public function init(array $options = [], $force = false)
    {
        if (is_null($this->handler) || $force) {

            if ('complex' == $options['type']) {
                $default = $options['default'];
                $options = isset($options[$default['type']]) ? $options[$default['type']] : $default;
            }
            $this->handler = $this->connect($options);
        }
        return $this->handler;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 切换缓存类型 需要配置 type 为 complex
     * @access public
     * @param  string $name 缓存标识
     * @return Driver
     */
    public function store($name = '')
    {
        if ('' !== $name && 'complex' == $this->config['type']) {
            return $this->connect($this->config[$name], strtolower($name));
        }
        return $this->init();
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->init(), $method], $args);
    }
}

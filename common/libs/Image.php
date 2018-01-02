<?php
/**
 * Created by PhpStorm.
 * User: WuJunhua
 * Date: 2016/8/9
 * Time: 10:43
 *
 * 图像处理
 * 使用前提：已注入di服务中
 * 使用方法：
 *      $this->image->make(APP_PATH.'/static/assets/img/11.jpg')->resize(320, 240)->insert(APP_PATH.'/static/assets/img/watermark.png')->save(APP_PATH.'/static/assets/img/123.jpg');  //加水印
 *
 *      $this->image->make(APP_PATH.'/static/assets/img/11.jpg')->text('www.baiyangwang.com',320,120)->blur(50)->save(APP_PATH.'/static/assets/img/234.jpg');  //加文字、加高斯模糊滤镜
 *
 * 更多使用方法请查看：http://image.intervention.io/
 *
 */

namespace Shop\Libs;
require APP_PATH."/vendor/autoload.php";
use Closure;
use Shop\Libs\LibraryBase;

class Image extends LibraryBase
{
    /**
     * Config 默认是调用gd库  【可以改为imagick：'driver' => 'imagick'】
     *
     * @var array
     */
    public $config = array(
        'driver' => 'gd'
    );

    /**
     * Creates new instance of Image Manager
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->checkRequirements();
        $this->configure($config);
    }

    /**
     * Overrides configuration settings
     *
     * @param array $config
     */
    public function configure(array $config = array())
    {
        $this->config = array_replace($this->config, $config);
        return $this;
    }

    /**
     * Initiates an Image instance from different input types
     *
     * @param  mixed $data
     *
     * @return \Intervention\Image\Image
     */
    public function make($data)
    {
        return $this->createDriver()->init($data);
    }

    /**
     * Creates an empty image canvas
     *
     * @param  integer $width
     * @param  integer $height
     * @param  mixed $background
     *
     * @return \Intervention\Image\Image
     */
    public function canvas($width, $height, $background = null)
    {
        return $this->createDriver()->newImage($width, $height, $background);
    }

    /**
     * Create new cached image and run callback
     * (requires additional package intervention/imagecache)
     *
     * @param Closure $callback
     * @param integer $lifetime
     * @param boolean $returnObj
     *
     * @return Image
     */
    public function cache(Closure $callback, $lifetime = null, $returnObj = false)
    {
        if (class_exists('Intervention\\Image\\ImageCache')) {
            // create imagecache
            $imagecache = new ImageCache($this);

            // run callback
            if (is_callable($callback)) {
                $callback($imagecache);
            }

            return $imagecache->get($lifetime, $returnObj);
        }

        throw new \Intervention\Image\Exception\MissingDependencyException(
            "Please install package intervention/imagecache before running this function."
        );
    }

    /**
     * Creates a driver instance according to config settings
     *
     * @return \Intervention\Image\AbstractDriver
     */
    private function createDriver()
    {
        $drivername = ucfirst($this->config['driver']);
        $driverclass = sprintf('Intervention\\Image\\%s\\Driver', $drivername);

        if (class_exists($driverclass)) {
            return new $driverclass;
        }

        throw new \Intervention\Image\Exception\NotSupportedException(
            "Driver ({$drivername}) could not be instantiated."
        );
    }

    /**
     * Check if all requirements are available
     *
     * @return void
     */
    private function checkRequirements()
    {
        if ( ! function_exists('finfo_buffer')) {
            throw new \Intervention\Image\Exception\MissingDependencyException(
                "PHP Fileinfo extension must be installed/enabled to use Intervention Image."
            );
        }
    }

}
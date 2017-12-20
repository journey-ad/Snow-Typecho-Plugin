<?php
/**
 * 简单的，有丰富自定义项的页面下雪插件 
 * 
 * @package Snow
 * @author journey.ad
 * @version 1.0.0
 * @link https://imjad.cn
 */
class Snow_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->header = array('Snow_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('Snow_Plugin', 'footer');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
   
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
		$mobile = new Typecho_Widget_Helper_Form_Element_Radio('mobile', array('1'=> '是', '0'=> '否'), 1, _t('移动端是否加载'), _t('配置移动端是否加载，默认为是'));
        $form->addInput($mobile);
		$flakeCount = new Typecho_Widget_Helper_Form_Element_Text('flakeCount', NULL, '200', _t('雪花数量'), _t('雪花数量，数值越大雪花数量越多，默认200'));
        $form->addInput($flakeCount);
		$size = new Typecho_Widget_Helper_Form_Element_Text('size', NULL, '2', _t('雪花大小'), _t('雪花大小，为基准值，数值越大雪花越大，默认2'));
        $form->addInput($size);
		$minDist = new Typecho_Widget_Helper_Form_Element_Text('minDist', NULL, '150', _t('雪花距离'), _t('雪花距离鼠标指针的最小值，小于这个距离的雪花将受到鼠标的排斥，默认150'));
        $form->addInput($minDist);
		$speed = new Typecho_Widget_Helper_Form_Element_Text('speed', NULL, '0.5', _t('雪花速度'), _t('雪花速度，为基准值，数值越大雪花速度越快，默认0.5'));
        $form->addInput($speed);
		$stepSize = new Typecho_Widget_Helper_Form_Element_Text('stepSize', NULL, '1', _t('雪花横移'), _t('雪花横移幅度，为基准值，数值越大雪花横移幅度越大，0为竖直下落，默认1'));
        $form->addInput($stepSize);
		$snowcolor = new Typecho_Widget_Helper_Form_Element_Text('snowcolor', NULL, '#ffffff', _t('雪花颜色'), _t('请用十六进制表示，默认#ffffff'));
        $form->addInput($snowcolor);
		$opacity = new Typecho_Widget_Helper_Form_Element_Text('opacity', NULL, '0.3', _t('雪花透明度'), _t('为基准值，范围0~1，默认0.3'));
        $form->addInput($opacity);
        $bgcolor = new Typecho_Widget_Helper_Form_Element_Text('bgcolor', NULL, '#7d895f', _t('背景颜色'), _t('请用十六进制表示，不使用请留空，默认#7d895f'));
        $form->addInput($bgcolor);

}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 输出头部css
     * 
     * @access public
     * @return void
     */
    public static function header(){
        $options = Typecho_Widget::widget('Widget_Options')->plugin('Snow');
        $bgcolor = !empty($options->bgcolor) ? "background: ".self::hex2rgba($options->bgcolor, 0.1).";" : '';
        echo <<<EOF
<!-- Snow Start -->
<style>
    #Snow{
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 99999;
        {$bgcolor}
        pointer-events: none;
    }
</style>
<!-- Snow End -->
EOF;
    }
    /**
     * 输出底部
     * 
     * @access public
     * @return void
     */
    public static function footer(){
		$options = Typecho_Widget::widget('Widget_Options')->plugin('Snow');
		$mobile = $options->mobile ? 'true' : 'screen && screen.width > 768';
		$rgb = self::hex2rgba($options->snowcolor, false, true);
        echo <<<EOF
<!-- Snow Start -->
<canvas id="Snow"></canvas>
<script>
    if({$mobile}){
        (function() {
            var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame || window.msRequestAnimationFrame ||
            function(callback) {
                window.setTimeout(callback, 1000 / 60);
            };
            window.requestAnimationFrame = requestAnimationFrame;
        })();
        
        (function() {
            var flakes = [],
                canvas = document.getElementById("Snow"),
                ctx = canvas.getContext("2d"),
                flakeCount = {$options->flakeCount},
                mX = -100,
                mY = -100;
            
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            
            function snow() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            
                for (var i = 0; i < flakeCount; i++) {
                    var flake = flakes[i],
                        x = mX,
                        y = mY,
                        minDist = {$options->minDist},
                        x2 = flake.x,
                        y2 = flake.y;
            
                    var dist = Math.sqrt((x2 - x) * (x2 - x) + (y2 - y) * (y2 - y)),
                        dx = x2 - x,
                        dy = y2 - y;
            
                    if (dist < minDist) {
                        var force = minDist / (dist * dist),
                            xcomp = (x - x2) / dist,
                            ycomp = (y - y2) / dist,
                            deltaV = force / 2;
            
                        flake.velX -= deltaV * xcomp;
                        flake.velY -= deltaV * ycomp;
            
                    } else {
                        flake.velX *= .98;
                        if (flake.velY <= flake.speed) {
                            flake.velY = flake.speed
                        }
                        flake.velX += Math.cos(flake.step += .05) * flake.stepSize;
                    }
            
                    ctx.fillStyle = "rgba({$rgb[0]},{$rgb[1]},{$rgb[2]}," + flake.opacity + ")";
                    flake.y += flake.velY;
                    flake.x += flake.velX;
                        
                    if (flake.y >= canvas.height || flake.y <= 0) {
                        reset(flake);
                    }
            
                    if (flake.x >= canvas.width || flake.x <= 0) {
                        reset(flake);
                    }
            
                    ctx.beginPath();
                    ctx.arc(flake.x, flake.y, flake.size, 0, Math.PI * 2);
                    ctx.fill();
                }
                requestAnimationFrame(snow);
            };
            
            function reset(flake) {
                flake.x = Math.floor(Math.random() * canvas.width);
                flake.y = 0;
                flake.size = (Math.random() * 3) + {$options->size};
                flake.speed = (Math.random() * 1) + {$options->speed};
                flake.velY = flake.speed;
                flake.velX = 0;
                flake.opacity = (Math.random() * 0.5) + {$options->opacity};
            }
            
            function init() {
                for (var i = 0; i < flakeCount; i++) {
                    var x = Math.floor(Math.random() * canvas.width),
                        y = Math.floor(Math.random() * canvas.height),
                        size = (Math.random() * 3) + {$options->size},
                        speed = (Math.random() * 1) + {$options->speed},
                        opacity = (Math.random() * 0.5) + {$options->opacity};
            
                    flakes.push({
                        speed: speed,
                        velY: speed,
                        velX: 0,
                        x: x,
                        y: y,
                        size: size,
                        stepSize: (Math.random()) / 30 * {$options->stepSize},
                        step: 0,
                        angle: 180,
                        opacity: opacity
                    });
                }
            
                snow();
            };
            
            document.addEventListener("mousemove", function(e) {
                mX = e.clientX,
                mY = e.clientY
            });
            window.addEventListener("resize", function() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
            init();
        })();
    }
</script>
<!-- Snow End -->
EOF;
    }
    
    /**
     * 16进制颜色代码转换为rgba格式
     * 
     * @access private
     * @param string $color
     * @param string $opacity
     * @param boolean $raw
     * @return mixed
     */
    private static function hex2rgba($color, $opacity = false, $raw = false) {
    	$default = 'rgb(0,0,0)';
    	//Return default if no color provided
    	if(empty($color))
              return $default; 
    	//Sanitize $color if "#" is provided 
        if ($color[0] == '#' ) {
        	$color = substr( $color, 1 );
        }
        //Check if color has 6 or 3 characters and get values
        if (strlen($color) == 6) {
                $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
        } elseif ( strlen( $color ) == 3 ) {
                $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
        } else {
                return $default;
        }
 
        //Convert hexadec to rgb
        $rgb =  array_map('hexdec', $hex);
 
        if($raw){
            if($opacity){
            	if(abs($opacity) > 1) $opacity = 1.0;
            	array_push($rgb, $opacity);
            }
            $output = $rgb;
        }else{
            //Check if opacity is set(rgba or rgb)
            if($opacity){
            	if(abs($opacity) > 1)
            		$opacity = 1.0;
            	$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
            } else {
            	$output = 'rgb('.implode(",",$rgb).')';
            }
        }
 
        //Return rgb(a) color string
        return $output;
    }
}

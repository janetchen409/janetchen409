<?php
/**
 * Help to debug PHP code printed in the javascript console
 * PHP version 5.3
 * @category 	Library
 * @author   	Lenin Zapata <leninzapata.com>
 * @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @since 	    1.0		2019-07-31  Release
 * @since       1.1     2019-08-28  Minimal Improvements
 *
*/
if( ! class_exists('phpConsole') ){
final class phpConsole{

	/**
	 * Class constants
	 */
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';
    const SUCCESS   = 'success';
    const LOG       = 'log';
    const GROUP     = 'group';
    const GROUPEND  = 'groupEnd';

    public static
        /**
         * Project's name.
         *
         * @since   1.0     2019-07-31 12:43:00     Release
         * @var     string
         */
        $project_name = '',
        /**
         * TRUE = print the logs, FALSE = returns in a string variable
         *
         * @since   1.0     2019-07-31 14:32:53     Release
         * @var     bool
         * @access  public
         */
        $echo = true,
        /**
         * TRUE = Collapse all group, FALSE = expanded all group
         *
         * @since   1.0     2019-07-31 14:32:53     Release
         * @var     bool
         * @access  public
         */
        $collapsed = true;

    protected static
        /**
         * Array of conversion levels.
         *
         * @since   1.0     2019-07-31 12:43:47     Release
         * @var     array
         */
        $levels = [
            'emergency' => 0,
            'alert'     => 1,
            'critical'  => 2,
            'error'     => 3,
            'warning'   => 4,
            'notice'    => 5,
            'info'      => 6,
            'debug'     => 7,
            'log'       => 8,
            'success'   => 9,
            'group'     => 10,
            'groupEnd'  => 11,
        ],
        /**
         * Array of conversion levels in reverse order.
         *
         * @since   1.0     2019-07-31 12:43:47     Release
         * @var     array
         */
        $s_levels = [
            0 => 'emergency',
            1 => 'alert',
            2 => 'critical',
            3 => 'error',
            4 => 'warning',
            5 => 'notice',
            6 => 'info',
            7 => 'debug',
            8 => '',
            9 => 'success',
            10 => 'group',
            11 => 'groupEnd',
        ],
        /**
         * Instance of the current class
         *
         * @since   1.0     2019-07-31 12:43:47     Release
         * @var phpConsole
         */
        $instance;
        /**
         * Store the logs.
         */
    private static
        $log = [],
        /**
         * Activa/disabled
         *
         * @since   1.0     2019-07-31 12:43:47     Release
         * @var     bool
         */
        $activate = false;


	/**
     * Gets instance of this class
     *
     * @since   1.0     2019-07-31 13:12:51     Release
     * @return  phpConsole
     */
    public static function instance() {
		if ( ! isset( static::$instance ) || !static::$instance ) {
			static::$instance = new static();
		}
		return static::$instance;
    }

	/**
     * Declare a new project
     *
     * @since   1.0     2019-07-31 13:13:11     Release
     * @return  phpConsole
     */
    public static function new( $name = '', $active = false ) {
		self::$activate = $active;
		self::$log = []; // reset
		self::$project_name = $name;
    }

	/**
     * System is unusable.
     *
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return  null
     */
    public static function emergency($message, $context = null){
        self::_log(self::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public static function alert($message, $context = null){
        self::_log(self::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return null
     */
    public static function critical($message, $context = null){
        self::_log(self::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     *
     * @return  null
     */
    public static function error($message, $context = null){
        self::_log(self::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return null
     */
    public static function warning($message, $context = null){
        self::_log(self::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return null
     */
    public static function notice($message, $context = null){
        self::_log(self::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return null
     */
    public static function info($message, $context = null){
        self::_log(self::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return null
     */
    public static function debug($message, $context = null){
        self::_log(self::DEBUG, $message, $context);
	}

	/**
     * Detailed debug success.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return null
     */
    public static function success($message, $context = null){
        self::_log(self::SUCCESS, $message, $context);
	}

	/**
     * Detailed log information.
	 * This is a line without label
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return null
     */
    public static function log($message, $context = null){
        self::_log(self::LOG, $message, $context);
	}

	/**
     * Open a group in the console
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return  null
     */
    public static function group($message, $context = null){
        self::_log(self::GROUP, $message);
	}

	/**
     * Close a group in the console
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   string  $message
     * @param   array   $context
     * @return  null
     */
    public static function groupEnd(){
        self::_log(self::GROUPEND, $message = '');
    }

	/**
     * Log.
     *
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param $level Error level (string or PHP syslog priority)
     * @param $message Error message
     * @param $context Contextual array
     * @return void
     */
    public static function _log($level, $message, $context = null){

        // If you are not active, do not register anything
        if( self::$activate == false ) return;

        if (is_string($level)) {
            if (!array_key_exists($level, self::$levels)) {
                throw new \Exception("Log level {$level} is not valid. Please use syslog levels instead", 500);
            } else {
                $level = self::$levels[$level];
            }
		}
        if (is_array($context) && array_key_exists('exception', $context)) {
            if ($context['exception'] instanceof \Exception) {
                $exc = $context['exception'];
                $message .= " Exception: {$exc->getMessage()}";
                unset($context['exception']);
            } else {
                unset($context['exception']);
            }
        }

        return self::interpolate($message, $context, $level);
	}

	/**
     * Interpolate string with parameters.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   $string     String with parameters
     * @param   $params     Parameter arrays
     * @param   $level      Level of log
     * @return void
     */
    public static function interpolate($string, $params, $level){
		if( is_array($params) ){
			if( self::isAssoc( $params ) ){
				foreach ($params as $placeholder => $value) {
					$params['{'.(string) $placeholder.'}'] = (string) $value;
					unset($params[$placeholder]);
				}
				$message = strtr($string, $params);
			}else{
				$message = $string . ' : ' . json_encode($params) ;
			}
		}elseif( is_string( $params ) || is_numeric( $params ) ) {
			if( strpos($string, "{value}") !== false ){
				$string = str_replace("{value}","%s",$string);
				$message = sprintf($string, $params);
			}elseif( !empty($params) ){
				$message = "{$string}:{$params}";
			}else{
				$message = $string;
			}
		}else{
			$message = $string;
		}

		$backtrace = debug_backtrace(false);
		$backtrace_message = 'unknown';
		$backtrace_line = 'unknown';
        if (isset($backtrace[2]['file']) && isset($backtrace[2]['line'])) {
			$backtrace_message = 'Line ' . $backtrace[2]['line'] . ' in ' . $backtrace[2]['file'];
        }
        self::store([
            'message'     => (($message)),
            'level'       => $level,
            'level_label' => ucwords(self::$s_levels[$level]),
            'backtrace'   => $backtrace_message,
        ]);
	}

	/**
	 * Check if an array is associative
     * @since   1.0     2019-07-31 13:14:01     Release
	 *
	 * @param 	array 	$array
	 * @return 	boolean
	 */
	public static function isAssoc(array $array){
		$keys = array_keys($array);
		return array_keys($keys) !== $keys;
	}

	/**
     * Store the log.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   array   $log                    Array logs
     * @return  array
     */
    public static function store($log){
        self::$log[] = $log;
        return static::$instance;
	}

	/**
     * Get the log message.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @return  array
     */
    public static function get(){
		return [
			'name' => self::$project_name,
			'logs' => self::$log
		];
	}

	/**
     * Get the log message in json encode.
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @return array
     */
    public static function get_json(){
        return json_encode(self::get());
	}

	/**
     * Get the log message print raw form javascript
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @return array
     */
    public static function print_console_raw(){
        if( ! empty( self::$log ) ){
			$arr = self::get();
			$obj = (object)$arr;
			echo "<script>console.log( '$obj->name ' , ".json_encode( $obj )." );</script>";
		}
	}

	/**
     * Get the log message print form to console javascript
     * @since   1.0     2019-07-31 13:14:01     Release
     *
     * @param   bool    $echo                   Indicates if you carry out the process or not
     * @return  array
     */
    public static function print_console(){

        // If you are not active, do not register anything
        if( self::$activate == false ) return;

        // If is grupo collased/expanded
        $collapse = '';
        if( self::$collapsed ){
            $collapse = "Collapsed";
        }

		// Colors
		$colors = '
		function log(level) {
			var css = "",
			paint = { // default colors
				clr: "#212121",
				bgc: "#b0bec5"
			},
			colors = {
				error: {clr:"#ffebee", bgc:"#c62828"}, // red
				emergency: {clr:"#fff", bgc:"#E7526F"}, // other
				alert: {clr:"#fff", bgc:"#E79B29"}, // other
				notice: {clr:"#fff", bgc:"#3B5BDB"}, // other
				success: {clr: "#e8f5e9", bgc: "#2e7d32"}, // green
				warning: {clr: "#fff3e0", bgc: "#f4511e"}, // orange
				info: {clr: "#ede7f6", bgc: "#651fff"} // purple
			};

			// overriting default colors if color given
			if (colors.hasOwnProperty(level)){ paint.clr = colors[level].clr; paint.bgc = colors[level].bgc; }
			css = "color:" + paint.clr + ";font-weight:bold; background-color: " + paint.bgc + "; padding: 3px 12px; border-radius: 2px; margin-right:5px;";
			return css;
		}';

		$js = '';
        if( ! empty( self::$log ) ){
			$js .= "<script>$colors";
			$obj = (object)self::get();
			if( ! empty( $obj->logs ) ){

				$js .= "console.group{$collapse}( '$obj->name' );";
				foreach($obj->logs as $value){

					$value          = (object)$value;
					$level          = $value->level;
					$value->message = addslashes(trim(preg_replace( "/\r|\n/", "", ($value->message) )));

					// check if it is a group
					if( ! in_array( $level, [10,11] ) ){
						$value->backtrace = str_replace("\\","\\\\" , $value->backtrace);
						$label = ! empty( $value->level_label ) ? '%c'. $value->level_label  : '';
						$backtrace = ''.$value->backtrace; //not empty( $label ) ? ' %c'.$value->backtrace : '';
                        $css = ! empty( $label ) ? ', log("'.(strtolower($value->level_label)).'"),"color: black;font-weight:normal;background-color:transparent;","font-style: italic;color: #C1C1C1;"' 
                        : ',"color: black;font-weight:normal;background-color:transparent;","font-style: italic;color: #C1C1C1;"';
						$js .= "console.log( '{$label}%c{$value->message} %c{$backtrace}'{$css} );";
					}

					// open a group
					else if( $level == 10 ){
						$js .= "console.group{$collapse}('%c{$value->message}','color:#5C5C5C;');";
					}
					// close a group
					else if( $level == 11 ){
						$js .= "console.groupEnd();";
					}
				}
				$js .= "console.groupEnd();";
			}
			$js .= "</script>";
		}
		if( self::$echo ){
			echo $js;
		}else{
			return $js;
		}
	}

}} ?>
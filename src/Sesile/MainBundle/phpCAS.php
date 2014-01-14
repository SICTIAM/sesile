<?php
/**
 * Created by PhpStorm.
 * User: j.mercier
 * Date: 23/12/13
 * Time: 12:13
 */

namespace Sesile\MainBundle;


class phpCAS
{

    /**
     * This variable is used by the interface class phpCAS.
     *
     * @hideinitializer
     */
    private static $_PHPCAS_CLIENT;

    /**
     * This variable is used to store where the initializer is called from
     * (to print a comprehensive error in case of multiple calls).
     *
     * @hideinitializer
     */
    private static $_PHPCAS_INIT_CALL;

    /**
     * This variable is used to store phpCAS debug mode.
     *
     * @hideinitializer
     */
    private static $_PHPCAS_DEBUG;


    // ########################################################################
    //  INITIALIZATION
    // ########################################################################

    /**
     * @addtogroup publicInit
     * @{
     */

    /**
     * phpCAS client initializer.
     *
     * @param string $server_version the version of the CAS server
     * @param string $server_hostname the hostname of the CAS server
     * @param string $server_port the port the CAS server is running on
     * @param string $server_uri the URI the CAS server is responding on
     * @param bool $changeSessionID Allow phpCAS to change the session_id (Single
     * Sign Out/handleLogoutRequests is based on that change)
     *
     * @return a newly created CAS_Client object
     * @note Only one of the phpCAS::client() and phpCAS::proxy functions should be
     * called, only once, and before all other methods (except phpCAS::getVersion()
     * and phpCAS::setDebug()).
     */
    public static function client($server_version, $server_hostname,
                                  $server_port, $server_uri, $changeSessionID = true
    )
    {
        phpCAS :: traceBegin();
        if (is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error(self::$_PHPCAS_INIT_CALL['method'] . '() has already been called (at ' . self::$_PHPCAS_INIT_CALL['file'] . ':' . self::$_PHPCAS_INIT_CALL['line'] . ')');
        }
        if (gettype($server_version) != 'string') {
            phpCAS :: error('type mismatched for parameter $server_version (should be `string\')');
        }
        if (gettype($server_hostname) != 'string') {
            phpCAS :: error('type mismatched for parameter $server_hostname (should be `string\')');
        }
        if (gettype($server_port) != 'integer') {
            phpCAS :: error('type mismatched for parameter $server_port (should be `integer\')');
        }
        if (gettype($server_uri) != 'string') {
            phpCAS :: error('type mismatched for parameter $server_uri (should be `string\')');
        }

        // store where the initializer is called from
        $dbg = debug_backtrace();
        self::$_PHPCAS_INIT_CALL = array(
            'done' => true,
            'file' => $dbg[0]['file'],
            'line' => $dbg[0]['line'],
            'method' => __CLASS__ . '::' . __FUNCTION__
        );

        // initialize the object $_PHPCAS_CLIENT
        self::$_PHPCAS_CLIENT = new CAS_Client(
            $server_version, false, $server_hostname, $server_port, $server_uri,
            $changeSessionID
        );
        phpCAS :: traceEnd();
    }

    /**
     * phpCAS proxy initializer.
     *
     * @param string $server_version the version of the CAS server
     * @param string $server_hostname the hostname of the CAS server
     * @param string $server_port the port the CAS server is running on
     * @param string $server_uri the URI the CAS server is responding on
     * @param bool $changeSessionID Allow phpCAS to change the session_id (Single
     * Sign Out/handleLogoutRequests is based on that change)
     *
     * @return a newly created CAS_Client object
     * @note Only one of the phpCAS::client() and phpCAS::proxy functions should be
     * called, only once, and before all other methods (except phpCAS::getVersion()
     * and phpCAS::setDebug()).
     */
    public static function proxy($server_version, $server_hostname,
                                 $server_port, $server_uri, $changeSessionID = true
    )
    {
        phpCAS :: traceBegin();
        if (is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error(self::$_PHPCAS_INIT_CALL['method'] . '() has already been called (at ' . self::$_PHPCAS_INIT_CALL['file'] . ':' . self::$_PHPCAS_INIT_CALL['line'] . ')');
        }
        if (gettype($server_version) != 'string') {
            phpCAS :: error('type mismatched for parameter $server_version (should be `string\')');
        }
        if (gettype($server_hostname) != 'string') {
            phpCAS :: error('type mismatched for parameter $server_hostname (should be `string\')');
        }
        if (gettype($server_port) != 'integer') {
            phpCAS :: error('type mismatched for parameter $server_port (should be `integer\')');
        }
        if (gettype($server_uri) != 'string') {
            phpCAS :: error('type mismatched for parameter $server_uri (should be `string\')');
        }

        // store where the initialzer is called from
        $dbg = debug_backtrace();
        self::$_PHPCAS_INIT_CALL = array(
            'done' => true,
            'file' => $dbg[0]['file'],
            'line' => $dbg[0]['line'],
            'method' => __CLASS__ . '::' . __FUNCTION__
        );

        // initialize the object $_PHPCAS_CLIENT
        self::$_PHPCAS_CLIENT = new CAS_Client(
            $server_version, true, $server_hostname, $server_port, $server_uri,
            $changeSessionID
        );
        phpCAS :: traceEnd();
    }

    /** @} */
    // ########################################################################
    //  DEBUGGING
    // ########################################################################

    /**
     * @addtogroup publicDebug
     * @{
     */

    /**
     * Set/unset debug mode
     *
     * @param string $filename the name of the file used for logging, or false
     * to stop debugging.
     *
     * @return void
     */
    public static function setDebug($filename = '')
    {
        if ($filename != false && gettype($filename) != 'string') {
            phpCAS :: error('type mismatched for parameter $dbg (should be false or the name of the log file)');
        }
        if ($filename === false) {
            self::$_PHPCAS_DEBUG['filename'] = false;

        } else {
            if (empty ($filename)) {
                if (preg_match('/^Win.*/', getenv('OS'))) {
                    if (isset ($_ENV['TMP'])) {
                        $debugDir = $_ENV['TMP'] . '/';
                    } else {
                        $debugDir = '';
                    }
                } else {
                    $debugDir = DEFAULT_DEBUG_DIR;
                }
                $filename = $debugDir . 'phpCAS.log';
            }

            if (empty (self::$_PHPCAS_DEBUG['unique_id'])) {
                self::$_PHPCAS_DEBUG['unique_id'] = substr(strtoupper(md5(uniqid(''))), 0, 4);
            }

            self::$_PHPCAS_DEBUG['filename'] = $filename;
            self::$_PHPCAS_DEBUG['indent'] = 0;

            phpCAS :: trace('START phpCAS-' . PHPCAS_VERSION . ' ******************');
        }
    }


    /**
     * Logs a string in debug mode.
     *
     * @param string $str the string to write
     *
     * @return void
     * @private
     */
    public static function log($str)
    {
        $indent_str = ".";


        if (!empty(self::$_PHPCAS_DEBUG['filename'])) {
            // Check if file exists and modifiy file permissions to be only
            // readable by the webserver
            if (!file_exists(self::$_PHPCAS_DEBUG['filename'])) {
                touch(self::$_PHPCAS_DEBUG['filename']);
                // Chmod will fail on windows
                @chmod(self::$_PHPCAS_DEBUG['filename'], 0600);
            }
            for ($i = 0; $i < self::$_PHPCAS_DEBUG['indent']; $i++) {

                $indent_str .= '|    ';
            }
            // allow for multiline output with proper identing. Usefull for
            // dumping cas answers etc.
            $str2 = str_replace("\n", "\n" . self::$_PHPCAS_DEBUG['unique_id'] . ' ' . $indent_str, $str);
            error_log(self::$_PHPCAS_DEBUG['unique_id'] . ' ' . $indent_str . $str2 . "\n", 3, self::$_PHPCAS_DEBUG['filename']);
        }

    }

    /**
     * This method is used by interface methods to print an error and where the
     * function was originally called from.
     *
     * @param string $msg the message to print
     *
     * @return void
     * @private
     */
    public static function error($msg)
    {
        $dbg = debug_backtrace();
        $function = '?';
        $file = '?';
        $line = '?';
        if (is_array($dbg)) {
            for ($i = 1; $i < sizeof($dbg); $i++) {
                if (is_array($dbg[$i]) && isset($dbg[$i]['class'])) {
                    if ($dbg[$i]['class'] == __CLASS__) {
                        $function = $dbg[$i]['function'];
                        $file = $dbg[$i]['file'];
                        $line = $dbg[$i]['line'];
                    }
                }
            }
        }
        echo "<br />\n<b>phpCAS error</b>: <font color=\"FF0000\"><b>" . __CLASS__ . "::" . $function . '(): ' . htmlentities($msg) . "</b></font> in <b>" . $file . "</b> on line <b>" . $line . "</b><br />\n";
        phpCAS :: trace($msg);
        phpCAS :: traceEnd();

        throw new CAS_GracefullTerminationException(__CLASS__ . "::" . $function . '(): ' . $msg);
    }

    /**
     * This method is used to log something in debug mode.
     *
     * @param string $str string to log
     *
     * @return void
     */
    public static function trace($str)
    {
        $dbg = debug_backtrace();
        phpCAS :: log($str . ' [' . basename($dbg[0]['file']) . ':' . $dbg[0]['line'] . ']');
    }

    /**
     * This method is used to indicate the start of the execution of a function in debug mode.
     *
     * @return void
     */
    public static function traceBegin()
    {
        $dbg = debug_backtrace();
        $str = '=> ';
        if (!empty ($dbg[1]['class'])) {
            $str .= $dbg[1]['class'] . '::';
        }
        $str .= $dbg[1]['function'] . '(';
        if (is_array($dbg[1]['args'])) {
            foreach ($dbg[1]['args'] as $index => $arg) {
                if ($index != 0) {
                    $str .= ', ';
                }
                if (is_object($arg)) {
                    $str .= get_class($arg);
                } else {
                    $str .= str_replace(array("\r\n", "\n", "\r"), "", var_export($arg, true));
                }
            }
        }
        if (isset($dbg[1]['file'])) {
            $file = basename($dbg[1]['file']);
        } else {
            $file = 'unknown_file';
        }
        if (isset($dbg[1]['line'])) {
            $line = $dbg[1]['line'];
        } else {
            $line = 'unknown_line';
        }
        $str .= ') [' . $file . ':' . $line . ']';
        phpCAS :: log($str);
        if (!isset(self::$_PHPCAS_DEBUG['indent'])) {
            self::$_PHPCAS_DEBUG['indent'] = 0;
        } else {
            self::$_PHPCAS_DEBUG['indent']++;
        }
    }

    /**
     * This method is used to indicate the end of the execution of a function in
     * debug mode.
     *
     * @param string $res the result of the function
     *
     * @return void
     */
    public static function traceEnd($res = '')
    {
        if (empty(self::$_PHPCAS_DEBUG['indent'])) {
            self::$_PHPCAS_DEBUG['indent'] = 0;
        } else {
            self::$_PHPCAS_DEBUG['indent']--;
        }
        $dbg = debug_backtrace();
        $str = '';
        if (is_object($res)) {
            $str .= '<= ' . get_class($res);
        } else {
            $str .= '<= ' . str_replace(array("\r\n", "\n", "\r"), "", var_export($res, true));
        }

        phpCAS :: log($str);
    }

    /**
     * This method is used to indicate the end of the execution of the program
     *
     * @return void
     */
    public static function traceExit()
    {
        phpCAS :: log('exit()');
        while (self::$_PHPCAS_DEBUG['indent'] > 0) {
            phpCAS :: log('-');
            self::$_PHPCAS_DEBUG['indent']--;
        }
    }

    /** @} */
    // ########################################################################
    //  INTERNATIONALIZATION
    // ########################################################################
    /**
     * @addtogroup publicLang
     * @{
     */

    /**
     * This method is used to set the language used by phpCAS.
     *
     * @param string $lang string representing the language.
     *
     * @return void
     *
     * @sa PHPCAS_LANG_FRENCH, PHPCAS_LANG_ENGLISH
     * @note Can be called only once.
     */
    public static function setLang($lang)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (gettype($lang) != 'string') {
            phpCAS :: error('type mismatched for parameter $lang (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setLang($lang);
    }

    /** @} */
    // ########################################################################
    //  VERSION
    // ########################################################################
    /**
     * @addtogroup public
     * @{
     */

    /**
     * This method returns the phpCAS version.
     *
     * @return the phpCAS version.
     */
    public static function getVersion()
    {
        return PHPCAS_VERSION;
    }

    /** @} */
    // ########################################################################
    //  HTML OUTPUT
    // ########################################################################
    /**
     * @addtogroup publicOutput
     * @{
     */

    /**
     * This method sets the HTML header used for all outputs.
     *
     * @param string $header the HTML header.
     *
     * @return void
     */
    public static function setHTMLHeader($header)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (gettype($header) != 'string') {
            phpCAS :: error('type mismatched for parameter $header (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setHTMLHeader($header);
    }

    /**
     * This method sets the HTML footer used for all outputs.
     *
     * @param string $footer the HTML footer.
     *
     * @return void
     */
    public static function setHTMLFooter($footer)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (gettype($footer) != 'string') {
            phpCAS :: error('type mismatched for parameter $footer (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setHTMLFooter($footer);
    }

    /** @} */
    // ########################################################################
    //  PGT STORAGE
    // ########################################################################
    /**
     * @addtogroup publicPGTStorage
     * @{
     */

    /**
     * This method can be used to set a custom PGT storage object.
     *
     * @param CAS_PGTStorage $storage a PGT storage object that inherits from the
     * CAS_PGTStorage class
     *
     * @return void
     */
    public static function setPGTStorage($storage)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called before ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() (called at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ')');
        }
        if (!($storage instanceof CAS_PGTStorage)) {
            phpCAS :: error('type mismatched for parameter $storage (should be a CAS_PGTStorage `object\')');
        }
        self::$_PHPCAS_CLIENT->setPGTStorage($storage);
        phpCAS :: traceEnd();
    }

    /**
     * This method is used to tell phpCAS to store the response of the
     * CAS server to PGT requests in a database.
     *
     * @param string $dsn_or_pdo a dsn string to use for creating a PDO
     * object or a PDO object
     * @param string $username the username to use when connecting to the
     * database
     * @param string $password the password to use when connecting to the
     * database
     * @param string $table the table to use for storing and retrieving
     * PGT's
     * @param string $driver_options any driver options to use when connecting
     * to the database
     *
     * @return void
     */
    public static function setPGTStorageDb($dsn_or_pdo, $username = '',
                                           $password = '', $table = '', $driver_options = null
    )
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called before ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() (called at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ')');
        }
        if (gettype($username) != 'string') {
            phpCAS :: error('type mismatched for parameter $username (should be `string\')');
        }
        if (gettype($password) != 'string') {
            phpCAS :: error('type mismatched for parameter $password (should be `string\')');
        }
        if (gettype($table) != 'string') {
            phpCAS :: error('type mismatched for parameter $table (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setPGTStorageDb($dsn_or_pdo, $username, $password, $table, $driver_options);
        phpCAS :: traceEnd();
    }

    /**
     * This method is used to tell phpCAS to store the response of the
     * CAS server to PGT requests onto the filesystem.
     *
     * @param string $path the path where the PGT's should be stored
     *
     * @return void
     */
    public static function setPGTStorageFile($path = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called before ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() (called at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ')');
        }
        if (gettype($path) != 'string') {
            phpCAS :: error('type mismatched for parameter $path (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setPGTStorageFile($path);
        phpCAS :: traceEnd();
    }

    /** @} */
    // ########################################################################
    // ACCESS TO EXTERNAL SERVICES
    // ########################################################################
    /**
     * @addtogroup publicServices
     * @{
     */

    /**
     * Answer a proxy-authenticated service handler.
     *
     * @param string $type The service type. One of
     * PHPCAS_PROXIED_SERVICE_HTTP_GET; PHPCAS_PROXIED_SERVICE_HTTP_POST;
     * PHPCAS_PROXIED_SERVICE_IMAP
     *
     * @return CAS_ProxiedService
     * @throws InvalidArgumentException If the service type is unknown.
     */
    public static function getProxiedService($type)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after the programmer is sure the user has been authenticated (by calling ' . __CLASS__ . '::checkAuthentication() or ' . __CLASS__ . '::forceAuthentication()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        if (gettype($type) != 'string') {
            phpCAS :: error('type mismatched for parameter $type (should be `string\')');
        }

        $res = self::$_PHPCAS_CLIENT->getProxiedService($type);

        phpCAS :: traceEnd();
        return $res;
    }

    /**
     * Initialize a proxied-service handler with the proxy-ticket it should use.
     *
     * @param CAS_ProxiedService $proxiedService Proxied Service Handler
     *
     * @return void
     * @throws CAS_ProxyTicketException If there is a proxy-ticket failure.
     *        The code of the Exception will be one of:
     *            PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE
     *            PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE
     *            PHPCAS_SERVICE_PT_FAILURE
     */
    public static function initializeProxiedService(CAS_ProxiedService $proxiedService)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after the programmer is sure the user has been authenticated (by calling ' . __CLASS__ . '::checkAuthentication() or ' . __CLASS__ . '::forceAuthentication()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }

        self::$_PHPCAS_CLIENT->initializeProxiedService($proxiedService);
    }

    /**
     * This method is used to access an HTTP[S] service.
     *
     * @param string $url the service to access.
     * @param string &$err_code an error code Possible values are
     * PHPCAS_SERVICE_OK (on success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
     * PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE, PHPCAS_SERVICE_PT_FAILURE,
     * PHPCAS_SERVICE_NOT_AVAILABLE.
     * @param string &$output the output of the service (also used to give an
     * error message on failure).
     *
     * @return bool true on success, false otherwise (in this later case,
     * $err_code gives the reason why it failed and $output contains an error
     * message).
     */
    public static function serviceWeb($url, & $err_code, & $output)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after the programmer is sure the user has been authenticated (by calling ' . __CLASS__ . '::checkAuthentication() or ' . __CLASS__ . '::forceAuthentication()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }

        $res = self::$_PHPCAS_CLIENT->serviceWeb($url, $err_code, $output);

        phpCAS :: traceEnd($res);
        return $res;
    }

    /**
     * This method is used to access an IMAP/POP3/NNTP service.
     *
     * @param string $url a string giving the URL of the service,
     * including the mailing box for IMAP URLs, as accepted by imap_open().
     * @param string $service a string giving for CAS retrieve Proxy ticket
     * @param string $flags options given to imap_open().
     * @param string &$err_code an error code Possible values are
     * PHPCAS_SERVICE_OK (on success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
     * PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE, PHPCAS_SERVICE_PT_FAILURE,
     * PHPCAS_SERVICE_NOT_AVAILABLE.
     * @param string &$err_msg an error message on failure
     * @param string &$pt the Proxy Ticket (PT) retrieved from the CAS
     * server to access the URL on success, false on error).
     *
     * @return object IMAP stream on success, false otherwise (in this later
     * case, $err_code gives the reason why it failed and $err_msg contains an
     * error message).
     */
    public static function serviceMail($url, $service, $flags, & $err_code, & $err_msg, & $pt)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after the programmer is sure the user has been authenticated (by calling ' . __CLASS__ . '::checkAuthentication() or ' . __CLASS__ . '::forceAuthentication()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }

        if (gettype($flags) != 'integer') {
            phpCAS :: error('type mismatched for parameter $flags (should be `integer\')');
        }

        $res = self::$_PHPCAS_CLIENT->serviceMail($url, $service, $flags, $err_code, $err_msg, $pt);

        phpCAS :: traceEnd($res);
        return $res;
    }

    /** @} */
    // ########################################################################
    //  AUTHENTICATION
    // ########################################################################
    /**
     * @addtogroup publicAuth
     * @{
     */

    /**
     * Set the times authentication will be cached before really accessing the
     * CAS server in gateway mode:
     * - -1: check only once, and then never again (until you pree login)
     * - 0: always check
     * - n: check every "n" time
     *
     * @param int $n an integer.
     *
     * @return void
     */
    public static function setCacheTimesForAuthRecheck($n)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (gettype($n) != 'integer') {
            phpCAS :: error('type mismatched for parameter $n (should be `integer\')');
        }
        self::$_PHPCAS_CLIENT->setCacheTimesForAuthRecheck($n);
    }

    /**
     * Set a callback function to be run when a user authenticates.
     *
     * The callback function will be passed a $logoutTicket as its first
     * parameter, followed by any $additionalArgs you pass. The $logoutTicket
     * parameter is an opaque string that can be used to map the session-id to
     * logout request in order to support single-signout in applications that
     * manage their own sessions (rather than letting phpCAS start the session).
     *
     * phpCAS::forceAuthentication() will always exit and forward client unless
     * they are already authenticated. To perform an action at the moment the user
     * logs in (such as registering an account, performing logging, etc), register
     * a callback function here.
     *
     * @param string $function Callback function
     * @param array $additionalArgs optional array of arguments
     *
     * @return void
     */
    public static function setPostAuthenticateCallback($function, array $additionalArgs = array())
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }

        self::$_PHPCAS_CLIENT->setPostAuthenticateCallback($function, $additionalArgs);
    }

    /**
     * Set a callback function to be run when a single-signout request is
     * received. The callback function will be passed a $logoutTicket as its
     * first parameter, followed by any $additionalArgs you pass. The
     * $logoutTicket parameter is an opaque string that can be used to map a
     * session-id to the logout request in order to support single-signout in
     * applications that manage their own sessions (rather than letting phpCAS
     * start and destroy the session).
     *
     * @param string $function Callback function
     * @param array $additionalArgs optional array of arguments
     *
     * @return void
     */
    public static function setSingleSignoutCallback($function, array $additionalArgs = array())
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }

        self::$_PHPCAS_CLIENT->setSingleSignoutCallback($function, $additionalArgs);
    }

    /**
     * This method is called to check if the user is already authenticated
     * locally or has a global cas session. A already existing cas session is
     * determined by a cas gateway call.(cas login call without any interactive
     * prompt)
     *
     * @return true when the user is authenticated, false when a previous
     * gateway login failed or the function will not return if the user is
     * redirected to the cas server for a gateway login attempt
     */
    public static function checkAuthentication()
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }

        $auth = self::$_PHPCAS_CLIENT->checkAuthentication();

        // store where the authentication has been checked and the result
        self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        phpCAS :: traceEnd($auth);
        return $auth;
    }

    /**
     * This method is called to force authentication if the user was not already
     * authenticated. If the user is not authenticated, halt by redirecting to
     * the CAS server.
     *
     * @return bool Authentication
     */
    public static function forceAuthentication()
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }

        $auth = self::$_PHPCAS_CLIENT->forceAuthentication();

        // store where the authentication has been checked and the result
        self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        /*		if (!$auth) {
         phpCAS :: trace('user is not authenticated, redirecting to the CAS server');
        self::$_PHPCAS_CLIENT->forceAuthentication();
        } else {
        phpCAS :: trace('no need to authenticate (user `' . phpCAS :: getUser() . '\' is already authenticated)');
        }*/

        phpCAS :: traceEnd();
        return $auth;
    }

    /**
     * This method is called to renew the authentication.
     *
     * @return void
     **/
    public static function renewAuthentication()
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        $auth = self::$_PHPCAS_CLIENT->renewAuthentication();

        // store where the authentication has been checked and the result
        self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        //self::$_PHPCAS_CLIENT->renewAuthentication();
        phpCAS :: traceEnd();
    }

    /**
     * This method is called to check if the user is authenticated (previously or by
     * tickets given in the URL).
     *
     * @return true when the user is authenticated.
     */
    public static function isAuthenticated()
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }

        // call the isAuthenticated method of the $_PHPCAS_CLIENT object
        $auth = self::$_PHPCAS_CLIENT->isAuthenticated();

        // store where the authentication has been checked and the result
        self::$_PHPCAS_CLIENT->markAuthenticationCall($auth);

        phpCAS :: traceEnd($auth);
        return $auth;
    }

    /**
     * Checks whether authenticated based on $_SESSION. Useful to avoid
     * server calls.
     *
     * @return bool true if authenticated, false otherwise.
     * @since 0.4.22 by Brendan Arnold
     */
    public static function isSessionAuthenticated()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        return (self::$_PHPCAS_CLIENT->isSessionAuthenticated());
    }

    /**
     * This method returns the CAS user's login name.
     *
     * @return string the login name of the authenticated user
     * @warning should not be called only after phpCAS::forceAuthentication()
     * or phpCAS::checkAuthentication().
     * */
    public static function getUser()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::forceAuthentication() or ' . __CLASS__ . '::isAuthenticated()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        return self::$_PHPCAS_CLIENT->getUser();
    }

    /**
     * Answer attributes about the authenticated user.
     *
     * @warning should not be called only after phpCAS::forceAuthentication()
     * or phpCAS::checkAuthentication().
     *
     * @return array
     */
    public static function getAttributes()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::forceAuthentication() or ' . __CLASS__ . '::isAuthenticated()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        return self::$_PHPCAS_CLIENT->getAttributes();
    }

    /**
     * Answer true if there are attributes for the authenticated user.
     *
     * @warning should not be called only after phpCAS::forceAuthentication()
     * or phpCAS::checkAuthentication().
     *
     * @return bool
     */
    public static function hasAttributes()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::forceAuthentication() or ' . __CLASS__ . '::isAuthenticated()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        return self::$_PHPCAS_CLIENT->hasAttributes();
    }

    /**
     * Answer true if an attribute exists for the authenticated user.
     *
     * @param string $key attribute name
     *
     * @return bool
     * @warning should not be called only after phpCAS::forceAuthentication()
     * or phpCAS::checkAuthentication().
     */
    public static function hasAttribute($key)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::forceAuthentication() or ' . __CLASS__ . '::isAuthenticated()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        return self::$_PHPCAS_CLIENT->hasAttribute($key);
    }

    /**
     * Answer an attribute for the authenticated user.
     *
     * @param string $key attribute name
     *
     * @return mixed string for a single value or an array if multiple values exist.
     * @warning should not be called only after phpCAS::forceAuthentication()
     * or phpCAS::checkAuthentication().
     */
    public static function getAttribute($key)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCalled()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::forceAuthentication() or ' . __CLASS__ . '::isAuthenticated()');
        }
        if (!self::$_PHPCAS_CLIENT->wasAuthenticationCallSuccessful()) {
            phpCAS :: error('authentication was checked (by ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerMethod() . '() at ' . self::$_PHPCAS_CLIENT->getAuthenticationCallerFile() . ':' . self::$_PHPCAS_CLIENT->getAuthenticationCallerLine() . ') but the method returned false');
        }
        return self::$_PHPCAS_CLIENT->getAttribute($key);
    }

    /**
     * Handle logout requests.
     *
     * @param bool $check_client additional safety check
     * @param array $allowed_clients array of allowed clients
     *
     * @return void
     */
    public static function handleLogoutRequests($check_client = true, $allowed_clients = false)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        return (self::$_PHPCAS_CLIENT->handleLogoutRequests($check_client, $allowed_clients));
    }

    /**
     * This method returns the URL to be used to login.
     * or phpCAS::isAuthenticated().
     *
     * @return the login name of the authenticated user
     */
    public static function getServerLoginURL()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        return self::$_PHPCAS_CLIENT->getServerLoginURL();
    }

    /**
     * Set the login URL of the CAS server.
     *
     * @param string $url the login URL
     *
     * @return void
     * @since 0.4.21 by Wyman Chan
     */
    public static function setServerLoginURL($url = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after' . __CLASS__ . '::client()');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string`)');
        }
        self::$_PHPCAS_CLIENT->setServerLoginURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * Set the serviceValidate URL of the CAS server.
     * Used only in CAS 1.0 validations
     *
     * @param string $url the serviceValidate URL
     *
     * @return void
     */
    public static function setServerServiceValidateURL($url = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after' . __CLASS__ . '::client()');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string`)');
        }
        self::$_PHPCAS_CLIENT->setServerServiceValidateURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * Set the proxyValidate URL of the CAS server.
     * Used for all CAS 2.0 validations
     *
     * @param string $url the proxyValidate URL
     *
     * @return void
     */
    public static function setServerProxyValidateURL($url = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after' . __CLASS__ . '::client()');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string`)');
        }
        self::$_PHPCAS_CLIENT->setServerProxyValidateURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * Set the samlValidate URL of the CAS server.
     *
     * @param string $url the samlValidate URL
     *
     * @return void
     */
    public static function setServerSamlValidateURL($url = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after' . __CLASS__ . '::client()');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be`string\')');
        }
        self::$_PHPCAS_CLIENT->setServerSamlValidateURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * This method returns the URL to be used to login.
     * or phpCAS::isAuthenticated().
     *
     * @return the login name of the authenticated user
     */
    public static function getServerLogoutURL()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should not be called before ' . __CLASS__ . '::client() or ' . __CLASS__ . '::proxy()');
        }
        return self::$_PHPCAS_CLIENT->getServerLogoutURL();
    }

    /**
     * Set the logout URL of the CAS server.
     *
     * @param string $url the logout URL
     *
     * @return void
     * @since 0.4.21 by Wyman Chan
     */
    public static function setServerLogoutURL($url = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error(
                'this method should only be called after' . __CLASS__ . '::client()'
            );
        }
        if (gettype($url) != 'string') {
            phpCAS :: error(
                'type mismatched for parameter $url (should be `string`)'
            );
        }
        self::$_PHPCAS_CLIENT->setServerLogoutURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * This method is used to logout from CAS.
     *
     * @param string $params an array that contains the optional url and
     * service parameters that will be passed to the CAS server
     *
     * @return void
     */
    public static function logout($params = "")
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        $parsedParams = array();
        if ($params != "") {
            if (is_string($params)) {
                phpCAS :: error('method `phpCAS::logout($url)\' is now deprecated, use `phpCAS::logoutWithUrl($url)\' instead');
            }
            if (!is_array($params)) {
                phpCAS :: error('type mismatched for parameter $params (should be `array\')');
            }
            foreach ($params as $key => $value) {
                if ($key != "service" && $key != "url") {
                    phpCAS :: error('only `url\' and `service\' parameters are allowed for method `phpCAS::logout($params)\'');
                }
                $parsedParams[$key] = $value;
            }
        }
        self::$_PHPCAS_CLIENT->logout($parsedParams);
        // never reached
        phpCAS :: traceEnd();
    }

    /**
     * This method is used to logout from CAS. Halts by redirecting to the CAS
     * server.
     *
     * @param service $service a URL that will be transmitted to the CAS server
     *
     * @return void
     */
    public static function logoutWithRedirectService($service)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (!is_string($service)) {
            phpCAS :: error('type mismatched for parameter $service (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->logout(array("service" => $service));
        // never reached
        phpCAS :: traceEnd();
    }

    /**
     * This method is used to logout from CAS. Halts by redirecting to the CAS
     * server.
     *
     * @param string $url a URL that will be transmitted to the CAS server
     *
     * @return void
     * @deprecated The url parameter has been removed from the CAS server as of
     * version 3.3.5.1
     */
    public static function logoutWithUrl($url)
    {
        trigger_error('Function deprecated for cas servers >= 3.3.5.1', E_USER_DEPRECATED);
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (!is_string($url)) {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->logout(array("url" => $url));
        // never reached
        phpCAS :: traceEnd();
    }

    /**
     * This method is used to logout from CAS. Halts by redirecting to the CAS
     * server.
     *
     * @param string $service a URL that will be transmitted to the CAS server
     * @param string $url a URL that will be transmitted to the CAS server
     *
     * @return void
     *
     * @deprecated The url parameter has been removed from the CAS server as of
     * version 3.3.5.1
     */
    public static function logoutWithRedirectServiceAndUrl($service, $url)
    {
        trigger_error('Function deprecated for cas servers >= 3.3.5.1', E_USER_DEPRECATED);
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (!is_string($service)) {
            phpCAS :: error('type mismatched for parameter $service (should be `string\')');
        }
        if (!is_string($url)) {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->logout(
            array(
                "service" => $service,
                "url" => $url
            )
        );
        // never reached
        phpCAS :: traceEnd();
    }

    /**
     * Set the fixed URL that will be used by the CAS server to transmit the
     * PGT. When this method is not called, a phpCAS script uses its own URL
     * for the callback.
     *
     * @param string $url the URL
     *
     * @return void
     */
    public static function setFixedCallbackURL($url = '')
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (!self::$_PHPCAS_CLIENT->isProxy()) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setCallbackURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * Set the fixed URL that will be set as the CAS service parameter. When this
     * method is not called, a phpCAS script uses its own URL.
     *
     * @param string $url the URL
     *
     * @return void
     */
    public static function setFixedServiceURL($url)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (gettype($url) != 'string') {
            phpCAS :: error('type mismatched for parameter $url (should be `string\')');
        }
        self::$_PHPCAS_CLIENT->setURL($url);
        phpCAS :: traceEnd();
    }

    /**
     * Get the URL that is set as the CAS service parameter.
     *
     * @return string Service Url
     */
    public static function getServiceURL()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        return (self::$_PHPCAS_CLIENT->getURL());
    }

    /**
     * Retrieve a Proxy Ticket from the CAS server.
     *
     * @param string $target_service Url string of service to proxy
     * @param string &$err_code error code
     * @param string &$err_msg error message
     *
     * @return string Proxy Ticket
     */
    public static function retrievePT($target_service, & $err_code, & $err_msg)
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::proxy()');
        }
        if (gettype($target_service) != 'string') {
            phpCAS :: error('type mismatched for parameter $target_service(should be `string\')');
        }
        return (self::$_PHPCAS_CLIENT->retrievePT($target_service, $err_code, $err_msg));
    }

    /**
     * Set the certificate of the CAS server CA and if the CN should be properly
     * verified.
     *
     * @param string $cert CA certificate file name
     * @param bool $validate_cn Validate CN in certificate (default true)
     *
     * @return void
     */
    public static function setCasServerCACert($cert, $validate_cn = true)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (gettype($cert) != 'string') {
            phpCAS :: error('type mismatched for parameter $cert (should be `string\')');
        }
        if (gettype($validate_cn) != 'boolean') {
            phpCAS :: error('type mismatched for parameter $validate_cn (should be `boolean\')');
        }
        self::$_PHPCAS_CLIENT->setCasServerCACert($cert, $validate_cn);
        phpCAS :: traceEnd();
    }

    /**
     * Set no SSL validation for the CAS server.
     *
     * @return void
     */
    public static function setNoCasServerValidation()
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        phpCAS :: trace('You have configured no validation of the legitimacy of the cas server. This is not recommended for production use.');
        self::$_PHPCAS_CLIENT->setNoCasServerValidation();
        phpCAS :: traceEnd();
    }


    /**
     * Disable the removal of a CAS-Ticket from the URL when authenticating
     * DISABLING POSES A SECURITY RISK:
     * We normally remove the ticket by an additional redirect as a security
     * precaution to prevent a ticket in the HTTP_REFERRER or be carried over in
     * the URL parameter
     *
     * @return void
     */
    public static function setNoClearTicketsFromUrl()
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        self::$_PHPCAS_CLIENT->setNoClearTicketsFromUrl();
        phpCAS :: traceEnd();
    }

    /** @} */

    /**
     * Change CURL options.
     * CURL is used to connect through HTTPS to CAS server
     *
     * @param string $key the option key
     * @param string $value the value to set
     *
     * @return void
     */
    public static function setExtraCurlOption($key, $value)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        self::$_PHPCAS_CLIENT->setExtraCurlOption($key, $value);
        phpCAS :: traceEnd();
    }

    /**
     * If you want your service to be proxied you have to enable it (default
     * disabled) and define an accepable list of proxies that are allowed to
     * proxy your service.
     *
     * Add each allowed proxy definition object. For the normal CAS_ProxyChain
     * class, the constructor takes an array of proxies to match. The list is in
     * reverse just as seen from the service. Proxies have to be defined in reverse
     * from the service to the user. If a user hits service A and gets proxied via
     * B to service C the list of acceptable on C would be array(B,A). The definition
     * of an individual proxy can be either a string or a regexp (preg_match is used)
     * that will be matched against the proxy list supplied by the cas server
     * when validating the proxy tickets. The strings are compared starting from
     * the beginning and must fully match with the proxies in the list.
     * Example:
     *        phpCAS::allowProxyChain(new CAS_ProxyChain(array(
     *                'https://app.example.com/'
     *            )));
     *        phpCAS::allowProxyChain(new CAS_ProxyChain(array(
     *                '/^https:\/\/app[0-9]\.example\.com\/rest\//',
     *                'http://client.example.com/'
     *            )));
     *
     * For quick testing or in certain production screnarios you might want to
     * allow allow any other valid service to proxy your service. To do so, add
     * the "Any" chain:
     *        phpcas::allowProxyChain(new CAS_ProxyChain_Any);
     * THIS SETTING IS HOWEVER NOT RECOMMENDED FOR PRODUCTION AND HAS SECURITY
     * IMPLICATIONS: YOU ARE ALLOWING ANY SERVICE TO ACT ON BEHALF OF A USER
     * ON THIS SERVICE.
     *
     * @param CAS_ProxyChain_Interface $proxy_chain A proxy-chain that will be
     * matched against the proxies requesting access
     *
     * @return void
     */
    public static function allowProxyChain(CAS_ProxyChain_Interface $proxy_chain)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (self::$_PHPCAS_CLIENT->getServerVersion() !== CAS_VERSION_2_0) {
            phpCAS :: error('this method can only be used with the cas 2.0 protool');
        }
        self::$_PHPCAS_CLIENT->getAllowedProxyChains()->allowProxyChain($proxy_chain);
        phpCAS :: traceEnd();
    }

    /**
     * Answer an array of proxies that are sitting in front of this application.
     * This method will only return a non-empty array if we have received and
     * validated a Proxy Ticket.
     *
     * @return array
     * @access public
     * @since 6/25/09
     */
    public static function getProxies()
    {
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS::error('this method should only be called after ' . __CLASS__ . '::client()');
        }

        return (self::$_PHPCAS_CLIENT->getProxies());
    }

    // ########################################################################
    // PGTIOU/PGTID and logoutRequest rebroadcasting
    // ########################################################################

    /**
     * Add a pgtIou/pgtId and logoutRequest rebroadcast node.
     *
     * @param string $rebroadcastNodeUrl The rebroadcast node URL. Can be
     * hostname or IP.
     *
     * @return void
     */
    public static function addRebroadcastNode($rebroadcastNodeUrl)
    {
        phpCAS::traceBegin();
        phpCAS::log('rebroadcastNodeUrl:' . $rebroadcastNodeUrl);
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        if (!(bool)preg_match("/^(http|https):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i", $rebroadcastNodeUrl)) {
            phpCAS::error('type mismatched for parameter $rebroadcastNodeUrl (should be `url\')');
        }
        self::$_PHPCAS_CLIENT->addRebroadcastNode($rebroadcastNodeUrl);
        phpCAS::traceEnd();
    }

    /**
     * This method is used to add header parameters when rebroadcasting
     * pgtIou/pgtId or logoutRequest.
     *
     * @param String $header Header to send when rebroadcasting.
     *
     * @return void
     */
    public static function addRebroadcastHeader($header)
    {
        phpCAS :: traceBegin();
        if (!is_object(self::$_PHPCAS_CLIENT)) {
            phpCAS :: error('this method should only be called after ' . __CLASS__ . '::client() or' . __CLASS__ . '::proxy()');
        }
        self::$_PHPCAS_CLIENT->addRebroadcastHeader($header);
        phpCAS :: traceEnd();
    }
}

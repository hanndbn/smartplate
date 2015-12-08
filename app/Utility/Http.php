<?php

/**
 * Http helper class
 *  
 * @package       app.Utility
 * 
 */
class Utility_Http {

    /**
     * List of browser strings to check for in the {@link isBrowsingWith()} function.
     *
     * @var array
     */
    protected static $_browsers = array(
        'firefox' => 'Firefox',
        'ie' => 'MSIE',
        'webkit' => 'WebKit',
        'opera' => 'Opera'
    );

    /** Checks to verify that the visitor is browsing with a particular user agent
     *
     * @param string $browser
     *
     * @return boolean
     */
    public static function isBrowsingWith($browser) {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        $ua = $_SERVER['HTTP_USER_AGENT'];

        if ($browser == 'mobile') {
            if (self::isBrowsingWith('webkit')) {
                if (preg_match('# Mobile( Safari)?/#', $ua)) { // iPhone, Android, etc
                    return true;
                } else if (preg_match('#NokiaN[^\/]*#', $ua)) {
                    return true;
                } else if (strpos('SymbianOS', $ua) !== false) {
                    return true;
                } else if (strpos('Silk-Accelerated', $ua) !== false) { // Amazon Silk
                    return true;
                }
            } else if (self::isBrowsingWith('opera') && preg_match('#Opera( |/)(Mini|8|9\.[0-7])#', $ua)) {
                // well, this may not be mobile, but is very old :)
                return true;
            } else if (preg_match('#IEMobile/#', $ua)) {
                return true;
            } else if (preg_match('#^BlackBerry#', $ua)) {
                return true;
            }

            return false;
        }

        //TODO: Add version checking and more browsers
        $browser = strtolower($browser);
        if (array_key_exists($browser, self::$_browsers)) {
            return (strpos($ua, self::$_browsers[$browser]) !== false);
        } else {
            return false;
        }
    }

}
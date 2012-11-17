<?php namespace XMLRPC;

/**
 * XML-RPC Bundle 
 * 
 * An XML-RPC bundle for laravel I made this as I needed one for CellSix a project I'm
 * working on creating a web interface for Atheme IRC Platform. Didn't see one, so I figured I would share it.
 * Hope someone gets some use out of it. >.<
 *
 * Note: 
 * This bundle will try first to use PHP's native xmlrpc_encode_request function however I know a lot
 * of people don't have that installed. This was one of the issues I faced on my project, so if it's not found it
 * will nicely fallback to the functions I've writtin below. This way the user doesn't need to install the PHP-XMLRPC module
 * and this class will still work.
 * 
 * @category    Bundle
 * @package     XML-RPC
 * @author      Joseph Newing <jnewing@gmail.com>
 * @license     MIT License <http://www.opensource.org/licenses/mit>
 * @copyright   2012 Joseph Newing
 * 
 * @see         https://github.com/jnewing/laravel-xmlrpc
 * 
 */

use \Config;

class XML_RPC
{
    /**
     * CallMethod()
     * Call a XML-RPC method over HTTP post and get the response returned as a nice SimpleXML Object
     *
     * @param string $host      - hostname or ip of the XML-RPC server
     * @param string $method    - XML-RPC method we wish to call
     * @param mixed $params     - additional parameters that acompnay the $method
     * @return SimpleXML Object
     */
    public static function CallMethod($host, $method, $params)
    {
        // build our payload
        if (function_exists('xmlrpc_encode_request'))
            $payload = xmlrpc_encode_request($method, $params);
        else
            $payload = XML_RPC::xmlrpc_encode_request($method, $params);

        // create a stream HTTP resource for our POST
        $xml_rpc = stream_context_create(array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: text/xml\r\n" .
                            "User-Agent: " . Config::get('xmlrpc::default.xmlrpc_useragent') . "\r\n",
                'content' => $payload
            )
        ));

        // HTTP response as raw data
        $raw_data = file_get_contents(self::Host($host) . ":" . Config::get('xmlrpc::default.xmlrpc_port') . Config::get('xmlrpc::default.xmlrpc_path'), false, $xml_rpc);

        return new \SimpleXMLElement($raw_data);

    /**
     * Host()
     * Takes a host as a string and makes sure there is a http:// on the front
     *
     * @param string $host  - the hostname we want to check
     * @return string
     */
    public static function Host($host)
    {
        if (substr($host, 0, 4) != 'http')
            return "http://{$host}";
    }

    /**
     * xmlrpc_encode_request()
     * function will take the place of 'xmlrpc_encode_request' if the user does not have
     * the PHP-XMLRPC package installed on their system.
     *
     * @param string $method    - the XML-RPC method they wish to call
     * @param string $params    - the XML-RPC parameters they wish to use along with it
     * @return string $payload  - the XML-RPC request payload as a string
     */
    public static function xmlrpc_encode_request($method, $params)
    {
        // payload header
        $payload = '<?xml version="1.0" encoding="iso-8859-1"?>' . "\r\n";
        $payload .= "\t<methodCall>\r\n";
        
        // payload method
        $payload .= "\t\t<methodName>{$method}</methodName>\r\n";

        // payload parms
        $payload .= "\t\t<params>\r\n";

        foreach ($params as $param)
            $payload .= "<param>\r\n" . XML_RPC::xmlrpc_encode_type($param) . "</param>\r\n";

        // payload close
        $payload .= "\t\t</params>\r\n";
        $payload .= "\t</methodCall>\r\n";

        return $payload;
    }

    /**
     * xmlrpc_encode_type()
     * function will encode a php var as it's respective type into it's XML-RPC counter part.
     *
     * @param mixed $var    - the variable we want to encode
     * @return string       - XML-RPC encoded type
     */
    public static function xmlrpc_encode_type($var)
    {   
        $ret_val = false; // if we fail return false

        switch (gettype($var))
        {
            case 'boolean':
                $ret_val = "<value><boolean>{$var}</boolean></value>\r\n";
                break;

            case 'double':
                $ret_val = "<value><double>{$var}</double></value>\r\n";
                break;

            case 'integer':
                $ret_val = "<value><int>{$var}</int></value>\r\n";
                break;

            case 'string':
                $ret_val = "<value><string>{$var}</string></value>\r\n";
                break;

            case 'array':
                $ret_val = XML_RPC::encode_array($var);
                break;
            
            default:
                $ret_val = false;
                break;
        }

        return $ret_val;
    }

    /**
     * encode_array()
     * function will encode an array to be compilant with a XML-RPC request
     * 
     * @param array     - the array we want to encode
     * @return string   - XML-RPC encoded string of the array and it's data
     */
    private static function encode_array($array)
    {
        $encoded_array = false;

        if (XML_RPC::is_assoc($array))
        {
            $encoded_array = "<struct>\r\n";

            foreach ($array as $key => $value)
            {
                $encoded_array .= "<member>\r\n";
                $encoded_array .= "<name>{$key}</name>\r\n";
                $encoded_array .= XML_RPC::xmlrpc_encode_type($value);
                $encoded_array .= "</member>\r\n";
            }

            $encoded_array .= "</struct>\r\n";
        }
        else
        {
            $encoded_array = "<array>\r\n";
            $encoded_array .= "<data>\r\n";

            foreach ($array as $value)
                $encoded_array .= XML_RPC::xmlrpc_encode_type($value);

            $encoded_array .= "</data>\r\n";
            $encoded_array .= "</array>\r\n";
        }

        return $encoded_array;
    }

    /**
     * is_assoc()
     * for some reason that is beyond me PHP lacks a function to check and see if an array is associative or not
     * this is the quickest way I could come up with to check.
     *
     * @param array $array  - the array we want to check
     * @return bool         - bool value if the array is associative or not
     */
    private static function is_assoc($array)
    {
        if (is_array($array) && !is_numeric(array_shift(array_keys($array))))
            return true;

        return false;
    }



}

<?php
/**
 * XmlToArray: A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-XmlToArray
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 *
 * Usage:
 *          $array = XmlToArray::createArray($xml);
 */

if(!class_exists('XmlToArray')) {
    class XmlToArray {
        private static $xml = null;
        private static $encoding = 'UTF-8';

        /**
         * Initialize the root XML node [optional]
         *
         * @param string $version
         * @param string $encoding
         * @param bool $format_output
         */
        public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true) {
            self::$xml = new DOMDocument($version, $encoding);
            self::$xml->formatOutput = $format_output;
            self::$encoding = $encoding;
        } // END public static function init($version = '1.0', $encoding = 'UTF-8', $format_output = true)

        /**
         * Convert an XML to Array
         *
         * @param string $input_xml
         * @throws Exception
         * @return Ambigous DOMDocument <Ambigous, string, multitype:multitype: string multitype:string  Ambigous >
         */
        public static function &createArray($input_xml) {
            $input_xml = self::strip_comment($input_xml);
            $xml = self::getXMLRoot();

            if(is_string($input_xml)) {
                $parsed = $xml->loadXML($input_xml);

                if(!$parsed) {
                    throw new Exception('[XmlToArray] Error parsing the XML string.');
                } // END if(!$parsed)
            } else {
                if(get_class($input_xml) != 'DOMDocument') {
                    throw new Exception('[XmlToArray] The input XML object should be of type: DOMDocument.');
                } // END if(get_class($input_xml) != 'DOMDocument')

                $xml = self::$xml = $input_xml;
            } // END if(is_string($input_xml))

            $array[$xml->documentElement->tagName] = self::convert($xml->documentElement);

            self::$xml = null; // clear the xml node in the class for 2nd time use.

            return $array;
        } // END public static function &createArray($input_xml)

        /**
         * Convert an Array to XML
         *
         * @param string $node XML as a string or as an object of DOMDocument
         * @return Ambigous <string, multitype:multitype: string multitype:string  unknown >
         */
        private static function &convert($node) {
            $output = array();

            switch($node->nodeType) {
                case XML_CDATA_SECTION_NODE:
                    $output['@cdata'] = trim($node->textContent);
                    break;

                case XML_TEXT_NODE:
                    $output = trim($node->textContent);
                    break;

                case XML_ELEMENT_NODE:
                    // for each child node, call the covert function recursively
                    for($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                        $child = $node->childNodes->item($i);
                        $v = self::convert($child);

                        if(isset($child->tagName)) {
                            $t = $child->tagName;

                            // assume more nodes of same kind are coming
                            if(!isset($output[$t])) {
                                $output[$t] = array();
                            } // END if(!isset($output[$t]))

                            $output[$t][] = $v;
                        } else {
                            //check if it is not an empty text node
                            if($v !== '') {
                                $output = $v;
                            } // END if($v !== '')
                        } // END if(isset($child->tagName))
                    } // END for($i = 0, $m = $node->childNodes->length; $i < $m; $i++)

                    if(is_array($output)) {
                        // if only one node of its kind, assign it directly instead if array($value);
                        foreach($output as $t => $v) {
                            if(is_array($v) && count($v) == 1) {
                                $output[$t] = $v[0];
                            } // END if(is_array($v) && count($v) == 1)
                        } // END foreach($output as $t => $v)

                        if(empty($output)) {
                            //for empty nodes
                            $output = '';
                        } // END if(empty($output))
                    } // END if(is_array($output))

                    // loop through the attributes and collect them
                    if($node->attributes->length) {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        } // END foreach($node->attributes as $attrName => $attrNode)

                        // if its an leaf node, store the value in @value instead of directly storing it.
                        if(!is_array($output)) {
                            $output = array(
                                '@value' => $output
                            );
                        } // END if(!is_array($output))

                        $output['@attributes'] = $a;
                    } // END if($node->attributes->length)

                    break;
            } // END switch($node->nodeType)

            return $output;
        } // END private static function &convert($node)

        /**
         * Get the root XML node, if there isn't one, create it.
         *
         * @return NULL
         */
        private static function getXMLRoot() {
            if(empty(self::$xml)) {
                self::init();
            } // END if(empty(self::$xml))

            return self::$xml;
        } // END private static function getXMLRoot()

        /**
         * Strip the comments out of our XML
         *
         * @param string $input_xml
         * @return string
         */
        private static function strip_comment($input_xml) {
            return preg_replace('/<!--(?:.(?<!--))*-->/', '', $input_xml);
        } // END private static function strip_comment($input_xml)
    } // END class XmlToArray
} // END if(!class_exists('XmlToArray'))
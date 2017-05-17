<?php
/**
 * Skybet PHP implementation.
 *
 * (c) Alexander Sharapov <alexander@sharapov.biz>
 * http://sharapov.biz/
 *
 */

namespace Sharapov\SkybetPHP;

class SkybetAPI {

  private $_baseUri = "https://services.skybet.com";

  private $_sportApi = "/sportsapi/v2";

  private $_timeout = 5;

  private $_queryParams = [];

  private $_endpoint;

  private $_endpointResponseKeyField;

  /**
   * SkybetAPI constructor.
   *
   * @param array  $params
   * @param string $endpoint
   *
   * @throws \Sharapov\SkybetPHP\SkybetAPIException
   */
  public function __construct( array $params = [], $endpoint = null ) {
    $this->_queryParams = $params;
    if ( ! $this->_isCurl() ) {
      throw new SkybetAPIException( 'CURL is not enabled on the web server' );
    }
    if ( ! is_null( $endpoint ) ) {
      $this->_endpoint = $endpoint;
    }
  }

  /**
   * Set timeout
   *
   * @param int $timeout
   *
   * @return $this
   */
  public function setTimeout( $timeout ) {
    $this->_timeout = (int) $timeout;

    return $this;
  }

  /**
   * Get timeout value
   *
   * @return int
   */
  public function getTimeout() {
    return $this->_timeout;
  }

  /**
   * Set base uri
   *
   * @param string $baseUri
   *
   * @return $this
   */
  public function setBaseUri( $baseUri ) {
    $this->_baseUri = $baseUri;

    return $this;
  }

  /**
   * Get HTTP client options
   *
   * @return array|mixed
   */
  public function getBaseUri() {
    return $this->_baseUri;
  }

  /**
   * Get class events list
   *
   * @return $this
   */
  public function classes() {
    $this->_endpoint                 = $this->_sportApi.'/a-z';
    $this->_endpointResponseKeyField = 'event_classes';

    return $this;
  }

  /**
   * Get event document
   *
   * @param int  $eventId
   * @param bool $getPrimaryMarket
   *
   * @return $this
   */
  public function event( $eventId, $getPrimaryMarket = false ) {
    $this->_endpoint = $this->_sportApi.'/event/' . $eventId;
    if ( $getPrimaryMarket ) {
      $this->_endpoint .= '/primary-market';
    }

    return $this;
  }

  /**
   * Set the full url to be requested to skybet
   *
   * @param            $url
   * @param array|null $b
   *
   * @return \Sharapov\SkybetPHP\SkybetAPI
   * @throws \Sharapov\SkybetPHP\SkybetAPIException
   */
  public function setRequestUrl( $url, array $b = null ) {
    if ( ! is_null( $this->_endpoint ) ) {
      throw new SkybetAPIException( "You can't loop class by this method" );
    }

    return new SkybetAPI( $this->_arrayMerge( $this->_queryParams, $b ), $url );
  }

  /**
   * Catch undefined method and pass it to the url chain
   *
   * @param $endpoint
   * @param $b
   *
   * @return \Sharapov\SkybetPHP\SkybetAPI
   */
  public function __call( $endpoint, $b ) {
    $this->_endpoint = $this->_endpoint . '/' . $endpoint;
    if ( $b ) {
      foreach ( $b as $i => $a ) {
        if ( is_array( $a ) ) {
          $b = $this->_arrayMerge( $b[ $i ], $a );
        } else {
          $this->_endpoint = $this->_endpoint . '/' . $a;
        }
      }
    }

    return new SkybetAPI( $this->_arrayMerge( $this->_queryParams, $b ), $this->_endpoint );
  }

  /**
   * Get JSON response
   *
   * @return mixed
   * @throws \Sharapov\SkybetPHP\SkybetAPIException
   */
  public function get() {
    return $this->_request();
  }

  /**
   * Send request
   *
   * @return mixed
   * @throws \Sharapov\SkybetPHP\SkybetAPIException
   */
  private function _request() {
    try {
      if ( ! isset( $this->_queryParams['api_user'] ) ) {
        throw new SkybetAPIException( 'The parameter "api_user" is missed from the params array' );
      }

      $ch = curl_init();
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      curl_setopt( $ch, CURLOPT_URL, $this->_getRequestUri() );
      curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout );
      curl_setopt( $ch, CURLOPT_TIMEOUT, $this->_timeout );

      $response = curl_exec( $ch );
      $httpcode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

      if ( $response == false ) {
        throw new SkybetAPIException( 'Error requesting skybet' );
      }
      curl_close( $ch );

      if ( $httpcode >= 200 && $httpcode < 300 ) {
        $responseJson = json_decode( $response );
        if ( property_exists( $responseJson, $this->_endpointResponseKeyField ) ) {
          return $responseJson->{$this->_endpointResponseKeyField};
        } else {
          return $responseJson;
        }
      }
      throw new SkybetAPIException( 'Error: ' . $response );
    } catch ( \Exception $e ) {
      throw new SkybetAPIException( $e->getMessage() );
    }
  }

  private function _getRequestUri() {
    return $this->_baseUri . $this->_endpoint . '?' . http_build_query( $this->_queryParams );
  }

  private function _isCurl() {
    return function_exists( 'curl_version' );
  }

  /**
   * Merge two arrays - but if one is blank or not an array, return the other.
   *
   * @param $a      array First array, into which the second array will be merged
   * @param $b      array Second array, with the data to be merged
   * @param $unique boolean If true, remove duplicate values before returning
   *
   * @return array
   */
  private function _arrayMerge( &$a, $b, $unique = false ) {
    if ( empty( $b ) ) {
      return $a;  // No changes to be made to $a
    }
    if ( empty( $a ) ) {
      $a = $b;

      return $a;
    }
    $a = array_merge( $a, $b );
    if ( $unique ) {
      $a = array_unique( $a );
    }

    return $a;
  }
}
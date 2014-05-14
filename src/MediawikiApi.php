<?php

namespace Mediawiki\Api;

use Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar;
use Guzzle\Plugin\Cookie\CookiePlugin;
use Guzzle\Service\Mediawiki\MediawikiApiClient;
use InvalidArgumentException;

class MediawikiApi {

	/**
	 * @var MediawikiApiClient
	 */
	private $client;

	/**
	 * @var bool|string
	 */
	private $isLoggedIn;

	/**
	 * @var MediawikiSession
	 */
	private $session;

	/**
	 * @param string|MediawikiApiClient $client either the url or the api or
	 * @param MediawikiSession|null $session Inject a custom session here
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $client, $session = null ) {
		if( is_string( $client ) ) {
			$client = MediawikiApiClient::factory( array( 'base_url' => $client ) );
		} elseif ( !$client instanceof MediawikiApiClient ) {
			throw new InvalidArgumentException();
		}

		if( $session === null ) {
			$session = new MediawikiSession( $client );
		} elseif ( !$session instanceof MediawikiSession ){
			throw new InvalidArgumentException();
		}

		$this->client = $client;
		$this->client->addSubscriber( new CookiePlugin( new ArrayCookieJar() ) );
		$this->session = $session;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $action
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function getAction( $action, $params = array() ) {
		$resultArray = $this->client->getAction( array_merge( array( 'action' => $action ), $params ) );
		$this->throwUsageExceptions( $resultArray );
		return $resultArray;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $action
	 * @param array $params
	 *
	 * @return mixed
	 */
	public function postAction( $action, $params = array() ) {
		$resultArray = $this->client->postAction( array_merge( array( 'action' => $action ), $params ) );
		$this->throwUsageExceptions( $resultArray );
		return $resultArray;
	}

	/**
	 * @param array $result
	 *
	 * @throws UsageException
	 */
	private function throwUsageExceptions( $result ) {
		if( is_array( $result ) && array_key_exists( 'error', $result ) ) {
			throw new UsageException( $result['error']['code'], $result['error']['info'] );
		}
	}

	/**
	 * @since 0.1
	 *
	 * @return bool|string false or the name of the current user
	 */
	public function isLoggedin() {
		return $this->isLoggedIn;
	}

	/**
	 * @since 0.1
	 *
	 * @param ApiUser $apiUser
	 *
	 * @return bool success
	 */
	public function login( ApiUser $apiUser ) {
		$credentials = array(
			'lgname' => $apiUser->getUsername(),
			'lgpassword' => $apiUser->getPassword()
		);
		$result = $this->postAction( 'login', $credentials, $apiUser );
		if ( $result['login']['result'] == "NeedToken" ) {
			$result = $this->postAction( 'login', array_merge( array( 'lgtoken' => $result['login']['token'] ), $credentials), $apiUser );
		}
		if ( $result['login']['result'] == "Success" ) {
			$this->isLoggedIn = $apiUser->getUsername();
			return true;
		}
		$this->isLoggedIn = false;
		return false;
	}

	/**
	 * @since 0.1
	 * @return bool success
	 */
	public function logout() {
		$result = $this->postAction( 'logout', array() );
		if( $result === array() ) {
			$this->isLoggedIn = false;
			$this->clearTokens();
			return true;
		}
		return false;
	}

	/**
	 * @since 0.1
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function getToken( $type = 'edit' ) {
		$this->session->getToken( $type );
	}

	/**
	 * @since 0.1
	 * Clears all tokens stored by the api
	 */
	public function clearTokens() {
		$this->session->clearTokens();
	}

}

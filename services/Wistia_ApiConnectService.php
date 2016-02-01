<?php
namespace Craft;

class Wistia_ApiConnectService extends BaseApplicationComponent
{
	private $apiKey;

	public function __construct()
	{
		$this->apiKey = craft()
			->plugins
			->getPlugin('wistia')
			->getSettings()
			->apiKey;
	}

	/**
	 * Retrieve projects
	 *
	 * @return array
	 */
	public function getProjects() {
		$results = [];

		// Fail if no API key defined
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		$rawProjects = json_decode($this->send('projects.json'));

		if (is_array($rawProjects)) {
			$projects = [];

			foreach ($rawProjects as $rawProject) {
				$projects[$rawProject->id] = $rawProject->name;
			}

			$any = [
				'--' => '--Any--'
			];

			$results = [
				'--' => '--Any--'
			] + $projects;
		} else {
			$results = $rawProjects;
		}

		return $results;
	}

	/**
	 * Retrieve videos
	 *
	 * @return array
	 */
	public function getVideos($projects) {
		// Fail if no API key defined
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		$videos = [];

		foreach ($projects as $project) {
			$params = [];

			if ($project !== '--') {
				$params['project_id'] = $project;
			}

			$rawVideos = json_decode($this->send('medias.json?' . implode('=', $params));

			foreach ($rawVideos as $rawVideo) {
				$videos[$rawVideo->id] = $rawVideo->name;
			}
		}

		return $videos;
	}

	private function getData($endpoint, $params = []) {
		$baseUrl = craft()
			->config
			->get('apiUrl', 'wistia');
	}

	/**
	 * Fire curl request to endpoint
	 *
	 * @return array
	 */
	private function send($url)
	{
		// Fail if no API key defined
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $this->apiKey);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}
}
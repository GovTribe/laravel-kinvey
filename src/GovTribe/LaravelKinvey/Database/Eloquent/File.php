<?php namespace GovTribe\LaravelKinvey\Database\Eloquent;

use Guzzle\Http\StaticClient;
use Guzzle\Http\Client;

use Illuminate\Support\Facades\Request;

class File extends Model {

	/**
	 * The collection associated with the model.
	 *
	 * @var string
	 */
	protected $collection = 'files';

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	public static function boot()
	{
		parent::boot();

		File::creating(function($file)
		{
			if (!$file->path) throw new \Exception('File model must contain a not-null path attribute');
		});
	}

	/**
	 * Download the file to a local path.
	 *
	 * @param  string  $pathToFile,
	 * @param  string  $name
	 * @param  headers $array
	 * @return void
	 */
	public function download($pathToFile, $name = null, array $headers = array())
	{
		$response = StaticClient::get($this->offsetGet('_downloadURL'), array(
			'headers' => $headers,
			'timeout' => $this->timeout,
			'save_to' => $pathToFile,
		));

		file_put_contents($pathToFile, $response->getBody()->getStream());
	}

}
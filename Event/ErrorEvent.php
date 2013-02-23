<?php
namespace FS\SolrBundle\Event;

class ErrorEvent extends Event {
	
	/**
	 * @var \Exception
	 */
	private $exception = null;

	/**
	 * @return \Exception
	 */
	public function getException() {
		return $this->exception;
	}

	/**
	 * @param \Exception $exception
	 */
	public function setException($exception) {
		$this->exception = $exception;
	}

	/**
	 * @return string
	 */
	public function getExceptionMessage() {
		if (!$this->exception) {
			return '';
		}
		
		return $this->exception->getMessage();
	}
}

?>